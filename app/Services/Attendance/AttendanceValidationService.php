<?php

namespace App\Services\Attendance;

use App\Models\AttendanceSession;
use App\Models\Mahasiswa;
use App\Repositories\AttendanceLogRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AttendanceValidationService
{
    public function __construct(
        private readonly AttendanceLogRepository $logs,
    ) {
    }

    /**
     * @param  array{session_id:int,token:string,expired_at:string}  $payload
     */
    public function validateOrFail(
        AttendanceSession $session,
        Mahasiswa $student,
        array $payload,
        Request $request,
        ?string $browser = null,
    ): void {
        $now = now();

        if (! $session->isActive()) {
            throw new AttendanceValidationException('inactive_session', 'Sesi absensi tidak aktif.');
        }

        if ((int) $payload['session_id'] !== (int) $session->id) {
            throw new AttendanceValidationException('session_mismatch', 'QR Code tidak sesuai dengan sesi absensi.');
        }

        if (! $session->current_token || ! hash_equals($session->current_token, $payload['token'])) {
            throw new AttendanceValidationException('invalid_token', 'Token QR Code sudah tidak berlaku.');
        }

        $payloadExpiredAt = Carbon::parse($payload['expired_at'], config('attendance.timezone'))
            ->timezone(config('app.timezone'));

        if ($payloadExpiredAt->addSeconds((int) config('attendance.scan_tolerance_seconds'))->lte($now)) {
            throw new AttendanceValidationException('expired_qr', 'QR Code sudah kedaluwarsa.');
        }

        if (! $session->expires_at || $session->expires_at->lte($now)) {
            throw new AttendanceValidationException('expired_qr', 'QR Code sudah kedaluwarsa.');
        }

        if ($session->attendance_start_at && $session->attendance_start_at->gt($now)) {
            throw new AttendanceValidationException('outside_time_window', 'Jam absensi belum dimulai.');
        }

        if ($session->attendance_end_at && $session->attendance_end_at->lt($now)) {
            throw new AttendanceValidationException('outside_time_window', 'Jam absensi sudah berakhir.');
        }

        if ($this->logs->hasStudentAttendance($session->id, $student->id)) {
            throw new AttendanceValidationException('already_attended', 'Anda sudah melakukan absensi pada sesi ini.');
        }

        if ($this->usesSingleBrowserRule() && ! $this->browserIsAllowed($student, $request, $browser)) {
            throw new AttendanceValidationException('invalid_device', 'Browser atau perangkat tidak sesuai.');
        }
    }

    private function usesSingleBrowserRule(): bool
    {
        return (bool) config('attendance.device.enforce_single_browser_per_student', false);
    }

    private function browserIsAllowed(Mahasiswa $student, Request $request, ?string $browser): bool
    {
        $deviceHash = $this->deviceHash($request, $browser);

        $previousHash = $student->attendanceLogs()
            ->whereNotNull('device_hash')
            ->latest('scan_time')
            ->value('device_hash');

        return $previousHash === null || hash_equals($previousHash, $deviceHash);
    }

    public function deviceHash(Request $request, ?string $browser): string
    {
        return hash('sha256', implode('|', [
            $browser ?: $request->userAgent() ?: 'unknown-browser',
            $request->headers->get('accept-language', 'unknown-language'),
            $request->headers->get('sec-ch-ua-platform', 'unknown-platform'),
        ]));
    }
}
