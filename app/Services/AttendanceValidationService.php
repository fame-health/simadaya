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
    public function validateScan(AttendanceSession $session, Mahasiswa $student, string $token, string $expiredAt): array
    {
        // 1. Check if session is still active
        if ($session->status !== 'active') {
            return ['success' => false, 'message' => 'Sesi absensi sudah tidak aktif.'];
        }

        // 2. Check if token matches current token
        if ($session->current_token !== $token) {
            return ['success' => false, 'message' => 'QR Code sudah tidak berlaku (Token Expired).'];
        }

        // 3. Check if token has expired based on time
        if (now()->isAfter(Carbon::parse($expiredAt))) {
            return ['success' => false, 'message' => 'QR Code sudah kadaluarsa.'];
        }

        // 4. Check if student has already attended this session
        $alreadyAttended = AttendanceLog::where('session_id', $session->id)
            ->where('student_id', $student->id)
            ->exists();

        if ($alreadyAttended) {
            return ['success' => false, 'message' => 'Anda sudah melakukan absensi untuk sesi ini.'];
        }

        // 4b. Check if student has already attended ANY session today
        $attendedToday = AttendanceLog::where('student_id', $student->id)
            ->whereDate('scan_time', now()->toDateString())
            ->exists();

        if ($attendedToday) {
            return ['success' => false, 'message' => 'Anda sudah melakukan absensi hari ini. Silakan kembali besok!'];
        }

        // 5. Check if session time is still valid (started_at / ended_at)
        if ($session->ended_at && now()->isAfter($session->ended_at)) {
            return ['success' => false, 'message' => 'Waktu sesi absensi telah berakhir.'];
        }

        return ['success' => true, 'message' => 'Validasi berhasil.'];
    }
}
