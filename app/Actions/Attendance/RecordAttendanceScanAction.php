<?php

namespace App\Actions\Attendance;

use App\Models\AttendanceLog;
use App\Models\User;
use App\Services\Attendance\AttendanceService;
use Illuminate\Http\Request;

class RecordAttendanceScanAction
{
    public function __construct(
        private readonly AttendanceService $attendance,
    ) {
    }

    public function execute(User $user, string|array $payload, Request $request, ?string $browser = null): AttendanceLog
    {
        return $this->attendance->recordScan($user, $payload, $request, $browser);
    }
}
