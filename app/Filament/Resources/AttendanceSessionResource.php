<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceSessionResource\Pages;
use App\Models\AttendanceSession;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AttendanceSessionResource extends Resource
{
    protected static ?string $model = AttendanceSession::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'ALUR PELAKSANAAN PKL';

    protected static ?string $label = 'Sesi Absensi';

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->role !== 'mahasiswa';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Sesi')
                    ->schema([
                        Forms\Components\TextInput::make('session_name')
                            ->required()
                            ->maxLength(50)
                            ->default('Absensi Harian'),
                        Forms\Components\Select::make('location_id')
                            ->relationship('location', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\DatePicker::make('session_date')
                            ->required()
                            ->default(now()),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('session_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('session_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'danger' => 'inactive',
                    ]),
                Tables\Columns\TextColumn::make('logs_count')
                    ->counts('logs')
                    ->label('Peserta Hadir'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Aktif',
                        'inactive' => 'Tidak Aktif',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('view_qr')
                    ->label('Tampilkan QR')
                    ->icon('heroicon-o-qr-code')
                    ->color('success')
                    ->url(fn (AttendanceSession $record): string => static::getUrl('view-qr', ['record' => $record]))
                    ->visible(fn (AttendanceSession $record): bool => $record->status === 'active'),
                Tables\Actions\Action::make('stop_session')
                    ->label('Hentikan')
                    ->icon('heroicon-o-stop')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (AttendanceSession $record) {
                        $record->update([
                            'status' => 'inactive',
                            'ended_at' => now(),
                        ]);
                    })
                    ->visible(fn (AttendanceSession $record): bool => $record->status === 'active'),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendanceSessions::route('/'),
            'create' => Pages\CreateAttendanceSession::route('/create'),
            'edit' => Pages\EditAttendanceSession::route('/{record}/edit'),
            'view-qr' => Pages\ViewQRCode::route('/{record}/qr'),
        ];
    }
}
