<?php

namespace App\Filament\Resources\MahasiswaResource\Pages;

use App\Filament\Resources\MahasiswaResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class ListMahasiswas extends ListRecords
{
    protected static string $resource = MahasiswaResource::class;

    /**
     * Mount page
     */
    public function mount(): void
    {
        parent::mount();

        $user = Auth::user();

        if (!$user) {
            return; // Guest, tidak melakukan apa-apa
        }

        // Jika user adalah mahasiswa
        if ($user->role === \App\Models\User::ROLE_MAHASISWA) {

            // Cek apakah mahasiswa sudah punya data
            if ($user->mahasiswa) {
                // Sudah punya → langsung redirect ke view Mahasiswa
                $this->redirect(
                    MahasiswaResource::getUrl('view', [
                        'record' => $user->mahasiswa->id,
                    ])
                );
            } else {
                // Belum punya → redirect ke create Mahasiswa
                Notification::make()
                    ->title('Lengkapi biodata Anda terlebih dahulu!')
                    ->warning()
                    ->send();

                $this->redirect(
                    MahasiswaResource::getUrl('create')
                );
            }
        }

        // Admin tetap bisa lihat list Mahasiswa
    }
}
