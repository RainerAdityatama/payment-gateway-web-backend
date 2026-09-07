<?php

namespace App\Services;

use App\Models\DetailTransaksi;
use App\Models\Lapangan;
use Carbon\Carbon;

class LapanganService
{
    private const OPERATIONAL_HOURS = [
        '19:00:00',
        '20:00:00',
        '21:00:00',
        '22:00:00',
        '23:00:00'
    ];

    public function getLapanganAdmin()
    {
        $get = Lapangan::orderBy('id', 'desc')->get();

        return $get;
    }

    public function getLapanganPublic()
    {
        $get = Lapangan::where('status', 'aktif')->orderBy('tipe_lapangan')->get();

        return $get;
    }

    public function tambahLapangan(array $validated)
    {
        $tambah = Lapangan::create($validated);

        return $tambah;
    }

    public function editLapangan(array $validated, Lapangan $lapangan)
    {
        $edit = $lapangan->update($validated);

        return $edit;
    }

    public function hapusLapangan(Lapangan $lapangan)
    {
        $hapus = $lapangan->delete();

        return $hapus;
    }

    public function detailLapanganSlot(string $date, Lapangan $lapangan)
    {
        $slotSudahbooking = DetailTransaksi::where('lapangan_id', $lapangan->id)
            ->where('tanggal_booking', $date)
            ->whereHas('transaksi', function ($query) {
                $query->whereIn('status_transaksi', ['lunas', 'tertunda']);
            })->pluck('waktu_mulai')->toArray();

        // array data untuk menyimpan waktu booking
        $slots = [];
        $isToday = $date === now()->format('Y-m-d');
        $currentTime = now()->format('H:i:s');

        // buat data dinamisnya
        foreach (self::OPERATIONAL_HOURS as $waktuMulai) {
            $varWaktuMulai = Carbon::createFromFormat('H:i:s', $waktuMulai);
            $varWaktuSelesai = $varWaktuMulai->copy()->addHour();

            // cek apakah waktu ada di tabel transaksi (sudah dibooking)
            $isBooked = in_array($waktuMulai, $slotSudahbooking);

            // cek apakah waktu sudah lewat (untuk isAvailable)
            $isPassedToday = $isToday && ($waktuMulai <= $currentTime);

            $slots[] = [
                'waktu_mulai'   => $varWaktuMulai->format('H:i:s'),
                'waktu_selesai' => $varWaktuSelesai->format('H:i:s'),
                'date' => $date,
                'id_lapangan' => $lapangan->id,
                'nama_lapangan' => $lapangan->nama,
                'label' => $varWaktuMulai->format('H:i') . ' - ' . $varWaktuSelesai->format('H:i'),
                'harga_per_jam' => $lapangan->harga_per_jam,
                'is_available' => !$isBooked && !$isPassedToday
            ];
        }

        return $slots;
    }

    public function getKalenderBulanan(string $year, string $month, Lapangan $lapangan)
    {
        $totalSlotsPerDay = count(self::OPERATIONAL_HOURS);

        // cari tanggal yang full booking
        $bulananFullBooking = DetailTransaksi::where('lapangan_id', $lapangan->id)
            ->whereYear('tanggal_booking', $year)
            ->whereMonth('tanggal_booking', $month)
            ->whereHas('transaksi', function ($query) {
                $query->whereIn('status_transaksi', ['lunas, tertunda']);
            })->select('tanggal_booking')->groupBy('tanggal_booking')
            ->havingRaw('COUNT(id) >= ?', [$totalSlotsPerDay])
            ->pluck('tanggal_booking')->toArray();

        return $bulananFullBooking;
    }
}
