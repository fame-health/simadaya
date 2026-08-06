<?php

namespace App\Events;

use App\Models\AttendanceSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttendanceTokenUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public AttendanceSession $session,
        public string $token,
        public string $expiredAt,
        public int $countdown = 10
    ) {}

    public function broadcastOn(): array
    {
        // Use a private channel for the specific session
        return [
            new PrivateChannel('attendance-session.' . $this->session->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'token.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'session_id' => $this->session->id,
            'token' => $this->token,
            'expired_at' => $this->expiredAt,
            'countdown' => $this->countdown,
        ];
    }
}
