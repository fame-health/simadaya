<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PenilaianResource\Pages;
use App\Models\Penilaian;
use App\Models\Mahasiswa;
use App\Models\Pembimbing;
use App\Models\PengajuanMagang;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Illuminate\Database\Eloquent\Builder;

class PenilaianResource extends Resource
{
    protected static ?string $model = Penilaian::class;

    protected static ?string $navigationGroup = 'ALUR PELAKSANAAN PKL';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Penilaian';

    protected static ?string $modelLabel = 'Penilaian';

    protected static ?string $pluralModelLabel = 'Penilaian';

    public static function getNavigationSort(): ?int
    {
        return 5;
    }

    public static function shouldRegisterNavigation(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        // Admin dan Pembimbing selalu bisa melihat menu Penilaian
        if ($user->isAdmin() || $user->isPembimbing()) {
            return true;
        }

        // Mahasiswa hanya bisa melihat menu Penilaian jika pengajuannya sudah DITERIMA atau SELESAI
        if ($user->isMahasiswa()) {
            $mahasiswa = $user->mahasiswa;

            if (!$mahasiswa) {
                return false;
            }

            return \App\Models\PengajuanMagang::where('mahasiswa_id', $mahasiswa->id)
                ->whereIn('status', [
                    \App\Models\PengajuanMagang::STATUS_DITERIMA,
                    \App\Models\PengajuanMagang::STATUS_SELESAI
                ])
                ->exists();
        }

        return false;
    }

