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
        // Menggunakan dispatchSync agar langsung dieksekusi saat itu juga
        // Tanpa menunggu antrean (queue), cocok untuk shared hosting
        RotateAttendanceTokenJob::dispatchSync($session->id);
    }
})->everyTenSeconds();

// Verifikasi Laporan Akhir otomatis setiap minggu
Schedule::command('app:auto-verify-laporan')->weekly();

