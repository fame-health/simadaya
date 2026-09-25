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

    protected static ?string $label = 'Riwayat Absensi';

    protected static ?string $pluralModelLabel = 'Riwayat Absensi';

    protected static ?string $navigationLabel = 'Riwayat Absensi';

    protected static ?string $navigationGroup = 'ABSENSI';

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
                // Generasi otomatis log alpa untuk sesi yang telah berakhir
                app(\App\Services\Attendance\AttendanceService::class)->autoGenerateAlphaLogs();

                $user = Auth::user();
                if ($user->isMahasiswa()) {
                    $query->whereHas('student', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
                } elseif ($user->isPembimbing() && $user->pembimbing) {
                    $pembimbingId = $user->pembimbing->id;
                    $query->where(function (Builder $q) use ($pembimbingId) {
                        $q->whereHas('student.pengajuan', function ($pq) use ($pembimbingId) {
                            $pq->where('pembimbing_id', $pembimbingId)
                               ->whereIn('status', [
                                   \App\Models\PengajuanMagang::STATUS_DITERIMA,
                                   \App\Models\PengajuanMagang::STATUS_SELESAI,
                               ]);
                        })->orWhereHas('session', function ($sq) use ($pembimbingId) {
                            $sq->where('mentor_id', $pembimbingId);
                        });
                    });
                } elseif ($user->isAdmin() && session()->has('selected_mentor_id')) {
                    $mentorId = session('selected_mentor_id');
                    $query->where(function (Builder $q) use ($mentorId) {
                        $q->whereHas('student.pengajuan', function ($pq) use ($mentorId) {
                            $pq->where('pembimbing_id', $mentorId);
                        })->orWhereHas('session', function ($sq) use ($mentorId) {
                            $sq->where('mentor_id', $mentorId);
                        });
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
                    ->sortable()
                    ->placeholder('Izin / Sakit / Alpa'),
                Tables\Columns\TextColumn::make('scan_time')
                    ->label('Waktu')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status Absensi')
                    ->colors([
                        'success' => 'present',
                        'warning' => 'permit',
                        'danger' => ['sick', 'alpha', 'alpa'],
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => 'present',
                        'heroicon-o-document-text' => 'permit',
                        'heroicon-o-exclamation-circle' => 'sick',
                        'heroicon-o-x-circle' => ['alpha', 'alpa'],
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'present' => 'HADIR',
                        'permit' => 'IZIN',
                        'sick' => 'SAKIT',
                        'alpha', 'alpa' => 'ALPA',
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

                        AttendanceLog::create([
                            'student_id' => $student->id,
                            'status' => $data['status'],
                            'scan_time' => $data['scan_time'],
                            'reason' => $data['reason'],
                            'document_path' => $data['document_path'],
                        ]);
                    }),
                Action::make('downloadPdf')
                    ->label('Unduh PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->form(function () {
                        $user = Auth::user();
                        if ($user->isMahasiswa()) {
                            return [
                                \Filament\Forms\Components\DatePicker::make('start_date')->label('Dari Tanggal'),
                                \Filament\Forms\Components\DatePicker::make('end_date')->label('Sampai Tanggal'),
                            ];
                        }

                        $studentOptions = [];
                        if ($user->isPembimbing() && $user->pembimbing) {
                            $studentOptions = \App\Models\Mahasiswa::whereHas('pengajuan', function ($q) use ($user) {
                                $q->where('pembimbing_id', $user->pembimbing->id)
                                  ->whereIn('status', [\App\Models\PengajuanMagang::STATUS_DITERIMA, \App\Models\PengajuanMagang::STATUS_SELESAI]);
                            })->with('user')->get()->pluck('user.name', 'id')->toArray();
                        } else {
                            $studentOptions = \App\Models\Mahasiswa::with('user')->get()->pluck('user.name', 'id')->toArray();
                        }

                        return [
                            Select::make('student_id')
                                ->label('Pilih Peserta Magang')
                                ->options($studentOptions)
                                ->searchable()
                                ->required(!$user->isMahasiswa()),
                            \Filament\Forms\Components\DatePicker::make('start_date')->label('Dari Tanggal'),
                            \Filament\Forms\Components\DatePicker::make('end_date')->label('Sampai Tanggal'),
                        ];
                    })
                    ->action(function (array $data) {
                        $params = array_filter([
                            'student_id' => $data['student_id'] ?? null,
                            'start_date' => $data['start_date'] ?? null,
                            'end_date' => $data['end_date'] ?? null,
                        ]);

                        return redirect()->route('riwayat-absensi.pdf', $params);
                    })
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'present' => 'Hadir',
                        'permit' => 'Izin',
                        'sick' => 'Sakit',
                        'alpha' => 'Alpa',
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
                            ->label('Waktu Absensi')
                            ->dateTime(),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'present' => 'success',
                                'permit' => 'warning',
                                'sick', 'alpha', 'alpa' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'present' => 'HADIR',
                                'permit' => 'IZIN',
                                'sick' => 'SAKIT',
                                'alpha', 'alpa' => 'ALPA',
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
