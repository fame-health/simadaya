<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StatusPengajuanMagangNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $pengajuan;

    public function __construct($pengajuan)
    {
        $this->pengajuan = $pengajuan;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Update Status Pengajuan Magang')
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Status pengajuan magang kamu telah berubah menjadi **' . strtoupper($this->pengajuan->status) . '**.')
            ->when($this->pengajuan->status === 'ditolak', function ($mail) {
                return $mail->line('Alasan penolakan: ' . $this->pengajuan->alasan_penolakan);
            })
            ->line('Tanggal Pengajuan: ' . $this->pengajuan->created_at->format('d M Y'))
            ->action('Lihat Detail', url('/pengajuan/' . $this->pengajuan->id))
            ->line('Terima kasih telah menggunakan sistem SIMADAYA.');
    }
}
