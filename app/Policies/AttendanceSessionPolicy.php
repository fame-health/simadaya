<?php

namespace App\Policies;

use App\Models\AttendanceSession;
use App\Models\User;

class AttendanceSessionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isPembimbing();
    }

    public function view(User $user, AttendanceSession $attendanceSession): bool
    {
        return $user->isAdmin()
            || ($user->isPembimbing() && $attendanceSession->mentor?->user_id === $user->id);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, AttendanceSession $attendanceSession): bool
    {
        return true;
    }

    public function delete(User $user, AttendanceSession $attendanceSession): bool
    {
        return true;
    }
}
