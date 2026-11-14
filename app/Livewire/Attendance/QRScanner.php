<?php

namespace App\Livewire\Attendance;

use App\Models\AttendanceLog;
use App\Models\Mahasiswa;
use App\Services\AttendanceService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Throwable;

class QRScanner extends Component
{
    public string $message = '';

    public string $status = '';

    public bool $hasCheckedInToday = false;

    public bool $hasCheckedOutToday = false;

    public bool $isScanning = true;

    public ?string $checkInTime = null;

    public ?string $checkOutTime = null;

    public function mount(): void
    {
        $attendance = $this->todayAttendance();

        if (! $attendance) {
            return;
        }

        $this->hasCheckedInToday = true;
        $this->checkInTime = $attendance->scan_time?->format('H:i');
        $this->isScanning = false;

        if ($attendance->check_out_time) {
            $this->hasCheckedOutToday = true;
            $this->checkOutTime = $attendance->check_out_time->format('H:i');
            $this->status = 'success';
            $this->message = 'Absensi kehadiran dan kepulangan hari ini sudah tercatat.';
        }
    }

    public function startCheckout(): void
    {
        $attendance = $this->todayAttendance();

        if (! $attendance) {
            $this->status = 'error';
            $this->message = 'Anda belum melakukan absensi kehadiran hari ini.';

            return;
        }

        if ($attendance->check_out_time) {
            $this->hasCheckedInToday = true;
            $this->hasCheckedOutToday = true;
            $this->checkInTime = $attendance->scan_time?->format('H:i');
            $this->checkOutTime = $attendance->check_out_time->format('H:i');
            $this->status = 'success';
            $this->message = 'Absensi kepulangan hari ini sudah tercatat.';

            return;
        }

        $this->hasCheckedInToday = true;
        $this->checkInTime = $attendance->scan_time?->format('H:i');
        $this->status = '';
        $this->message = '';
        $this->isScanning = true;
        $this->dispatch('scanner-start');
    }

    public function resetScanner(): void
    {
        $this->status = '';
        $this->message = '';
        $this->isScanning = true;
        $this->dispatch('scanner-reset');
    }

    public function processResult(string $qrData): void
    {
        $this->status = 'scanning';

        try {
            $data = json_decode($qrData, true);
            if (! is_array($data) || ! isset($data['session_id'], $data['token'], $data['expired_at'])) {
                $this->status = 'error';
                $this->message = 'Format QR Code tidak valid.';

                return;
            }

            $user = Auth::user();
            $student = $user ? Mahasiswa::where('user_id', $user->id)->first() : null;
            if (! $student) {
                $this->status = 'error';
                $this->message = 'Data mahasiswa tidak ditemukan.';

                return;
            }

            $result = app(AttendanceService::class)->processScan(
                (int) $data['session_id'],
                (string) $data['token'],
                (string) $data['expired_at'],
                $student,
            );

            if (! $result['success']) {
                $this->status = 'error';
                $this->message = $result['message'];

                return;
            }

            $this->hasCheckedInToday = true;
            $this->hasCheckedOutToday = $result['type'] === 'out';
            $this->checkInTime = $result['check_in_time'];
            $this->checkOutTime = $result['check_out_time'];
            $this->isScanning = false;
            $this->status = 'success';
            $this->message = $result['message'];
        } catch (Throwable $exception) {
            report($exception);
            $this->status = 'error';
            $this->message = 'Absensi belum dapat diproses. Silakan coba scan kembali.';
        }
    }

    private function todayAttendance(): ?AttendanceLog
    {
        $studentId = Auth::user()?->mahasiswa?->id;

        if (! $studentId) {
            return null;
        }

        return AttendanceLog::query()
            ->where('student_id', $studentId)
            ->where('status', AttendanceLog::STATUS_PRESENT)
            ->whereDate('scan_time', now()->toDateString())
            ->latest('scan_time')
            ->first();
    }

    public function render()
    {
        return view('livewire.attendance.q-r-scanner');
    }
}
