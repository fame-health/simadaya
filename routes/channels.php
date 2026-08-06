<?php

use App\Models\AttendanceSession;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('attendance-session.{sessionId}', function ($user, $sessionId) {
    $session = AttendanceSession::find($sessionId);

    if (!$session) return false;

    // Mentor who created the session or admin can listen
    return (int) $user->id === (int) $session->mentor_id || $user->role === 'admin';
});
