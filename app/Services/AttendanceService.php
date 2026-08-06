<?php

namespace App\Services;

use App\Models\AttendanceLog;
use App\Models\AttendanceSession;
use App\Models\Mahasiswa;
use App\Models\Pembimbing;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class AttendanceService
{
    public function __construct(
        protected AttendanceValidationService $validationService,
        protected TokenGeneratorService $tokenGenerator
    ) {}

    /**
     * Start a new attendance session.
     */
    public function startSession(array $data, Pembimbing $mentor): AttendanceSession
    {
        return DB::transaction(function () use ($data, $mentor) {
            $token = $this->tokenGenerator->generate();

            return AttendanceSession::create([
                'mentor_id' => $mentor->id,
                'location_id' => $data['location_id'],
                'session_name' => $data['session_name'] ?? 'Absensi Harian',
                'session_date' => $data['session_date'] ?? now()->toDateString(),
                'current_token' => $token,
                'expires_at' => now()->addSeconds(10),
                'started_at' => now(),
                'status' => 'active',
            ]);
        });
    }

    /**
     * Process attendance from QR scan.
     */
    public function processScan(int $sessionId, string $token, string $expiredAt, Mahasiswa $student): array
    {
        $session = AttendanceSession::lockForUpdate()->find($sessionId);

        if (!$session) {
            return ['success' => false, 'message' => 'Sesi tidak ditemukan.'];
        }

        $validation = $this->validationService->validateScan($session, $student, $token, $expiredAt);

        if (!$validation['success']) {
            return $validation;
        }

        return DB::transaction(function () use ($session, $student, $token) {
            AttendanceLog::create([
                'session_id' => $session->id,
                'student_id' => $student->id,
                'token' => $token,
                'scan_time' => now(),
                'browser' => Request::header('User-Agent'),
                'ip_address' => Request::ip(),
                'status' => 'present',
            ]);

            return ['success' => true, 'message' => 'Absensi berhasil dicatat.'];
        });
    }

    /**
     * End a session.
     */
    public function endSession(AttendanceSession $session): bool
    {
        return $session->update([
            'status' => 'inactive',
            'ended_at' => now(),
        ]);
    }
}
