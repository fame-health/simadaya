<?php

namespace App\Filament\Resources\AttendanceLogResource\Widgets;

use App\Models\AttendanceLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class AttendanceStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();
        if (!$user) {
            return [];
        }

        app(\App\Services\Attendance\AttendanceService::class)->autoGenerateAlphaLogs();

        $query = AttendanceLog::query();

        if ($user->isMahasiswa()) {
            if (!$user->mahasiswa) {
                return [];
            }
            $query->where('student_id', $user->mahasiswa->id);
        } elseif ($user->isPembimbing() && $user->pembimbing) {
            $pembimbingId = $user->pembimbing->id;
            $query->where(function (Builder $q) use ($pembimbingId) {
                $q->whereHas('student.pengajuan', function ($pq) use ($pembimbingId) {
                    $pq->where('pembimbing_id', $pembimbingId)
                       ->whereIn('status', [
                           \App\Models\PengajuanMagang::STATUS_DITERIMA,
                           \App\Models\PengajuanMagang::STATUS_SELESAI,
                       ]);
                })->orWhereHas('session', fn ($sq) => $sq->where('mentor_id', $pembimbingId));
            });
        } elseif ($user->isAdmin() && session()->has('selected_mentor_id')) {
            $mentorId = session('selected_mentor_id');
            $query->where(function (Builder $q) use ($mentorId) {
                $q->whereHas('student.pengajuan', fn ($pq) => $pq->where('pembimbing_id', $mentorId))
                  ->orWhereHas('session', fn ($sq) => $sq->where('mentor_id', $mentorId));
            });
        }

        $logs = $query->get();

        $presentCount = $logs->where('status', AttendanceLog::STATUS_PRESENT)->count();
        $permitCount = $logs->where('status', AttendanceLog::STATUS_PERMIT)->count();
        $sickCount = $logs->where('status', AttendanceLog::STATUS_SICK)->count();
        $alphaCount = $logs->whereIn('status', [AttendanceLog::STATUS_ALPHA, 'alpa'])->count();
        $total = $logs->count();
        $persentase = $total > 0 ? round(($presentCount / $total) * 100, 1) : 0;

        return [
            Stat::make('Total Hadir', $presentCount . ' Sesi')
                ->description('Kehadiran terverifikasi')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Total Izin', $permitCount . ' Kali')
                ->description('Ketidakhadiran berizin')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning'),
            Stat::make('Total Sakit', $sickCount . ' Kali')
                ->description('Ketidakhadiran sakit')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('danger'),
            Stat::make('Total Alpa', $alphaCount . ' Kali')
                ->description('Tanpa Keterangan')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
            Stat::make('Kehadiran', $persentase . '%')
                ->description('Tingkat Kehadiran')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),
        ];
    }
}
