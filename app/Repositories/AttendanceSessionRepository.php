<?php

namespace App\Repositories;

use App\Models\AttendanceSession;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AttendanceSessionRepository
{
    public function create(array $attributes): AttendanceSession
    {
        return AttendanceSession::create($attributes);
    }

    public function activeQuery(): Builder
    {
        return AttendanceSession::query()->active();
    }

    public function lockById(int $sessionId): ?AttendanceSession
    {
        return AttendanceSession::query()
            ->whereKey($sessionId)
            ->lockForUpdate()
            ->first();
    }

    public function dueForRotationIds(?int $sessionId = null): Collection
    {
        $now = now();

        return AttendanceSession::query()
            ->active()
            ->when($sessionId, fn (Builder $query): Builder => $query->whereKey($sessionId))
            ->where(function (Builder $query) use ($now): void {
                $query->whereNull('attendance_end_at')
                    ->orWhere('attendance_end_at', '>', $now);
            })
            ->where(function (Builder $query) use ($now): void {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '<=', $now);
            })
            ->pluck('id');
    }

    public function updateToken(AttendanceSession $session, string $token, Carbon $expiresAt): AttendanceSession
    {
        $session->forceFill([
            'current_token' => $token,
            'expires_at' => $expiresAt,
            'last_rotated_at' => now(),
            'status' => AttendanceSession::STATUS_ACTIVE,
        ])->save();

        return $session->refresh();
    }

    public function end(AttendanceSession $session): AttendanceSession
    {
        $session->forceFill([
            'current_token' => null,
            'expires_at' => now(),
            'ended_at' => now(),
            'status' => AttendanceSession::STATUS_ENDED,
        ])->save();

        return $session->refresh();
    }
}
