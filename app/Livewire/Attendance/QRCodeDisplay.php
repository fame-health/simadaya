<?php

namespace App\Livewire\Attendance;

use App\Models\AttendanceSession;
use App\Services\QRCodeService;
use Livewire\Component;
use Livewire\Attributes\On;

class QRCodeDisplay extends Component
{
    public AttendanceSession $session;
    public string $qrCode;
    public string $token;
    public string $expiredAt;
    public int $countdown = 10;

    public function mount(AttendanceSession $session, QRCodeService $qrCodeService)
    {
        $this->session = $session;
        $this->token = $session->current_token;
        $this->expiredAt = $session->expires_at->toDateTimeString();
        $this->generateQRCode($qrCodeService);
    }

    public function generateQRCode(QRCodeService $qrCodeService)
    {
        $this->qrCode = $qrCodeService->generate(
            $this->session->id,
            $this->token,
            $this->expiredAt
        );
    }

    public function decrementCountdown()
    {
        // 1. Ambil data sesi terbaru dari database untuk sinkronisasi
        $this->session->refresh();

        // 2. Cek apakah token di database SUDAH BERUBAH
        // Kita bandingkan token di memori ($this->token) dengan yang ada di DB
        if ($this->session->current_token !== $this->token && !empty($this->session->current_token)) {
            // JIKA BERUBAH: Update state di frontend dan reset countdown ke 10
            $this->token = $this->session->current_token;
            $this->expiredAt = $this->session->expires_at ? $this->session->expires_at->toDateTimeString() : now()->toDateTimeString();
            $this->countdown = 10;
            $this->generateQRCode(app(QRCodeService::class));
        } else {
            // JIKA MASIH SAMA: Kurangi angka di layar (tapi jangan sampai minus)
            if ($this->countdown > 0) {
                $this->countdown--;
            } else {
                // Jika sudah 0, tetap di 0 sambil menunggu Cron Job/Command mengupdate database
                $this->countdown = 0;
            }
        }
    }

    public function render()
    {
        return view('livewire.attendance.q-r-code-display');
    }
}
