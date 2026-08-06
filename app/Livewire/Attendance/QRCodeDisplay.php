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

    public function getListeners()
    {
        return [
            "echo-private:attendance-session.{$this->session->id},.token.updated" => 'onTokenUpdated',
        ];
    }

    public function onTokenUpdated($data)
    {
        $this->token = $data['token'];
        $this->expiredAt = $data['expired_at'];
        $this->countdown = $data['countdown'];

        $qrCodeService = app(QRCodeService::class);
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
        if ($this->countdown > 0) {
            $this->countdown--;
        }

        // Cek ke database setiap kali countdown mendekati 0 atau habis
        // Ini menggantikan peran Pusher karena hosting tidak support npm
        if ($this->countdown <= 0) {
            $this->session->refresh();

            if ($this->session->current_token !== $this->token) {
                $this->token = $this->session->current_token;
                $this->expiredAt = $this->session->expires_at->toDateTimeString();
                $this->countdown = 10;
                $this->generateQRCode(app(QRCodeService::class));
            } else {
                // Jika token belum berubah (mungkin scheduler belum jalan),
                // kita tahan di 0s sebentar sampai token baru muncul
                $this->countdown = 0;
            }
        }
    }

    public function render()
    {
        return view('livewire.attendance.q-r-code-display');
    }
}
