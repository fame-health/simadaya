<?php

namespace App\Actions\Attendance;

use App\Models\AttendanceSession;
use App\Models\User;
use App\Services\Attendance\AttendanceService;

class StartAttendanceSessionAction
{
    public function __construct(
        private readonly AttendanceService $attendance,
    ) {
    }

    public function execute(User $user, array $data): AttendanceSession
    {
        return $this->attendance->startSession($user, $data);
    }
}
