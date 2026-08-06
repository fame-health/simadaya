<?php

namespace App\Services\Attendance;

use RuntimeException;

class AttendanceValidationException extends RuntimeException
{
    public function __construct(
        public readonly string $attendanceStatus,
        string $message,
        int $code = 422,
    ) {
        parent::__construct($message, $code);
    }
}
