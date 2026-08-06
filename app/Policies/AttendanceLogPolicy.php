<?php

namespace App\Policies;

use App\Models\AttendanceLog;
use App\Models\User;

class AttendanceLogPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, AttendanceLog $attendanceLog): bool
    {
        if ($user->role === 'mahasiswa') {
            return $attendanceLog->student?->user_id === $user->id;
        }

        return $user->isAdmin() || $user->isPembimbing();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, AttendanceLog $attendanceLog): bool
    {
        return true;
    }

    public function delete(User $user, AttendanceLog $attendanceLog): bool
    {
        return true;
    }
}
