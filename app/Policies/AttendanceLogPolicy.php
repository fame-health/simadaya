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
        if ($user->isMahasiswa()) {
            return $attendanceLog->student?->user_id === $user->id;
        }

        if ($user->isPembimbing() && $user->pembimbing) {
            $pembimbingId = $user->pembimbing->id;
            return $attendanceLog->session?->mentor_id === $pembimbingId
                || $attendanceLog->student?->pengajuan()
                    ->where('pembimbing_id', $pembimbingId)
                    ->whereIn('status', [
                        \App\Models\PengajuanMagang::STATUS_DITERIMA,
                        \App\Models\PengajuanMagang::STATUS_SELESAI,
                    ])->exists();
        }

        return $user->isAdmin();
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
