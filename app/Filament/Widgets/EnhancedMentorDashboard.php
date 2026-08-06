<?php

namespace App\Filament\Widgets;

use App\Models\AttendanceLog;
use App\Models\AttendanceSession;
use App\Models\Mahasiswa;
use App\Models\PengajuanMagang;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EnhancedMentorDashboard extends Widget
{
    protected static string $view = 'filament.widgets.enhanced-mentor-dashboard';

    protected static ?int $sort = -2;

    protected int | string | array $columnSpan = 'full';

    // Enable auto-polling every 5 seconds for real-time updates without refresh
    protected static ?string $pollingInterval = '5s';

    public static function canView(): bool
    {
        $user = Auth::user();
        return $user && ($user->isAdmin() || $user->isPembimbing());
    }

    public function getData(): array
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $now = Carbon::today('Asia/Jakarta');

        $pendingCount = 0;
        $activeCount = 0;
        $completedCount = 0;
        $totalBimbingan = 0;

        // Additional Admin Stats
        $totalStudents = 0;
        $totalMentors = 0;
        $totalActiveSessions = 0;

        if ($user->isAdmin()) {
            $pendingCount = PengajuanMagang::where('status', PengajuanMagang::STATUS_PENDING)->count();
            $activeCount = PengajuanMagang::where('status', PengajuanMagang::STATUS_DITERIMA)
                ->where('tanggal_mulai', '<=', $now)
                ->where('tanggal_selesai', '>=', $now)
                ->count();
            $completedCount = PengajuanMagang::where('status', PengajuanMagang::STATUS_SELESAI)->count();
            $totalBimbingan = PengajuanMagang::count();

            // Admin only data
            $totalStudents = Mahasiswa::count();
            $totalMentors = \App\Models\Pembimbing::count();
            $totalActiveSessions = AttendanceSession::where('status', 'active')->count();
        } elseif ($user->isPembimbing()) {
            $pembimbingId = $user->pembimbing?->id;
            if ($pembimbingId) {
                $pendingCount = PengajuanMagang::where('pembimbing_id', $pembimbingId)->where('status', PengajuanMagang::STATUS_PENDING)->count();
                $activeCount = PengajuanMagang::where('pembimbing_id', $pembimbingId)
                    ->where('status', PengajuanMagang::STATUS_DITERIMA)
                    ->where('tanggal_mulai', '<=', $now)
                    ->where('tanggal_selesai', '>=', $now)
                    ->count();
                $completedCount = PengajuanMagang::where('pembimbing_id', $pembimbingId)->where('status', PengajuanMagang::STATUS_SELESAI)->count();
                $totalBimbingan = PengajuanMagang::where('pembimbing_id', $pembimbingId)->count();
            }
        }

        // Attendance Stats - Filtered for only Accepted Students
        $activeSessions = AttendanceSession::where('status', 'active')->count();
        $todayAttendance = AttendanceLog::whereDate('scan_time', now()->toDateString())->count();

        // Count only students who have an ACCEPTED (DITERIMA) internship status
        $acceptedStudentsCount = PengajuanMagang::where('status', PengajuanMagang::STATUS_DITERIMA)
            ->where('tanggal_mulai', '<=', $now)
            ->where('tanggal_selesai', '>=', $now)
            ->distinct('mahasiswa_id')
            ->count('mahasiswa_id');

        $notPresent = max(0, $acceptedStudentsCount - $todayAttendance);

        return [
            'pending' => $pendingCount,
            'active' => $activeCount,
            'completed' => $completedCount,
            'total' => $totalBimbingan,
            'active_sessions' => $activeSessions,
            'present_today' => $todayAttendance,
            'not_present' => $notPresent,
            'user_name' => $user->name,
            'role' => $user->role,
            'is_admin' => $user->isAdmin(),
            // Admin exclusive
            'total_students' => $totalStudents,
            'total_mentors' => $totalMentors,
            'admin_active_sessions' => $totalActiveSessions,
        ];
    }
}
