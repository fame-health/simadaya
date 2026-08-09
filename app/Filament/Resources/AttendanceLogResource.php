<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceLogResource\Pages;
use App\Models\AttendanceLog;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Pembimbing;
use Filament\Tables\Columns\Summarizers\Count;

use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Infolists\Components\IconEntry;

class AttendanceLogResource extends Resource
{
    protected static ?string $model = AttendanceLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $label = 'Log Absensi';

    protected static ?string $navigationGroup = 'PRESENSI';

    protected static ?int $navigationSort = 2;

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
            ->modifyQueryUsing(function (Builder $query) {
                $user = Auth::user();
                if ($user->role === 'mahasiswa') {
                    $query->whereHas('student', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
                }

                // Filter berdasarkan mentor jika admin memilih mentor (menggunakan session)
                if ($user->isAdmin() && session()->has('selected_mentor_id')) {
                    $query->whereHas('session', function ($q) {
                        $q->where('mentor_id', session('selected_mentor_id'));
                    });
                } elseif ($user->isPembimbing()) {
                    $query->whereHas('session', function ($q) use ($user) {
                        $q->where('mentor_id', $user->pembimbing->id);
                    });
                }
            })
            ->columns([
                Tables\Columns\TextColumn::make('student.user.name')
                    ->label('Peserta')
                    ->searchable()
                    ->sortable()
                    ->hidden(fn() => Auth::user()->isMahasiswa()),
                Tables\Columns\TextColumn::make('session.session_name')
                    ->label('Sesi')
                    ->sortable(),
                Tables\Columns\TextColumn::make('scan_time')
                    ->label('Waktu')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status Presensi')
                    ->colors([
                        'success' => 'present',
                        'warning' => 'permit',
                        'danger' => 'sick',
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => 'present',
                        'heroicon-o-document-text' => 'permit',
                        'heroicon-o-exclamation-circle' => 'sick',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'present' => 'HADIR',
                        'permit' => 'IZIN',
                        'sick' => 'SAKIT',
                        default => strtoupper($state),
                    }),
                Tables\Columns\TextColumn::make('reason')
                    ->label('Keterangan Alasan')
                    ->placeholder('Tidak ada keterangan')
                    ->wrap()
                    ->limit(30),
            ])
            ->headerActions([
                Action::make('ajukan_izin')
                    ->label('Ajukan Izin / Sakit')
                    ->icon('heroicon-o-document-plus')
                    ->color('warning')
                    ->hidden(fn() => !Auth::user()->isMahasiswa())
                    ->form([
                        Select::make('status')
                            ->label('Jenis Ketidakhadiran')
                            ->options([
                                'permit' => 'Izin',
                                'sick' => 'Sakit',
                            ])
                            ->required(),
                        DateTimePicker::make('scan_time')
                            ->label('Tanggal & Waktu')
                            ->default(now())
                            ->required(),
                        Textarea::make('reason')
                            ->label('Alasan / Keterangan')
                            ->required(),
                        FileUpload::make('document_path')
                            ->label('Upload Surat (PDF/Foto)')
                            ->directory('attendance-permits')
                            ->visibility('public')
                            ->required(false)
                            ->helperText('Maksimal 2MB. Format: PDF, JPG, PNG.')
                            ->maxSize(2048)
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png']),
                    ])
                    ->action(function (array $data) {
                        $student = Auth::user()->mahasiswa;

                        // Cari atau buat sesi dummy jika perlu, atau biarkan null jika diizinkan
                        // Disini kita asumsikan mahasiswa menginput tanpa sesi spesifik jika manual
                        AttendanceLog::create([
                            'student_id' => $student->id,
                            'status' => $data['status'],
                            'scan_time' => $data['scan_time'],
                            'reason' => $data['reason'],
                            'document_path' => $data['document_path'],
                        ]);
                    })
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'present' => 'Hadir',
                        'permit' => 'Izin',
                        'sick' => 'Sakit',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('view_file')
                    ->label('Buka Surat')
                    ->icon('heroicon-m-document-magnifying-glass')
                    ->color('info')
                    ->url(fn ($record) => $record?->document_path ? asset('storage/' . $record->document_path) : null)
                    ->openUrlInNewTab()
                    ->hidden(fn ($record) => !($record?->document_path)),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfoSection::make('Detail Absensi')
                    ->schema([
                        TextEntry::make('student.user.name')
                            ->label('Nama Peserta'),
                        TextEntry::make('session.session_name')
                            ->label('Sesi Pertemuan')
                            ->placeholder('Input Manual'),
                        TextEntry::make('scan_time')
                            ->label('Waktu Presensi')
                            ->dateTime(),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'present' => 'success',
                                'permit' => 'warning',
                                'sick' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'present' => 'HADIR',
                                'permit' => 'IZIN',
                                'sick' => 'SAKIT',
                                default => strtoupper($state),
                            }),
                        TextEntry::make('reason')
                            ->label('Alasan / Keterangan')
                            ->placeholder('Tidak ada keterangan')
                            ->columnSpanFull(),
                    ])->columns(2),

                InfoSection::make('Dokumen Pendukung')
                    ->schema([
                        TextEntry::make('document_path')
                            ->label('Nama File')
                            ->placeholder('Tidak ada file diunggah'),
                        ImageEntry::make('document_path')
                            ->label('Pratinjau Gambar')
                            ->visibility('public')
                            ->width(400)
                            ->height(400)
                            ->hidden(fn ($record) => !$record->document_path || !in_array(pathinfo($record->document_path, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png'])),
                        TextEntry::make('view_document')
                            ->label('Dokumen PDF')
                            ->default('Buka PDF')
                            ->url(fn ($record) => asset('storage/' . $record->document_path), true)
                            ->hidden(fn ($record) => !$record->document_path || pathinfo($record->document_path, PATHINFO_EXTENSION) !== 'pdf'),
                    ])
                    ->hidden(fn ($record) => $record->status === 'present'),

                InfoSection::make('Informasi Teknis')
                    ->schema([
                        TextEntry::make('ip_address')
                            ->label('Alamat IP'),
                        TextEntry::make('browser')
                            ->label('Browser/Perangkat'),
                    ])->columns(2)
                    ->collapsed(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendanceLogs::route('/'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            AttendanceLogResource\Widgets\AttendanceStatsOverview::class,
        ];
    }
}