    public static function form(Form $form): Form
    {
        $user = Auth::user();
        $isMahasiswa = $user && $user->role === 'mahasiswa';
        $isPembimbing = $user && $user->role === 'pembimbing';

        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dasar')
                    ->schema([
                        Forms\Components\Placeholder::make('mahasiswa_name_display')
                            ->label('Mahasiswa yang Dinilai')
                            ->content(fn () => \App\Models\Mahasiswa::find(request()->query('mahasiswa_id'))?->user?->name ?? '-')
                            ->visible(fn () => $isPembimbing && request()->has('mahasiswa_id')),

                        Forms\Components\Select::make('mahasiswa_id')
                            ->label('Mahasiswa')
                            ->relationship('mahasiswa', 'nim')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->user->name} ({$record->nim})")
                            ->required()
                            ->searchable()
                            ->preload()
                            ->default(fn () => request()->query('mahasiswa_id'))
                            ->disabled($isMahasiswa || $form->getOperation() === 'edit')
                            ->hidden(fn () => $isPembimbing && request()->has('mahasiswa_id')),

                        Forms\Components\Hidden::make('mahasiswa_id')
                            ->default(fn () => request()->query('mahasiswa_id'))
                            ->visible(fn () => $isPembimbing && request()->has('mahasiswa_id')),

                        Forms\Components\Select::make('pembimbing_id')
                            ->label('Pembimbing')
                            ->relationship('pembimbing', 'nip')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->user->name} ({$record->nip})")
                            ->required()
                            ->searchable()
                            ->preload()
                            ->default(fn () => $isPembimbing ? \App\Models\Pembimbing::where('user_id', Auth::id())->first()?->id : null)
                            ->disabled($isMahasiswa)
                            ->hidden($isPembimbing),

                        Forms\Components\Hidden::make('pembimbing_id')
                            ->default(fn () => $isPembimbing ? \App\Models\Pembimbing::where('user_id', Auth::id())->first()?->id : null)
                            ->visible($isPembimbing),

                        Forms\Components\Hidden::make('bobot')
                            ->default(1.0),

                        Forms\Components\Hidden::make('aspek_penilaian')
                            ->default('Penilaian Magang Keseluruhan'),
                    ])->columns(1),

                Forms\Components\Section::make('Input Nilai')
                    ->description('Masukkan nilai angka dari 1 hingga 100.')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('nilai')
                                    ->label('Masukkan Nilai (1-100)')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(100)
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, callable $set, callable $get) => static::calculateFinalScore($set, $get))
                                    ->disabled($isMahasiswa)
                                    ->placeholder('Input Angka Saja')
                                    ->extraAttributes(['style' => 'font-size: 1.25rem; font-weight: bold;']),

                                Forms\Components\DateTimePicker::make('tanggal_penilaian')
                                    ->label('Waktu Penilaian')
                                    ->required()
                                    ->default(now())
                                    ->disabled($isMahasiswa),
                            ]),

                        Forms\Components\Textarea::make('keterangan')
                            ->label('Catatan Tambahan (Opsional)')
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->placeholder('Contoh: Mahasiswa sangat aktif dan disiplin...')
                            ->disabled($isMahasiswa),
                    ]),

                Forms\Components\Section::make('Hasil Akhir')
                    ->schema([
                        Forms\Components\TextInput::make('nilai_akhir')
                            ->label('Nilai Akhir')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->extraAttributes(['class' => 'font-bold text-lg']),
                        Forms\Components\TextInput::make('grade')
                            ->label('Grade')
                            ->disabled()
                            ->dehydrated()
                            ->extraAttributes(['class' => 'font-black text-xl']),
                    ])->columns(2),
            ]);
    }

    public static function calculateFinalScore(callable $set, callable $get)
    {
        $nilai = floatval($get('nilai'));
        $bobot = floatval($get('bobot'));
        $akhir = $nilai * $bobot;

        $set('nilai_akhir', $akhir);

        $grade = '-';
        if ($akhir >= 85) $grade = 'A';
        elseif ($akhir >= 75) $grade = 'B';
        elseif ($akhir >= 60) $grade = 'C';
        elseif ($akhir > 0) $grade = 'D';

        $set('grade', $grade);
    }

    public static function table(Table $table): Table
    {
        $user = Auth::user();
        $isMahasiswa = $user && $user->role === 'mahasiswa';
        $isPembimbing = $user && $user->role === 'pembimbing';
        $isAdmin = $user && !$isMahasiswa && !$isPembimbing;

        $pembimbing = $isPembimbing ? Pembimbing::where('user_id', $user->id)->first() : null;
        $mahasiswa = $isMahasiswa ? Mahasiswa::where('user_id', $user->id)->first() : null;

        // Hitung jumlah mahasiswa bimbingan untuk pembimbing
        $mahasiswaList = $isPembimbing && $pembimbing
            ? PengajuanMagang::where('pembimbing_id', $pembimbing->id)
                ->where('status', PengajuanMagang::STATUS_DITERIMA)
                ->whereNotExists(function ($query) use ($pembimbing) {
                    $query->select(\Illuminate\Support\Facades\DB::raw(1))
                        ->from('penilaian')
                        ->whereColumn('penilaian.mahasiswa_id', 'pengajuan_magang.mahasiswa_id')
                        ->where('penilaian.pembimbing_id', $pembimbing->id);
                })
                ->with(['mahasiswa.user'])
                ->distinct('mahasiswa_id')
                ->get()
            : collect([]);
        $mahasiswaCount = $mahasiswaList->count();

        return $table
            ->heading(function () use ($isPembimbing, $isMahasiswa, $mahasiswaCount, $mahasiswaList, $pembimbing, $mahasiswa) {
                if ($isPembimbing) {
                    if ($mahasiswaCount === 0) {
                        return new HtmlString('
                            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; width: 100%; display: flex; align-items: center; gap: 10px;">
                                <div style="background: #16a34a; color: white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                    <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <p style="color: #15803d; font-size: 14px; margin: 0; font-weight: 700;">SELESAI: Semua mahasiswa bimbingan Anda sudah diberikan nilai magang.</p>
                            </div>
                        ');
                    }

                    $html = '
                        <div style="background: linear-gradient(to right, #ffffff, #f8fafc); border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; margin-bottom: 24px; width: 100%; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
                                <div style="background: #2563eb; color: white; border-radius: 8px; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);">
                                    <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 style="color: #1e293b; font-size: 16px; font-weight: 800; margin: 0; text-transform: uppercase; letter-spacing: 0.05em;">Mahasiswa Belum Dinilai</h3>
                                    <p style="color: #64748b; font-size: 12px; margin: 0; font-weight: 500;">Terdapat ' . e($mahasiswaCount) . ' mahasiswa yang menunggu penilaian Anda.</p>
                                </div>
                            </div>';

                    $html .= '
                        <div style="overflow-x: auto;">
                            <table style="width: 100%; border-collapse: separate; border-spacing: 0; background: #ffffff;">
                                <thead>
                                    <tr>
                                        <th style="padding: 10px 16px; text-align: left; font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; border-bottom: 2px solid #f1f5f9;">No</th>
                                        <th style="padding: 10px 16px; text-align: left; font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; border-bottom: 2px solid #f1f5f9;">Nama Mahasiswa</th>
                                        <th style="padding: 10px 16px; text-align: left; font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; border-bottom: 2px solid #f1f5f9;">NIM</th>
                                        <th style="padding: 10px 16px; text-align: right; font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; border-bottom: 2px solid #f1f5f9;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>';
                    foreach ($mahasiswaList as $index => $pengajuan) {
                        $html .= '
                            <tr style="transition: background 0.2s;">
                                <td style="padding: 14px 16px; font-size: 13px; color: #64748b; border-bottom: 1px solid #f8fafc;">' . ($index + 1) . '</td>
                                <td style="padding: 14px 16px; font-size: 14px; color: #0f172a; font-weight: 700; border-bottom: 1px solid #f8fafc;">' . e($pengajuan->mahasiswa->user->name) . '</td>
                                <td style="padding: 14px 16px; font-size: 13px; color: #64748b; border-bottom: 1px solid #f8fafc;">' . e($pengajuan->mahasiswa->nim) . '</td>
                                <td style="padding: 14px 16px; text-align: right; border-bottom: 1px solid #f8fafc;">
                                    <a href="' . static::getUrl('create', ['mahasiswa_id' => $pengajuan->mahasiswa_id]) . '"
                                       style="background: #2563eb; color: white; padding: 8px 18px; font-size: 12px; font-weight: 800; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2); transition: all 0.2s;"
                                       onmouseover="this.style.background=\'#1e40af\'; this.style.transform=\'translateY(-1px)\'"
                                       onmouseout="this.style.background=\'#2563eb\'; this.style.transform=\'translateY(0)\'">
                                        <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        BERI NILAI
                                    </a>
                                </td>
                            </tr>';
                    }
                    $html .= '
                                </tbody>
                            </table>
                        </div>

                        <div style="margin-top: 24px; margin-bottom: 8px; padding: 10px 0; border-top: 1px dashed #cbd5e1; border-bottom: 1px dashed #cbd5e1;">
                            <h4 style="color: #64748b; font-size: 13px; font-weight: 800; margin: 0; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 8px;">
                                <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                                RIWAYAT PENILAIAN (DAFTAR MAHASISWA YANG SUDAH DINILAI)
                            </h4>
                        </div>
                    </div>';

                    return new HtmlString($html);
                }

                if ($isMahasiswa && $mahasiswa) {
                    $totalNilai = Penilaian::where('mahasiswa_id', $mahasiswa->id)->sum('nilai_akhir');
                    $avgNilai = Penilaian::where('mahasiswa_id', $mahasiswa->id)->avg('nilai');
                    $count = Penilaian::where('mahasiswa_id', $mahasiswa->id)->count();

                    $grade = '-';
                    $gradeColor = '#6b7280';
                    $message = 'Nilai Anda sedang diproses oleh pembimbing.';

                    if ($count > 0) {
                        if ($totalNilai >= 85) { $grade = 'A'; $gradeColor = '#16a34a'; $message = 'Luar biasa! Pertahankan prestasi Anda.'; }
                        elseif ($totalNilai >= 75) { $grade = 'B'; $gradeColor = '#2563eb'; $message = 'Sangat baik! Teruslah berkembang.'; }
                        elseif ($totalNilai >= 60) { $grade = 'C'; $gradeColor = '#d97706'; $message = 'Cukup baik. Tingkatkan lagi usaha Anda.'; }
                        else { $grade = 'D'; $gradeColor = '#dc2626'; $message = 'Jangan menyerah, tetap semangat belajar!'; }
                    }

                    return new HtmlString('
                        <div style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 2px solid #16a34a; border-radius: 16px; padding: 24px; margin-bottom: 24px; box-shadow: 0 4px 15px rgba(22, 163, 74, 0.1);">
                            <div style="display: flex; flex-wrap: wrap; gap: 24px; align-items: center;">
                                <div style="flex: 1; min-width: 250px;">
                                    <h3 style="color: #15803d; font-size: 24px; font-weight: 800; margin: 0 0 8px 0;">Halo, ' . e(Auth::user()->name) . '! 👋</h3>
                                    <p style="color: #166534; font-size: 16px; margin: 0; line-height: 1.6;">Berikut adalah ringkasan hasil penilaian magang Anda. Tetap semangat dan terus berkarya!</p>
                                    <div style="margin-top: 16px; display: flex; gap: 12px;">
                                        <div style="background: #ffffff; padding: 12px 20px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                            <span style="display: block; font-size: 12px; color: #6b7280; font-weight: 600; text-transform: uppercase;">Rata-rata Nilai</span>
                                            <span style="font-size: 20px; font-weight: 800; color: #111827;">' . number_format($avgNilai ?? 0, 2) . '</span>
                                        </div>
                                        <div style="background: #ffffff; padding: 12px 20px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                            <span style="display: block; font-size: 12px; color: #6b7280; font-weight: 600; text-transform: uppercase;">Total Aspek</span>
                                            <span style="font-size: 20px; font-weight: 800; color: #111827;">' . $count . '</span>
                                        </div>
                                    </div>
                                </div>
                                <div style="background: white; padding: 20px; border-radius: 20px; text-align: center; min-width: 150px; border: 4px solid ' . $gradeColor . ';">
                                    <span style="display: block; font-size: 14px; font-weight: 700; color: #6b7280; margin-bottom: 4px;">GRADE AKHIR</span>
                                    <span style="font-size: 64px; font-weight: 900; color: ' . $gradeColor . '; line-height: 1;">' . $grade . '</span>
                                </div>
                            </div>
                            <div style="margin-top: 20px; padding-top: 20px; border-top: 1px dashed #bbf7d0;">
                                <p style="font-style: italic; color: #15803d; font-weight: 500; margin: 0;">" ' . e($message) . ' "</p>
                            </div>
                        </div>
                    ');
                }

                return null;
            })
            ->query(function () use ($isMahasiswa, $isPembimbing, $mahasiswa, $pembimbing) {
                $query = Penilaian::query()->with(['mahasiswa.user', 'pembimbing.user']);
                if ($isMahasiswa && $mahasiswa) {
                    $query->where('mahasiswa_id', $mahasiswa->id);
                } elseif ($isPembimbing && $pembimbing) {
                    $query->where('pembimbing_id', $pembimbing->id);
                }
                return $query;
            })
            ->columns([
                Tables\Columns\TextColumn::make('mahasiswa.user.name')
                    ->label('Nama Mahasiswa')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn($record) => $record->mahasiswa->nim),
                Tables\Columns\TextColumn::make('pembimbing.user.name')
                    ->label('Penilai (Pembimbing)')
                    ->searchable()
                    ->sortable()
                    ->visible($isAdmin),
                Tables\Columns\TextColumn::make('nilai')
                    ->label('Nilai')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state): string => match (true) {
                        $state >= 85 => 'success',
                        $state >= 70 => 'info',
                        $state >= 60 => 'warning',
                        default => 'danger',
                    })
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('grade')
                    ->label('Grade')
                    ->badge()
                    ->size('lg')
                    ->color(fn ($state): string => match ($state) {
                        'A' => 'success',
                        'B' => 'info',
                        'C' => 'warning',
                        default => 'danger',
                    })
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('tanggal_penilaian')
                    ->label('Tanggal Penilaian')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Catatan')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->keterangan)
                    ->visible(!$isMahasiswa),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('mahasiswa')
                    ->relationship('mahasiswa', 'nim')
                    ->label('Mahasiswa')
                    ->searchable()
                    ->preload()
                    ->visible($isPembimbing),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->color('info')
                    ->visible($isMahasiswa),
                Tables\Actions\EditAction::make()
                    ->color('warning')
                    ->visible($isPembimbing),
                Tables\Actions\DeleteAction::make()
                    ->color('danger')
                    ->visible($isPembimbing || $isAdmin),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible($isPembimbing || $isAdmin),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(25);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPenilaians::route('/'),
            'create' => Pages\CreatePenilaian::route('/create'),
        ];
    }

    public static function canCreate(): bool
    {
        // Kontrol akses dipindahkan ke PenilaianPolicy.php
        return Auth::user()->role === 'admin' || Auth::user()->role === 'pembimbing';
    }

    public static function canEdit($record): bool
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'pembimbing') {
            return false;
        }
        $pembimbing = Pembimbing::where('user_id', $user->id)->first();
        return $pembimbing && $record->pembimbing_id === $pembimbing->id;
    }

    public static function canView($record): bool
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'mahasiswa') {
            return false;
        }
        $mahasiswa = Mahasiswa::where('user_id', $user->id)->first();
        return $mahasiswa && $record->mahasiswa_id === $mahasiswa->id;
    }

    public static function canDelete($record): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        $isAdmin = $user->role !== 'mahasiswa' && $user->role !== 'pembimbing';
        if ($isAdmin) {
            return true;
        }
        if ($user->role === 'pembimbing') {
            $pembimbing = Pembimbing::where('user_id', $user->id)->first();
            return $pembimbing && $record->pembimbing_id === $pembimbing->id;
        }
        return false;
    }

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        if ($user->role === 'mahasiswa') {
            return Mahasiswa::where('user_id', $user->id)->exists();
        }

        return $user->role === 'pembimbing' || ($user->role !== 'mahasiswa' && $user->role !== 'pembimbing');
    }

    public static function getNavigationBadge(): ?string
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        if ($user->role === 'pembimbing') {
            $pembimbing = Pembimbing::where('user_id', $user->id)->first();
            return $pembimbing ? (string) Penilaian::where('pembimbing_id', $pembimbing->id)->count() : '0';
        }

        if ($user->role === 'mahasiswa') {
            $mahasiswa = Mahasiswa::where('user_id', $user->id)->first();
            return $mahasiswa ? (string) Penilaian::where('mahasiswa_id', $mahasiswa->id)->count() : '0';
        }

        return (string) Penilaian::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $user = Auth::user();
        if (!$user) {
            return 'primary';
        }

        if ($user->role === 'mahasiswa') {
            $mahasiswa = Mahasiswa::where('user_id', $user->id)->first();
            return $mahasiswa && Penilaian::where('mahasiswa_id', $mahasiswa->id)->count() > 0 ? 'success' : 'warning';
        }

        if ($user->role === 'pembimbing') {
            $pembimbing = Pembimbing::where('user_id', $user->id)->first();
            return $pembimbing && Penilaian::where('pembimbing_id', $pembimbing->id)->whereNull('nilai')->count() > 0 ? 'warning' : 'success';
        }

        return 'primary';
    }
}
