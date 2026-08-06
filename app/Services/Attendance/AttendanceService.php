<?php

namespace App\Services\Attendance;

use App\Events\AttendanceTokenUpdated;
use App\Models\AttendanceLog;
use App\Models\AttendanceSession;
use App\Models\Location;
use App\Models\Mahasiswa;
use App\Models\Pembimbing;
use App\Models\User;
use App\Repositories\AttendanceLogRepository;
use App\Repositories\AttendanceSessionRepository;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class AttendanceService
{
    public function __construct(
        private readonly AttendanceSessionRepository $sessions,
        private readonly AttendanceLogRepository $logs,
        private readonly AttendanceValidationService $validator,
        private readonly TokenGeneratorService $tokens,
        private readonly QRCodeService $qrCodes,
    ) {
    }

    public function startSession(User $user, array $data): AttendanceSession
    {
        return DB::transaction(function () use ($user, $data): AttendanceSession {
            $mentorId = $this->resolveMentorId($user, $data['mentor_id'] ?? null);
            $locationId = (int) ($data['location_id'] ?? 0);

            if (! Location::query()->whereKey($locationId)->where('is_active', true)->exists()) {
                throw ValidationException::withMessages([
                    'location_id' => 'Lokasi absensi tidak aktif atau tidak ditemukan.',
                ]);
            }

            $sessionDate = Carbon::parse($data['session_date'] ?? now(config('attendance.timezone')), config('attendance.timezone'))->toDateString();
            $sessionName = (string) ($data['session_name'] ?? 'pagi');
            $startsAt = Carbon::parse($data['attendance_start_at'] ?? now(), config('attendance.timezone'))
                ->timezone(config('app.timezone'));
            $endsAt = Carbon::parse($data['attendance_end_at'] ?? $startsAt->copy()->addHours(2), config('attendance.timezone'))
                ->timezone(config('app.timezone'));

            if ($endsAt->lte($startsAt)) {
                throw ValidationException::withMessages([
                    'attendance_end_at' => 'Jam selesai absensi harus setelah jam mulai.',
                ]);
            }

            $activeDuplicate = AttendanceSession::query()
                ->active()
                ->where('mentor_id', $mentorId)
                ->where('session_date', $sessionDate)
                ->where('session_name', $sessionName)
                ->lockForUpdate()
                ->exists();

            if ($activeDuplicate) {
                throw ValidationException::withMessages([
                    'session_name' => 'Sesi absensi dengan pembimbing, tanggal, dan sesi yang sama masih aktif.',
                ]);
            }

            $now = now();
            $rotationInterval = (int) config('attendance.token_rotation_seconds', 10);
            $token = $this->tokens->generate();

            return $this->sessions->create([
                'mentor_id' => $mentorId,
                'location_id' => $locationId,
                'session_name' => $sessionName,
                'session_date' => $sessionDate,
                'current_token' => $token,
                'expires_at' => $now->copy()->addSeconds($rotationInterval),
                'started_at' => $now,
                'attendance_start_at' => $startsAt,
                'attendance_end_at' => $endsAt,
                'rotation_interval_seconds' => $rotationInterval,
                'last_rotated_at' => $now,
                'status' => AttendanceSession::STATUS_ACTIVE,
            ]);
        });
    }

    public function endSession(AttendanceSession $session, User $user): AttendanceSession
    {
        $this->ensureCanManage($session, $user);

        return DB::transaction(fn (): AttendanceSession => $this->sessions->end(
            $this->sessions->lockById($session->id) ?? $session
        ));
    }

    public function snapshot(AttendanceSession $session): array
    {
        $session->refresh();

        if (! $session->isActive()) {
            return [
                'session' => $session,
                'payload' => null,
                'qr_image' => null,
                'expires_at' => null,
                'seconds_remaining' => 0,
            ];
        }

        if (! $session->current_token || ! $session->expires_at || $session->expires_at->lte(now())) {
            return $this->rotateTokenById($session->id, true) ?? [
                'session' => $session->refresh(),
                'payload' => null,
                'qr_image' => null,
                'expires_at' => null,
                'seconds_remaining' => 0,
            ];
        }

        $payload = $this->qrCodes->payloadForSession($session);

        return [
            'session' => $session,
            'payload' => $payload,
            'qr_image' => $this->qrCodes->dataUriForPayload($payload),
            'expires_at' => $session->expires_at->toIso8601String(),
            'seconds_remaining' => $session->secondsUntilTokenExpires(),
        ];
    }

    public function rotateDueTokens(?int $sessionId = null): int
    {
        $rotated = 0;

        foreach ($this->sessions->dueForRotationIds($sessionId) as $dueSessionId) {
            if ($this->rotateTokenById((int) $dueSessionId) !== null) {
                $rotated++;
            }
        }

        return $rotated;
    }

    public function rotateTokenById(int $sessionId, bool $force = false): ?array
    {
        $lockSeconds = max(2, (int) config('attendance.token_rotation_seconds', 10) - 1);
        $lock = Cache::lock("attendance-session-token:{$sessionId}", $lockSeconds);

        try {
            return $lock->block(1, function () use ($sessionId, $force): ?array {
                return DB::transaction(function () use ($sessionId, $force): ?array {
                    $session = $this->sessions->lockById($sessionId);

                    if (! $session || ! $session->isActive()) {
                        return null;
                    }

                    if ($session->attendance_end_at && $session->attendance_end_at->lte(now())) {
                        $this->sessions->end($session);

                        return null;
                    }

                    if (! $force && $session->expires_at && $session->expires_at->gt(now())) {
                        return null;
                    }

                    $token = $this->tokens->generate();
                    $expiresAt = now()->addSeconds($session->rotation_interval_seconds ?: (int) config('attendance.token_rotation_seconds', 10));
                    $session = $this->sessions->updateToken($session, $token, $expiresAt);
                    $payload = $this->qrCodes->payloadForSession($session);
                    $qrImage = $this->qrCodes->dataUriForPayload($payload);
                    $snapshot = [
                        'session' => $session,
                        'payload' => $payload,
                        'qr_image' => $qrImage,
                        'expires_at' => $session->expires_at->toIso8601String(),
                        'seconds_remaining' => $session->secondsUntilTokenExpires(),
                    ];

                    DB::afterCommit(fn () => event(new AttendanceTokenUpdated(
                        sessionId: $session->id,
                        payload: $payload,
                        qrImage: $qrImage,
                    )));

                    return $snapshot;
                });
            });
        } catch (Throwable $exception) {
            Log::warning('attendance.token_rotation_failed', [
                'session_id' => $sessionId,
                'message' => $exception->getMessage(),
            ]);

            return null;
        } finally {
            optional($lock)->release();
        }
    }

    public function recordScan(User $user, string|array $rawPayload, Request $request, ?string $browser = null): AttendanceLog
    {
        $student = $this->resolveStudent($user);
        $payload = $this->qrCodes->parsePayload($rawPayload);

        return DB::transaction(function () use ($student, $payload, $request, $browser): AttendanceLog {
            $session = $this->sessions->lockById($payload['session_id']);

            if (! $session) {
                throw new AttendanceValidationException('session_not_found', 'Sesi absensi tidak ditemukan.');
            }

            $this->validator->validateOrFail($session, $student, $payload, $request, $browser);

            try {
                $log = $this->logs->createPresent([
                    'session_id' => $session->id,
                    'student_id' => $student->id,
                    'token' => $this->tokens->hash($payload['token']),
                    'scan_time' => now(),
                    'browser' => $browser ?: $request->userAgent(),
                    'ip_address' => $request->ip(),
                    'device_hash' => $this->validator->deviceHash($request, $browser),
                ]);
            } catch (QueryException $exception) {
                if ($this->isDuplicateAttendance($exception)) {
                    throw new AttendanceValidationException('already_attended', 'Anda sudah melakukan absensi pada sesi ini.');
                }

                throw $exception;
            }

            Log::info('attendance.scan_accepted', [
                'session_id' => $session->id,
                'student_id' => $student->id,
                'ip' => $request->ip(),
            ]);

            return $log;
        });
    }

    private function resolveMentorId(User $user, mixed $requestedMentorId): int
    {
        if ($user->isAdmin()) {
            $mentorId = (int) $requestedMentorId;

            if (! Pembimbing::query()->whereKey($mentorId)->exists()) {
                throw ValidationException::withMessages([
                    'mentor_id' => 'Pembimbing tidak ditemukan.',
                ]);
            }

            return $mentorId;
        }

        if ($user->isPembimbing() && $user->pembimbing) {
            return $user->pembimbing->id;
        }

        throw ValidationException::withMessages([
            'mentor_id' => 'Akun ini belum memiliki data pembimbing.',
        ]);
    }

    private function resolveStudent(User $user): Mahasiswa
    {
        if (! $user->isMahasiswa() || ! $user->mahasiswa) {
            throw new AttendanceValidationException('student_profile_missing', 'Akun ini belum memiliki data peserta magang.');
        }

        return $user->mahasiswa;
    }

    private function ensureCanManage(AttendanceSession $session, User $user): void
    {
        if ($user->isAdmin()) {
            return;
        }

        if ($user->isPembimbing() && $session->mentor?->user_id === $user->id) {
            return;
        }

        throw new AttendanceValidationException('forbidden', 'Anda tidak berwenang mengelola sesi absensi ini.', 403);
    }

    private function isDuplicateAttendance(QueryException $exception): bool
    {
        return (string) $exception->getCode() === '23000'
            || str_contains(strtolower($exception->getMessage()), 'unique');
    }
}
