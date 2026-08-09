<?php

namespace App\Filament\Resources\AttendanceSessionResource\Pages;

use App\Filament\Resources\AttendanceSessionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Models\AttendanceSession;
use App\Models\Location;
use Filament\Notifications\Notification;

class ListAttendanceSessions extends ListRecords
{
    protected static string $resource = AttendanceSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('start_session')
                ->label('Mulai Absensi')
                ->color('success')
                ->icon('heroicon-o-play')
                ->action(function () {
                    /** @var \App\Models\User $user */
                    $user = auth()->user();

                    // Admin mungkin tidak punya profile pembimbing secara langsung,
                    // namun AttendanceSession membutuhkan mentor_id.
                    // Jika admin, kita coba cari profile pembimbingnya.
                    $mentorId = $user->pembimbing?->id;

                    // Cari sesi yang sedang aktif untuk mentor ini
                    $activeSession = AttendanceSession::where('mentor_id', $mentorId)
                        ->where('status', 'active')
                        ->first();

                    if (!$activeSession) {
                        // Ambil lokasi pertama yang aktif sebagai default
                        $defaultLocation = Location::where('is_active', true)->first();

                        if (!$defaultLocation) {
                             Notification::make()
                                ->title('Lokasi Tidak Tersedia')
                                ->body('Harap aktifkan minimal satu lokasi di Manajemen Lokasi terlebih dahulu.')
                                ->danger()
                                ->send();
                            return;
                        }

                        // Inisialisasi token pertama agar tidak null
                        $tokenGenerator = app(\App\Services\TokenGeneratorService::class);
                        $initialToken = $tokenGenerator->generate();

                        // Buat sesi baru secara otomatis
                        $activeSession = AttendanceSession::create([
                            'mentor_id' => $mentorId,
                            'location_id' => $defaultLocation->id,
                            'session_name' => 'Absensi Harian - ' . now()->format('d/m/Y'),
                            'session_date' => now(),
                            'status' => 'active',
                            'started_at' => now(),
                            'current_token' => $initialToken,
                            'expires_at' => now()->addSeconds(10),
                            'last_rotated_at' => now(),
                        ]);
                    }

                    // Redirect ke halaman QR
                    return redirect(AttendanceSessionResource::getUrl('view-qr', ['record' => $activeSession]));
                }),
            Actions\CreateAction::make()
                ->label('Buat Sesi Manual')
                ->color('gray')
                ->outlined(),
        ];
    }
}
