<?php

namespace App\Filament\Widgets;

use App\Models\PengajuanMagang;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Carbon\Carbon;

class AdminPembimbingDashboardWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        /** @var User|null $user */
        $user = Auth::user();
        $stats = [];
        $now = Carbon::today('Asia/Jakarta'); // Use date only for comparison

        if ($user?->isAdmin()) {
            // Statistik untuk Admin
            $pendingPengajuan = PengajuanMagang::where('status', PengajuanMagang::STATUS_PENDING)->count();
            $completedMahasiswa = PengajuanMagang::where('status', PengajuanMagang::STATUS_SELESAI)
                ->distinct('mahasiswa_id')
                ->count('mahasiswa_id');
            $activeMahasiswa = PengajuanMagang::where('status', PengajuanMagang::STATUS_DITERIMA)
                ->where('tanggal_mulai', '<=', $now)
                ->where('tanggal_selesai', '>=', $now)
                ->distinct('mahasiswa_id')
                ->count('mahasiswa_id');
            $activePembimbing = PengajuanMagang::where('status', PengajuanMagang::STATUS_DITERIMA)
                ->where('tanggal_mulai', '<=', $now)
                ->where('tanggal_selesai', '>=', $now)
                ->whereNotNull('pembimbing_id')
                ->distinct('pembimbing_id')
                ->count('pembimbing_id');
            $totalPengajuan = PengajuanMagang::count();

            $stats = [
                Stat::make('Pengajuan Pending', $pendingPengajuan)
                    ->description('Menunggu persetujuan')
                    ->descriptionIcon('heroicon-m-clock')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('warning')
                    ->extraAttributes([
                        'class' => 'bg-gradient-to-br from-amber-50 to-orange-100 dark:from-gray-800 dark:to-gray-700 border-b-4 border-orange-500 rounded-xl shadow-sm hover:shadow-md transition-all duration-300',
                    ]),

                Stat::make('Mahasiswa Aktif', $activeMahasiswa)
                    ->description('Sedang magang')
                    ->descriptionIcon('heroicon-m-user-group')
                    ->icon('heroicon-o-users')
                    ->color('primary')
                    ->extraAttributes([
                        'class' => 'bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-800 dark:to-gray-700 border-b-4 border-blue-500 rounded-xl shadow-sm hover:shadow-md transition-all duration-300',
                    ]),

                Stat::make('Mahasiswa Selesai', $completedMahasiswa)
                    ->description('Telah selesai')
                    ->descriptionIcon('heroicon-m-check-badge')
                    ->icon('heroicon-o-academic-cap')
                    ->color('success')
                    ->extraAttributes([
                        'class' => 'bg-gradient-to-br from-emerald-50 to-teal-100 dark:from-gray-800 dark:to-gray-700 border-b-4 border-emerald-500 rounded-xl shadow-sm hover:shadow-md transition-all duration-300',
                    ]),

                Stat::make('Pembimbing Aktif', $activePembimbing)
                    ->description('Dosen pengawas')
                    ->descriptionIcon('heroicon-m-user-circle')
                    ->icon('heroicon-o-identification')
                    ->color('info')
                    ->extraAttributes([
                        'class' => 'bg-gradient-to-br from-cyan-50 to-sky-100 dark:from-gray-800 dark:to-gray-700 border-b-4 border-cyan-500 rounded-xl shadow-sm hover:shadow-md transition-all duration-300',
                    ]),

                Stat::make('Total Pengajuan', $totalPengajuan)
                    ->description('Seluruh data masuk')
                    ->descriptionIcon('heroicon-m-document-text')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->color('secondary')
                    ->extraAttributes([
                        'class' => 'bg-gradient-to-br from-purple-50 to-violet-100 dark:from-gray-800 dark:to-gray-700 border-b-4 border-purple-500 rounded-xl shadow-sm hover:shadow-md transition-all duration-300',
                    ]),
            ];
        } elseif ($user?->isPembimbing()) {
            // Statistik untuk Pembimbing
            $pembimbingId = $user->pembimbing?->id;

            if ($pembimbingId) {
                $pendingPengajuan = PengajuanMagang::where('pembimbing_id', $pembimbingId)
                    ->where('status', PengajuanMagang::STATUS_PENDING)
                    ->count();
                $completedMahasiswa = PengajuanMagang::where('pembimbing_id', $pembimbingId)
                    ->where('status', PengajuanMagang::STATUS_SELESAI)
                    ->distinct('mahasiswa_id')
                    ->count('mahasiswa_id');
                $activeMahasiswa = PengajuanMagang::where('pembimbing_id', $pembimbingId)
                    ->where('status', PengajuanMagang::STATUS_DITERIMA)
                    ->where('tanggal_mulai', '<=', $now)
                    ->where('tanggal_selesai', '>=', $now)
                    ->distinct('mahasiswa_id')
                    ->count('mahasiswa_id');
                $totalBimbingan = PengajuanMagang::where('pembimbing_id', $pembimbingId)->count();

                $stats = [
                    Stat::make('Pengajuan Pending', $pendingPengajuan)
                        ->description('Perlu disetujui')
                        ->descriptionIcon('heroicon-m-clock')
                        ->icon('heroicon-o-exclamation-triangle')
                        ->color('warning')
                        ->extraAttributes([
                            'class' => 'bg-gradient-to-br from-orange-50 to-amber-100 dark:from-gray-800 dark:to-gray-700 border-l-4 border-orange-500 rounded-xl shadow-sm hover:scale-[1.02] transition-transform duration-200',
                        ]),

                    Stat::make('Mahasiswa Aktif', $activeMahasiswa)
                        ->description('Sedang dibimbing')
                        ->descriptionIcon('heroicon-m-user-group')
                        ->icon('heroicon-o-users')
                        ->color('primary')
                        ->extraAttributes([
                            'class' => 'bg-gradient-to-br from-blue-50 to-sky-100 dark:from-gray-800 dark:to-gray-700 border-l-4 border-blue-500 rounded-xl shadow-sm hover:scale-[1.02] transition-transform duration-200',
                        ]),

                    Stat::make('Mahasiswa Selesai', $completedMahasiswa)
                        ->description('Telah selesai')
                        ->descriptionIcon('heroicon-m-check-badge')
                        ->icon('heroicon-o-academic-cap')
                        ->color('success')
                        ->extraAttributes([
                            'class' => 'bg-gradient-to-br from-green-50 to-emerald-100 dark:from-gray-800 dark:to-gray-700 border-l-4 border-emerald-500 rounded-xl shadow-sm hover:scale-[1.02] transition-transform duration-200',
                        ]),

                    Stat::make('Total Bimbingan', $totalBimbingan)
                        ->description('Seluruh riwayat')
                        ->descriptionIcon('heroicon-m-chart-bar')
                        ->icon('heroicon-o-chart-bar-square')
                        ->color('danger')
                        ->extraAttributes([
                            'class' => 'bg-gradient-to-br from-rose-50 to-pink-100 dark:from-gray-800 dark:to-gray-700 border-l-4 border-rose-500 rounded-xl shadow-sm hover:scale-[1.02] transition-transform duration-200',
                        ]),
                ];
            } else {
                $stats[] = Stat::make('No Data', '0')
                    ->description('Tidak ada data pembimbing tersedia')
                    ->descriptionIcon('heroicon-m-information-circle')
                    ->color('gray');
            }
        }

        if (empty($stats)) {
            $stats[] = Stat::make('No Data', '0')
                ->description('Tidak ada data yang tersedia saat ini')
                ->descriptionIcon('heroicon-m-information-circle')
                ->color('gray');
        }

        return $stats;
    }

    public static function canView(): bool
    {
        return false;
    }

    protected function getColumns(): int
    {
        return 3;
    }

    protected function getHeading(): string
    {
        /** @var User|null $user */
        $user = Auth::user();

        if ($user?->isAdmin()) {
            return '🎯 Admin Dashboard Overview';
        } elseif ($user?->isPembimbing()) {
            return '👨‍🏫 Pembimbing Dashboard Overview';
        }

        return 'Dashboard Overview';
    }
}
