<?php

namespace App\Actions\Attendance;

use App\Models\AttendanceSession;
use App\Models\User;
use App\Services\Attendance\AttendanceService;

class StopAttendanceSessionAction
{
    public function __construct(
        private readonly AttendanceService $attendance,
    ) {
    }

    public function execute(AttendanceSession $session, User $user): AttendanceSession
    {
        return $this->attendance->endSession($session, $user);
    }
}
