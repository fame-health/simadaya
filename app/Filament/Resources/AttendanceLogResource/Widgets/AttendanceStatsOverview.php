<?php

namespace App\Filament\Resources\AttendanceLogResource\Widgets;

use App\Models\AttendanceLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class AttendanceStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();

        if (!$user->isMahasiswa()) {
            return [];
        }

        $studentId = $user->mahasiswa->id;

        $presentCount = AttendanceLog::where('student_id', $studentId)->where('status', 'present')->count();
        $permitCount = AttendanceLog::where('student_id', $studentId)->where('status', 'permit')->count();
        $sickCount = AttendanceLog::where('student_id', $studentId)->where('status', 'sick')->count();

        return [
            Stat::make('Kehadiran', $presentCount)
                ->value($presentCount . ' Hari')
                ->description('Total kehadiran terverifikasi')
                ->descriptionIcon('heroicon-m-check-badge')
                ->chart([7, 3, 4, 5, 6, 3, $presentCount])
                ->color('success'),
            Stat::make('Izin Magang', $permitCount)
                ->value($permitCount . ' Kali')
                ->description('Ketidakhadiran berizin')
                ->descriptionIcon('heroicon-m-document-text')
                ->chart([1, 2, 1, 0, 2, 1, $permitCount])
                ->color('warning'),
            Stat::make('Sakit', $sickCount)
                ->value($sickCount . ' Kali')
                ->description('Ketidakhadiran karena sakit')
                ->descriptionIcon('heroicon-m-heart')
                ->chart([0, 1, 0, 1, 0, 0, $sickCount])
                ->color('danger'),
        ];
    }
}
