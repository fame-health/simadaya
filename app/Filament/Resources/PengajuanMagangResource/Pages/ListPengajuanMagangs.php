<?php

namespace App\Filament\Resources\PengajuanMagangResource\Pages;

use App\Filament\Resources\PengajuanMagangResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use App\Models\PengajuanMagang;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class ListPengajuanMagangs extends ListRecords
{
    protected static string $resource = PengajuanMagangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function mount(): void
    {
        parent::mount();

        $user = Auth::user();

        // Jika mahasiswa, redirect ke halaman view pengajuan miliknya
        if ($user && $user->role === 'mahasiswa') {
            $mahasiswa = $user->mahasiswa;

            if ($mahasiswa) {
                $pengajuan = PengajuanMagang::where('mahasiswa_id', $mahasiswa->id)->latest()->first();

                if ($pengajuan) {
                    // Kalau sudah ada pengajuan → arahkan ke halaman view
                    $this->redirect(PengajuanMagangResource::getUrl('view', ['record' => $pengajuan]));
                } else {
                    // Kalau belum ada pengajuan → arahkan ke halaman create
                    Notification::make()
                        ->title('Silakan buat pengajuan magang terlebih dahulu.')
                        ->success()
                        ->send();

                    $this->redirect(PengajuanMagangResource::getUrl('create'));
                }
            }
        }
    }

    public function getTitle(): string
    {
        return 'Data Pengajuan Magang';
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua')
                ->badge(PengajuanMagang::count())
                ->badgeColor('gray'),

            'pending' => Tab::make('Pending')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'pending'))
                ->badge(PengajuanMagang::where('status', 'pending')->count())
                ->badgeColor('warning'),

            'ditolak' => Tab::make('Ditolak')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'ditolak'))
                ->badge(PengajuanMagang::where('status', 'ditolak')->count())
                ->badgeColor('danger'),

            'diterima' => Tab::make('Diterima')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'diterima'))
                ->badge(PengajuanMagang::where('status', 'diterima')->count())
                ->badgeColor('success'),

            'selesai' => Tab::make('Selesai')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'selesai'))
                ->badge(PengajuanMagang::where('status', 'selesai')->count())
                ->badgeColor('info'),
        ];
    }
}
