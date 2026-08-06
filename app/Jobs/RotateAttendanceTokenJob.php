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

        // Check if session has been active for more than 2 hours
        if ($session->started_at && $session->started_at->diffInHours(now()) >= 2) {
            $session->update([
                'status' => 'inactive',
                'ended_at' => now(),
            ]);

            // You might want to broadcast a 'session.closed' event here if needed
            return;
        }

        try {
            DB::transaction(function () use ($session, $tokenGenerator) {
                $newToken = $tokenGenerator->generate();
                $expiresAt = now()->addSeconds(10);

                $session->update([
                    'current_token' => $newToken,
                    'expires_at' => $expiresAt,
                    'last_rotated_at' => now(),
                ]);

                broadcast(new AttendanceTokenUpdated(
                    $session,
                    $newToken,
                    $expiresAt->toDateTimeString()
                ));
            });
        } catch (\Exception $e) {
            Log::error("Failed to rotate token for session {$this->sessionId}: " . $e->getMessage());
        }
    }
}
