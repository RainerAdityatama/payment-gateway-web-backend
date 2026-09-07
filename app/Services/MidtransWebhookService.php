<?php

namespace App\Services;

use App\Models\Transaksi;
use App\Notifications\PaymentSuccessNotification;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification as FacadesNotification;
use Midtrans\Config;
use Midtrans\Notification;

class MidtransWebhookService
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production_midtrans');
    }

    public function handle()
    {
        try {
            $notif = new Notification();

            $transactionStatus = $notif->transaction_status;
            $fraudStatus = $notif->fraud_status;
            $orderId = $notif->order_id;

            Log::info("Webhook diterima untuk Order ID: " . $orderId . ", status : " . $transactionStatus);

            $transaksi = Transaksi::where('kode_transaksi', $orderId)->first();

            if (!$transaksi) {
                Log::warning("Webhook Gagal: Transaksi dengan kode {$orderId} tidak ditemukan di database.");
                throw new Exception("Transaksi tidak ditemukan: {$orderId}");
            }

            if ($transactionStatus === 'capture') {
                if ($fraudStatus === 'challenge') {
                    $transaksi->update(['status_transaksi' => 'tertunda']);
                } else if ($fraudStatus === 'accept') {
                    if ($transaksi->status_transaksi !== 'lunas') {
                        $transaksi->update(['status_transaksi' => 'lunas']);
                        FacadesNotification::route('mail', $transaksi->email_penyewa)->notify(new PaymentSuccessNotification($transaksi));
                    }
                }
            } else if ($transactionStatus === 'settlement') {
                if ($transaksi->status_transaksi !== 'lunas') {
                    $transaksi->update(['status_transaksi' => 'lunas']);
                    FacadesNotification::route('mail', $transaksi->email_penyewa)->notify(new PaymentSuccessNotification($transaksi));
                }
            } else if ($transactionStatus == 'pending') {
                $transaksi->update(['status_transaksi' => 'tertunda']);
            } else if ($transactionStatus == 'deny' || $transactionStatus == 'expire' || $transactionStatus == 'cancel') {
                // Pembayaran gagal, kadaluwarsa, atau dibatalkan
                $transaksi->update(['status_transaksi' => 'batal']);
            }

            Log::info("Status transaksi {$orderId} berhasil diperbarui.");
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
