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
        return DB::transaction(function () use ($sessionId, $token, $expiredAt, $student): array {
            $session = AttendanceSession::lockForUpdate()->find($sessionId);

            if (! $session) {
                return ['success' => false, 'message' => 'Sesi tidak ditemukan.'];
            }

            $attendance = AttendanceLog::query()
                ->where('student_id', $student->id)
                ->where('status', AttendanceLog::STATUS_PRESENT)
                ->whereDate('scan_time', now()->toDateString())
                ->lockForUpdate()
                ->first();
            $type = $attendance ? 'out' : 'in';

            $validation = $this->validationService->validateScan($session, $student, $token, $expiredAt, $type);
            if (! $validation['success']) {
                return $validation;
            }

            if ($type === 'out') {
                $checkOutTime = now();
                $attendance->update(['check_out_time' => $checkOutTime]);

                return [
                    'success' => true,
                    'type' => 'out',
                    'check_in_time' => $attendance->scan_time?->format('H:i'),
                    'check_out_time' => $checkOutTime->format('H:i'),
                    'message' => 'Absensi kepulangan berhasil dicatat.',
                ];
            }

            $checkInTime = now();
            $attendance = AttendanceLog::create([
                'session_id' => $session->id,
                'student_id' => $student->id,
                'token' => $token,
                'scan_time' => $checkInTime,
                'browser' => Request::header('User-Agent'),
                'ip_address' => Request::ip(),
                'status' => AttendanceLog::STATUS_PRESENT,
            ]);

            return [
                'success' => true,
                'type' => 'in',
                'check_in_time' => $attendance->scan_time->format('H:i'),
                'check_out_time' => null,
                'message' => 'Absensi kehadiran berhasil dicatat.',
            ];
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
