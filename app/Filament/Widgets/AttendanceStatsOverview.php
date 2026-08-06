<?php

namespace App\Filament\Widgets;

use App\Models\AttendanceLog;
use App\Models\AttendanceSession;
use App\Models\Mahasiswa;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AttendanceStatsOverview extends BaseWidget
{
    public static function canView(): bool
    {
        return false;
    }

    protected function getStats(): array
    {
        $activeSessionsCount = AttendanceSession::where('status', 'active')->count();
        $todayAttendanceCount = AttendanceLog::whereDate('scan_time', now()->toDateString())->count();
        $totalStudents = Mahasiswa::count();

        // Simple calculation for "Not present yet" today
        // This assumes one session per student per day for simplicity
        $notPresentCount = max(0, $totalStudents - $todayAttendanceCount);

        return [
            Stat::make('Sesi Aktif', $activeSessionsCount)
                ->description('Sedang berjalan')
                ->descriptionIcon('heroicon-m-play')
                ->color('success')
                ->extraAttributes([
                    'class' => 'bg-white dark:bg-gray-800 border-l-4 border-emerald-500 rounded-xl shadow-sm',
                ]),
            Stat::make('Peserta Hadir (Hari Ini)', $todayAttendanceCount)
                ->description('Total scan berhasil')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary')
                ->extraAttributes([
                    'class' => 'bg-white dark:bg-gray-800 border-l-4 border-blue-500 rounded-xl shadow-sm',
                ]),
            Stat::make('Belum Hadir', $notPresentCount)
                ->description('Estimasi tersisa')
                ->descriptionIcon('heroicon-m-user-minus')
                ->color('warning')
                ->extraAttributes([
                    'class' => 'bg-white dark:bg-gray-800 border-l-4 border-amber-500 rounded-xl shadow-sm',
                ]),
        ];
    }
}
