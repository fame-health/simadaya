<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class ScanAttendance extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-qr-code';

    protected static ?string $navigationLabel = 'Scan Absensi';

    protected static string $view = 'filament.pages.scan-attendance';

    protected static ?string $title = 'Absensi Magang';

    protected static ?string $navigationGroup = 'PRESENSI';

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user || $user->role !== 'mahasiswa') {
            return false;
        }

        // Hanya mahasiswa dengan status Diterima/Selesai yang bisa melihat menu Scan
        return \App\Models\PengajuanMagang::where('mahasiswa_id', $user->mahasiswa?->id)
            ->whereIn('status', [
                \App\Models\PengajuanMagang::STATUS_DITERIMA,
                \App\Models\PengajuanMagang::STATUS_SELESAI
            ])
            ->exists();
    }
}
