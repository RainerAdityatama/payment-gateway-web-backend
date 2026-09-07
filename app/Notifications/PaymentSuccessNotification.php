<?php

namespace App\Notifications;

use App\Models\Transaksi;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentSuccessNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Transaksi $transaksi;

    /**
     * Create a new notification instance.
     */
    public function __construct(Transaksi $transaksi)
    {
        $this->transaksi = $transaksi;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $pdfOutput = Pdf::loadView('pdf.struk', ['transaksi' => $this->transaksi])->output();

        $fileName = 'Struk-' . $this->transaksi->kode_transaksi . '.pdf';

        return (new MailMessage)
            ->subject('Struk Pembayaran KelapaDua Sports - ' . $this->transaksi->kode_transaksi)
            ->greeting('Halo, ' . $this->transaksi->nama_penyewa . '!')
            ->line('Terima kasih telah melakukan pembayaran. Berikut kami lampirkan bukti struk penyewaan lapangan Anda.')
            ->line('Harap tunjukkan struk ini kepada petugas kami saat Anda datang.')
            ->attachData($pdfOutput, $fileName, [
                'mime' => 'application/pdf',
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
