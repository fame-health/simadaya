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
        // 1. Selalu kurangi angka di layar
        if ($this->countdown > 0) {
            $this->countdown--;
        }

        // 2. Setiap kali mencapai 0, ambil data TERBARU dari database
        if ($this->countdown <= 0) {
            $this->session->refresh();

            if ($this->session->current_token !== $this->token) {
                $this->token = $this->session->current_token;
                $this->expiredAt = $this->session->expires_at->toDateTimeString();
                $this->countdown = 10; // RESET KE 10 DETIK
                $this->generateQRCode(app(QRCodeService::class));
            } else {
                $this->countdown = 0;
            }
        }
    }

    public function render()
    {
        return view('livewire.attendance.q-r-code-display');
    }
}
