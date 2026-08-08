<?php

namespace App\Jobs;

use App\Events\AttendanceTokenUpdated;
use App\Models\AttendanceSession;
use App\Services\TokenGeneratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RotateAttendanceTokenJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected int $sessionId
    ) {}

    public function handle(TokenGeneratorService $tokenGenerator): void
    {
        $session = AttendanceSession::find($this->sessionId);

        if (!$session || $session->status !== 'active') {
            return;
        }

        // Cek durasi 2 jam
        if ($session->started_at && $session->started_at->diffInHours(now('Asia/Jakarta')) >= 2) {
            $session->update([
                'status' => 'inactive',
                'ended_at' => now('Asia/Jakarta'),
            ]);
            return;
        }

        try {
            $newToken = $tokenGenerator->generate();
            $expiresAt = now('Asia/Jakarta')->addSeconds(10);

            // GUNAKAN QUERY BUILDER LANGSUNG (Bypass Eloquent Cache)
            \Illuminate\Support\Facades\DB::table('attendance_sessions')
                ->where('id', $this->sessionId)
                ->update([
                    'current_token' => $newToken,
                    'expires_at' => $expiresAt,
                    'last_rotated_at' => now('Asia/Jakarta'),
                ]);

            // Ambil data terbaru untuk broadcast
            $freshSession = AttendanceSession::find($this->sessionId);

            broadcast(new AttendanceTokenUpdated(
                $freshSession,
                $newToken,
                $expiresAt->toDateTimeString()
            ));

        } catch (\Exception $e) {
            Log::error("Failed to rotate token for session {$this->sessionId}: " . $e->getMessage());
        }
    }
}
