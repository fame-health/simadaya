<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceLogResource\Pages;
use App\Models\AttendanceLog;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class AttendanceLogResource extends Resource
{
    protected static ?string $model = AttendanceLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $label = 'Log Absensi';

    protected static ?string $navigationGroup = 'ALUR PELAKSANAAN PKL';

    public static function shouldRegisterNavigation(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        // Admin dan Pembimbing selalu bisa melihat Log Absensi
        if ($user->isAdmin() || $user->isPembimbing()) {
            return true;
        }

        // Mahasiswa hanya bisa melihat Log jika pengajuannya sudah DITERIMA atau SELESAI
        if ($user->isMahasiswa()) {
            return \App\Models\PengajuanMagang::where('mahasiswa_id', $user->mahasiswa?->id)
                ->whereIn('status', [
                    \App\Models\PengajuanMagang::STATUS_DITERIMA,
                    \App\Models\PengajuanMagang::STATUS_SELESAI
                ])
                ->exists();
        }

        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                if (Auth::user()->role === 'mahasiswa') {
                    $query->whereHas('student', function ($q) {
                        $q->where('user_id', Auth::id());
                    });
                }
            })
            ->columns([
                Tables\Columns\TextColumn::make('student.user.name')
                    ->label('Peserta')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('session.session_name')
                    ->label('Sesi')
                    ->sortable(),
                Tables\Columns\TextColumn::make('scan_time')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'present',
                        'danger' => 'failed',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('session')
                    ->relationship('session', 'session_name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendanceLogs::route('/'),
        ];
    }
}
