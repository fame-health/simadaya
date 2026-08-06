<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use App\Jobs\RotateAttendanceTokenJob;
use App\Models\AttendanceSession;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Rotate tokens for all active sessions every 10 seconds
Schedule::call(function () {
    $activeSessions = AttendanceSession::where('status', 'active')->get();

    foreach ($activeSessions as $session) {
        RotateAttendanceTokenJob::dispatch($session->id);
    }
})->everyTenSeconds();

