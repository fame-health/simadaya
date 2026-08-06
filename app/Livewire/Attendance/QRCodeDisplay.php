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
        // Paksakan refresh sesi dari database setiap kali poll terjadi
        $this->session->refresh();

        // Gunakan timestamp (angka murni) untuk menghindari masalah timezone
        $now = now('Asia/Jakarta')->timestamp;
        $expiresAt = \Illuminate\Support\Carbon::parse($this->session->expires_at, 'Asia/Jakarta')->timestamp;

        $diff = $expiresAt - $now;

        if ($diff > 0) {
            $this->countdown = (int) $diff;
        } else {
            $this->countdown = 0;
        }

        // Jika token di layar berbeda dengan di database, generate QR ulang
        if ($this->session->current_token !== $this->token) {
            $this->token = $this->session->current_token;
            $this->expiredAt = $this->session->expires_at->toDateTimeString();
            $this->generateQRCode(app(QRCodeService::class));
        }
    }

    public function render()
    {
        // Panggil decrement setiap kali render dipicu oleh wire:poll
        $this->decrementCountdown();

        return view('livewire.attendance.q-r-code-display');
    }
}
