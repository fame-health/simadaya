<?php

namespace App\Listeners;

use App\Events\AttendanceTokenUpdated;
use Illuminate\Support\Facades\Log;

class LogAttendanceTokenUpdated
{
    public function handle(AttendanceTokenUpdated $event): void
    {
        Log::debug('attendance.token_updated', [
            'session_id' => $event->sessionId,
            'expired_at' => $event->payload['expired_at'] ?? null,
        ]);
    }
}
