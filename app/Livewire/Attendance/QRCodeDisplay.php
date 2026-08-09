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
    public ?string $token = null;
    public ?string $expiredAt = null;
    public int $countdown = 10;

    public function mount(AttendanceSession $session, QRCodeService $qrCodeService)
    {
        $this->session = $session;
        $this->token = $session->current_token ?? '';
        $this->expiredAt = $session->expires_at ? $session->expires_at->toDateTimeString() : now()->toDateTimeString();
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
        $this->countdown = 10;
        $this->generateQRCode(app(QRCodeService::class));
    }

    public function decrementCountdown()
    {
        // Ganti refresh() dengan query baru langsung ke DB untuk menghindari cache model
        $freshSession = AttendanceSession::find($this->session->id);

        if (!$freshSession) {
            return;
        }

        // Update instance session agar render() menggunakan data terbaru
        $this->session = $freshSession;

        // Jika sesi sudah tidak aktif, hentikan countdown di 0
        if ($this->session->status !== 'active') {
            $this->countdown = 0;
            return;
        }

        // 3. Cek apakah token di database SUDAH BERUBAH
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
                $this->countdown = 0;
            }
        }
    }

    public function render()
    {
        // Pastikan render selalu mengambil data paling segar dari DB
        $this->session = AttendanceSession::find($this->session->id);

        return view('livewire.attendance.q-r-code-display');
    }
}
