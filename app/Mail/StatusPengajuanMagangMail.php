<?php

namespace App\Mail;

use App\Models\PengajuanMagang;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StatusPengajuanMagangMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pengajuan;

    public function __construct(PengajuanMagang $pengajuan)
    {
        $this->pengajuan = $pengajuan;
    }

    public function build()
    {
        return $this->subject('Update Status Pengajuan Magang')
                    ->view('emails.status-pengajuan');
    }
}
