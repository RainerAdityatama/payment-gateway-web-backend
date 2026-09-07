<?php

namespace App\Services;

use App\Models\DetailTransaksi;
use App\Models\Lapangan;
use App\Models\Transaksi;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;

class CheckoutService
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production_midtrans');
        Config::$isSanitized = true; // Mengamankan input data
        Config::$is3ds = true; // Wajib true untuk kartu kredit
    }

    public function processCheckout(array $validated)
    {
        // Gunakan Database Transaction agar jika ada error di tengah jalan,
        // data tidak tersimpan setengah-setengah (Prinsip ACID).
        DB::beginTransaction();

        try {
            // Buat Kode Transaksi Unik (contoh: TRX-20260901-ABCD)
            $kodeTransaksi = 'TRX-' . date('Ymd') . '-' . strtoupper(Str::random(4));

            $totalBayar = 0;
            $itemDetails = [];
            $detailTransaksiData = [];

            foreach ($validated['cart_items'] as $item) {
                $lapangan = Lapangan::findOrfail($item['id_lapangan']);
                $harga = $lapangan->harga_per_jam;

                $totalBayar += $harga;

                $detailTransaksiData[] = [
                    'lapangan_id' => $lapangan->id,
                    'tanggal_booking' => $item['date'],
                    'waktu_mulai' => $item['waktu_mulai'],
                    'waktu_selesai' => $item['waktu_selesai'],
                    'harga' => $harga,
                ];

                $itemDetails[] = [
                    'id' => $lapangan->id,
                    'price' => $harga,
                    'quantity' => 1,
                    'name' => "Booking " . $item['nama_lapangan'] . " (" . $item['label'] . ")"
                ];
            }

            // simpan ke tabel transaksi
            $transaksi = Transaksi::create([
                'kode_transaksi' => $kodeTransaksi,
                'nama_penyewa' => $validated['nama_penyewa'],
                'email_penyewa' => $validated['email_penyewa'],
                'nomor_penyewa' => $validated['nomor_penyewa'],
                'total_bayar' => $totalBayar,
                'status_transaksi' => 'tertunda',
            ]);

            foreach ($detailTransaksiData as $detail) {
                DetailTransaksi::create(array_merge($detail, ['transaksi_id' => $transaksi->id]));
            }

            $midtransPayload = [
                'transaction_details' => [
                    'order_id' => $kodeTransaksi,
                    'gross_amount' => $totalBayar,
                ],
                'customer_details' => [
                    'first_name' => $validated['nama_penyewa'],
                    'email' => $validated['email_penyewa'],
                    'phone' => $validated['nomor_penyewa'],
                ],
                'item_details' => $itemDetails,
            ];

            // dapatkan snap token
            $snapToken = Snap::getSnapToken($midtransPayload);

            $transaksi->update([
                'snap_token' => $snapToken
            ]);

            DB::commit();

            return [
                'snap_token' => $snapToken,
                'kode_transaksi' => $kodeTransaksi
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            throw new Exception('Gagal membuat transaksi: ' . $e->getMessage());
        }
    }
}
