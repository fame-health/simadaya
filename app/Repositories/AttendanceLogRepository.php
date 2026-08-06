<?php

namespace App\Repositories;

use App\Models\AttendanceLog;
use Illuminate\Database\Eloquent\Builder;

class AttendanceLogRepository
{
    public function hasStudentAttendance(int $sessionId, int $studentId): bool
    {
        return AttendanceLog::query()
            ->where('session_id', $sessionId)
            ->where('student_id', $studentId)
            ->exists();
    }

    public function createPresent(array $attributes): AttendanceLog
    {
        return AttendanceLog::create([
            ...$attributes,
            'status' => AttendanceLog::STATUS_PRESENT,
        ]);
    }

    public function queryForSession(int $sessionId): Builder
    {
        return AttendanceLog::query()
            ->where('session_id', $sessionId)
            ->with(['student.user', 'session.location', 'session.mentor.user']);
    }
}
