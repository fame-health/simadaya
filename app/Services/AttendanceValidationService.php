<?php

namespace App\Services;

use App\Models\AttendanceLog;
use App\Models\AttendanceSession;
use App\Models\Mahasiswa;
use Carbon\Carbon;

class AttendanceValidationService
{
    /**
     * Validate the attendance scan.
     *
     * @return array{success: bool, message: string}
     */
    public function validateScan(
        AttendanceSession $session,
        Mahasiswa $student,
        string $token,
        string $expiredAt,
        string $type = 'in',
    ): array
    {
        // 1. Check if session is still active
        if ($session->status !== 'active') {
            return ['success' => false, 'message' => 'Sesi absensi sudah tidak aktif.'];
        }

        if ($session->session_date && ! $session->session_date->isToday()) {
            return ['success' => false, 'message' => 'QR Code ini bukan untuk absensi hari ini.'];
        }

        // 2. Check if token matches current token
        if ($session->current_token !== $token) {
            return ['success' => false, 'message' => 'QR Code sudah tidak berlaku (Token Expired).'];
        }

        // 3. Check if token has expired based on time
        if (now()->isAfter(Carbon::parse($expiredAt))) {
            return ['success' => false, 'message' => 'QR Code sudah kadaluarsa.'];
        }

        if ($type === 'out') {
            $attendance = AttendanceLog::query()
                ->where('student_id', $student->id)
                ->where('status', AttendanceLog::STATUS_PRESENT)
                ->whereDate('scan_time', now()->toDateString())
                ->first();

            if (! $attendance) {
                return ['success' => false, 'message' => 'Anda belum melakukan absensi kehadiran hari ini.'];
            }

            if ($attendance->check_out_time) {
                return ['success' => false, 'message' => 'Absensi kepulangan hari ini sudah tercatat.'];
            }
        } else {
            $alreadyAttended = AttendanceLog::query()
                ->where('student_id', $student->id)
                ->whereDate('scan_time', now()->toDateString())
                ->exists();

            if ($alreadyAttended) {
                return ['success' => false, 'message' => 'Anda sudah melakukan absensi kehadiran hari ini.'];
            }
        }

        // 5. Check if session time is still valid (started_at / ended_at)
        if ($session->ended_at && now()->isAfter($session->ended_at)) {
            return ['success' => false, 'message' => 'Waktu sesi absensi telah berakhir.'];
        }

        return ['success' => true, 'message' => 'Validasi berhasil.'];
    }
}
