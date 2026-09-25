<?php

namespace App\Filament\Pages;

use App\Models\AttendanceLog;
use App\Models\Mahasiswa;
use App\Models\PengajuanMagang;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class RiwayatAbsensi extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationLabel = 'Riwayat Absensi';

    protected static string $view = 'filament.pages.riwayat-absensi';

    protected static ?string $title = 'Riwayat Absensi';

    protected static ?string $navigationGroup = 'ABSENSI';

    protected static ?int $navigationSort = 3;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(AttendanceLog::query()->with(['student.user', 'session']))
            ->modifyQueryUsing(function (Builder $query) {
                app(\App\Services\Attendance\AttendanceService::class)->autoGenerateAlphaLogs();

                $user = Auth::user();

                if ($user->isMahasiswa()) {
                    $query->whereHas('student', fn ($q) => $q->where('user_id', $user->id));
                } elseif ($user->isPembimbing() && $user->pembimbing) {
                    $pembimbingId = $user->pembimbing->id;
                    $query->where(function (Builder $q) use ($pembimbingId) {
                        $q->whereHas('student.pengajuan', function ($pq) use ($pembimbingId) {
                            $pq->where('pembimbing_id', $pembimbingId)
                               ->whereIn('status', [
                                   PengajuanMagang::STATUS_DITERIMA,
                                   PengajuanMagang::STATUS_SELESAI,
                               ]);
                        })->orWhereHas('session', fn ($sq) => $sq->where('mentor_id', $pembimbingId));
                    });
                }
            })
            ->columns([
                Tables\Columns\TextColumn::make('student.user.name')
                    ->label('Nama Peserta')
                    ->searchable()
                    ->sortable()
                    ->hidden(fn () => Auth::user()->isMahasiswa()),
                Tables\Columns\TextColumn::make('session.session_name')
                    ->label('Sesi / Jenis')
                    ->sortable()
                    ->placeholder('Izin / Sakit / Alpa'),
                Tables\Columns\TextColumn::make('scan_time')
                    ->label('Waktu Absensi')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status Absensi')
                    ->colors([
                        'success' => AttendanceLog::STATUS_PRESENT,
                        'warning' => AttendanceLog::STATUS_PERMIT,
                        'danger' => [AttendanceLog::STATUS_SICK, AttendanceLog::STATUS_ALPHA, 'alpa'],
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => AttendanceLog::STATUS_PRESENT,
                        'heroicon-o-document-text' => AttendanceLog::STATUS_PERMIT,
                        'heroicon-o-exclamation-circle' => AttendanceLog::STATUS_SICK,
                        'heroicon-o-x-circle' => [AttendanceLog::STATUS_ALPHA, 'alpa'],
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'present' => 'HADIR',
                        'permit' => 'IZIN',
                        'sick' => 'SAKIT',
                        'alpha', 'alpa' => 'ALPA',
                        default => strtoupper($state),
                    }),
                Tables\Columns\TextColumn::make('reason')
                    ->label('Keterangan / Alasan')
                    ->placeholder('Tidak ada keterangan')
                    ->wrap()
                    ->limit(40),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Filter Status')
                    ->options([
                        'present' => 'Hadir',
                        'permit' => 'Izin',
                        'sick' => 'Sakit',
                        'alpha' => 'Alpa',
                    ]),
                SelectFilter::make('student_id')
                    ->label('Filter Peserta')
                    ->relationship('student.user', 'name')
                    ->hidden(fn () => Auth::user()->isMahasiswa()),
                Filter::make('scan_time')
                    ->form([
                        DatePicker::make('from')->label('Dari Tanggal'),
                        DatePicker::make('until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn ($q, $date) => $q->whereDate('scan_time', '>=', $date))
                            ->when($data['until'], fn ($q, $date) => $q->whereDate('scan_time', '<=', $date));
                    }),
            ])
            ->headerActions([
                Action::make('downloadPdf')
                    ->label('Unduh PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->form(function () {
                        $user = Auth::user();
                        if ($user->isMahasiswa()) {
                            return [
                                DatePicker::make('start_date')->label('Dari Tanggal'),
                                DatePicker::make('end_date')->label('Sampai Tanggal'),
                            ];
                        }

                        $studentOptions = [];
                        if ($user->isPembimbing() && $user->pembimbing) {
                            $studentOptions = Mahasiswa::whereHas('pengajuan', function ($q) use ($user) {
                                $q->where('pembimbing_id', $user->pembimbing->id)
                                  ->whereIn('status', [PengajuanMagang::STATUS_DITERIMA, PengajuanMagang::STATUS_SELESAI]);
                            })->with('user')->get()->pluck('user.name', 'id')->toArray();
                        } else {
                            $studentOptions = Mahasiswa::with('user')->get()->pluck('user.name', 'id')->toArray();
                        }

                        return [
                            Select::make('student_id')
                                ->label('Pilih Peserta Magang')
                                ->options($studentOptions)
                                ->searchable()
                                ->required(!$user->isMahasiswa()),
                            DatePicker::make('start_date')->label('Dari Tanggal'),
                            DatePicker::make('end_date')->label('Sampai Tanggal'),
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
            ->defaultSort('scan_time', 'desc');
    }

    public function getSummaryStatsProperty(): array
    {
        $user = Auth::user();
        $query = AttendanceLog::query();

        if ($user->isMahasiswa()) {
            $query->whereHas('student', fn ($q) => $q->where('user_id', $user->id));
        } elseif ($user->isPembimbing() && $user->pembimbing) {
            $pembimbingId = $user->pembimbing->id;
            $query->where(function (Builder $q) use ($pembimbingId) {
                $q->whereHas('student.pengajuan', function ($pq) use ($pembimbingId) {
                    $pq->where('pembimbing_id', $pembimbingId)
                       ->whereIn('status', [
                           PengajuanMagang::STATUS_DITERIMA,
                           PengajuanMagang::STATUS_SELESAI,
                       ]);
                })->orWhereHas('session', fn ($sq) => $sq->where('mentor_id', $pembimbingId));
            });
        }

        $logs = $query->get();

        $hadir = $logs->where('status', AttendanceLog::STATUS_PRESENT)->count();
        $izin = $logs->where('status', AttendanceLog::STATUS_PERMIT)->count();
        $sakit = $logs->where('status', AttendanceLog::STATUS_SICK)->count();
        $alpa = $logs->whereIn('status', [AttendanceLog::STATUS_ALPHA, 'alpa'])->count();
        $total = $logs->count();

        return [
            'hadir' => $hadir,
            'izin' => $izin,
            'sakit' => $sakit,
            'alpa' => $alpa,
            'total' => $total,
            'persentase' => $total > 0 ? round(($hadir / $total) * 100, 1) : 0,
        ];
    }
}
