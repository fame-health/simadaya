<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengajuanMagangResource\Pages;
use App\Models\PengajuanMagang;
use App\Models\TemplateSurat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction; // Ditambahkan
use Filament\Support\Enums\FontWeight;
use Filament\Support\Colors\Color;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Mail;


class PengajuanMagangResource extends Resource
{
    protected static ?string $model = PengajuanMagang::class;

    protected static ?string $navigationGroup = 'ALUR PELAKSANAAN PKL';

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Pengajuan Magang';

    protected static ?string $pluralModelLabel = 'Silakan klik Pengajuan Magang'; // Diperbaiki sedikit: Manggang -> Magang

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user && in_array($user->role, ['admin', 'mahasiswa', 'pembimbing']);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();
        // Memastikan relasi `mahasiswa.user` dan `pembimbing` dimuat
        $query = parent::getEloquentQuery()->with(['mahasiswa.user', 'pembimbing']);

        if ($user->role === 'admin') {
            return $query;
        }

        if ($user->role === 'mahasiswa' && $user->mahasiswa) {
            return $query->where('mahasiswa_id', $user->mahasiswa->id);
        }

        if ($user->role === 'pembimbing' && $user->pembimbing) {
            return $query->where('pembimbing_id', $user->pembimbing->id);
        }

        return $query->whereNull('id');
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();

        if (!$user || !in_array($user->role, ['admin', 'mahasiswa'])) {
            return false;
        }

        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'mahasiswa') {
            if (!$user->mahasiswa) {
                return false;
            }

            $lastPengajuan = PengajuanMagang::where('mahasiswa_id', $user->mahasiswa->id)
                ->orderBy('created_at', 'desc')
                ->first();

            // Jika belum ada pengajuan sama sekali
            if (!$lastPengajuan) {
                return true;
            }

            // Jika statusnya PENDING atau DITERIMA, tidak bisa membuat pengajuan baru
            if (in_array($lastPengajuan->status, [PengajuanMagang::STATUS_PENDING, PengajuanMagang::STATUS_DITERIMA])) {
                return false;
            }

            // Jika statusnya DITOLAK atau SELESAI, bisa membuat pengajuan baru
            if (in_array($lastPengajuan->status, [PengajuanMagang::STATUS_DITOLAK, PengajuanMagang::STATUS_SELESAI])) {
                return true;
            }
        }

        return false;
    }

    public static function form(Form $form): Form
    {
        $user = Auth::user();
        $isAdmin = $user->role === 'admin';
        $isMahasiswa = $user->role === 'mahasiswa';
        $isPembimbing = $user->role === 'pembimbing';

        return $form
            ->schema([
                Forms\Components\Hidden::make('mahasiswa_id')
                    ->default(fn () => $isMahasiswa && $user->mahasiswa ? $user->mahasiswa->id : null)
                    ->visible(fn ($livewire) => $livewire instanceof Pages\CreatePengajuanMagang && ($isMahasiswa || $user->role === 'siswa')),

                Forms\Components\Section::make('Informasi Mahasiswa')
                    ->schema([
                        Forms\Components\Select::make('mahasiswa_id')
                            ->relationship('mahasiswa', 'nim')
                            ->getOptionLabelFromRecordUsing(fn ($record) => ($record->user->name ?? 'Tanpa Nama') . ' - ' . ($record->nim ?? 'NIM tidak tersedia'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->default(function () use ($user, $isMahasiswa) {
                                return $isMahasiswa && $user->mahasiswa ? $user->mahasiswa->id : null;
                            })
                            ->disabled($isMahasiswa)
                            ->dehydrated(true)
                            ->visible($isAdmin || $isMahasiswa || $isPembimbing),
                        Forms\Components\Select::make('pembimbing_id')
                            ->relationship('pembimbing', 'user_id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->user->name ?? 'Tanpa Nama')
                            ->searchable()
                            ->preload()
                            ->disabled($isMahasiswa)
                            ->visible($isAdmin || $isPembimbing), // Pembimbing hanya diisi oleh Admin atau dilihat oleh Pembimbing
                    ])
                    ->columns(2)
                    ->visible(fn ($livewire) => !($livewire instanceof Pages\CreatePengajuanMagang && ($isMahasiswa || $user->role === 'siswa'))),

                Forms\Components\Section::make('Detail Pengajuan')
                    ->schema([
                        Forms\Components\FileUpload::make('surat_permohonan')
                            ->required()
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(2048)
                            ->helperText('Hanya file PDF (Maks. 2MB)')
                            ->directory('pengajuan-magang/surat-permohonan')
                            ->disabled(!$isAdmin && !$isMahasiswa),
                        Forms\Components\FileUpload::make('ktm')
                            ->required()
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->maxSize(2048)
                            ->helperText('File PDF, JPG, atau PNG (Maks. 2MB)')
                            ->directory('pengajuan-magang/ktm')
                            ->disabled(!$isAdmin && !$isMahasiswa),
                        Forms\Components\DatePicker::make('tanggal_mulai')
                            ->required()
                            ->disabled(!$isAdmin && !$isMahasiswa)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                $startDate = Carbon::parse($state);
                                $endDate = Carbon::parse($get('tanggal_selesai'));
                                if ($startDate && $endDate && $endDate->gte($startDate)) {
                                    $weeks = ceil($startDate->diffInDays($endDate) / 7);
                                    $set('durasi_magang', $weeks);
                                } else {
                                    $set('durasi_magang', null);
                                }
                            }),
                        Forms\Components\DatePicker::make('tanggal_selesai')
                            ->required()
                            ->disabled(!$isAdmin && !$isMahasiswa)
                            ->reactive()
                            ->rules(['after_or_equal:tanggal_mulai'])
                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                $endDate = Carbon::parse($state);
                                $startDate = Carbon::parse($get('tanggal_mulai'));
                                if ($startDate && $endDate && $endDate->gte($startDate)) {
                                    $weeks = ceil($startDate->diffInDays($endDate) / 7);
                                    $set('durasi_magang', $weeks);
                                } else {
                                    $set('durasi_magang', null);
                                }
                            }),
                        Forms\Components\TextInput::make('durasi_magang')
                            ->numeric()
                            ->suffix('minggu')
                            ->required()
                            ->disabled()
                            ->dehydrated(true)
                            ->label('Durasi Magang (Minggu)'), // Ditambahkan label yang lebih deskriptif
                        Forms\Components\TextInput::make('bidang_diminati')
                            ->required()
                            ->disabled(!$isAdmin && !$isMahasiswa),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Status dan Verifikasi')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                PengajuanMagang::STATUS_PENDING => 'Pending',
                                PengajuanMagang::STATUS_DITERIMA => 'Diterima',
                                PengajuanMagang::STATUS_DITOLAK => 'Ditolak',
                                PengajuanMagang::STATUS_SELESAI => 'Selesai',
                            ])
                            ->required()
                            ->default(PengajuanMagang::STATUS_PENDING)
                            ->disabled($isMahasiswa)
                            ->visible($isAdmin || $isPembimbing),
                        Forms\Components\Textarea::make('alasan_penolakan')
                            ->visible(fn ($get) => $get('status') === PengajuanMagang::STATUS_DITOLAK && ($isAdmin || $isPembimbing))
                            ->columnSpanFull(),
                        Forms\Components\DateTimePicker::make('tanggal_verifikasi')
                            ->visible($isAdmin || $isPembimbing),
                        Forms\Components\Select::make('verified_by')
                            ->relationship('verifikator', 'name')
                            ->searchable()
                            ->preload()
                            ->visible($isAdmin || $isPembimbing),
                    ])
                    ->columns(2)
                    ->visible($isAdmin || $isPembimbing),

                Forms\Components\Section::make('Dokumen Tambahan')
                    ->schema([
                        Forms\Components\FileUpload::make('surat_balasan')
                            ->acceptedFileTypes(['application/pdf'])
                            ->directory('pengajuan-magang/surat-balasan')
                            ->disabled(true) // Disabled since it will be auto-generated
                            ->visible($isAdmin || $isPembimbing),
                        Forms\Components\FileUpload::make('final_laporan')
                            ->acceptedFileTypes(['application/pdf'])
                            ->directory('pengajuan-magang/laporan')
                            ->disabled(true) // Asumsi hanya Admin yang bisa mengunggah Laporan Final/Sertifikat
                            ->visible($isAdmin || $isPembimbing),
                        Forms\Components\FileUpload::make('sertifikat')
                            ->acceptedFileTypes(['application/pdf'])
                            ->directory('pengajuan-magang/sertifikat')
                            ->disabled(true) // Asumsi hanya Admin yang bisa mengunggah Laporan Final/Sertifikat
                            ->visible($isAdmin || $isPembimbing),
                    ])
                    ->columns(2)
                    ->visible($isAdmin || $isPembimbing),
            ]);
    }

    public static function table(Table $table): Table
    {
        $user = Auth::user();
        $isAdmin = $user->role === 'admin';
        $isPembimbing = $user->role === 'pembimbing';

        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('mahasiswa.nim')
                    ->label('NIM')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('mahasiswa.user.name')
                    ->label('Nama Mahasiswa')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pembimbing.user.name')
                    ->label('Nama Pembimbing')
                    ->searchable()
                    ->sortable()
                    ->default('-') // Tambahkan default jika null
                    ->visible($isAdmin || $isPembimbing),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        PengajuanMagang::STATUS_PENDING => 'warning',
                        PengajuanMagang::STATUS_DITERIMA => 'success',
                        PengajuanMagang::STATUS_DITOLAK => 'danger',
                        PengajuanMagang::STATUS_SELESAI => 'info',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('alasan_penolakan')
                    ->label('Alasan Penolakan')
                    ->searchable()
                    ->sortable()
                    ->visible($isAdmin || $isPembimbing)
                    ->wrap(),
                // Tampilan tanggal dibuat untuk membantu Admin/User melacak
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Diajukan')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('tanggal_verifikasi')
                    ->label('Diverifikasi')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible($isAdmin || $isPembimbing),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        PengajuanMagang::STATUS_PENDING => 'Pending',
                        PengajuanMagang::STATUS_DITERIMA => 'Diterima',
                        PengajuanMagang::STATUS_DITOLAK => 'Ditolak',
                        PengajuanMagang::STATUS_SELESAI => 'Selesai',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(), // *** PERBAIKAN PENTING: Menambahkan EditAction ***
            ]); // Penutup array dan ->actions yang benar
    } // Penutup public static function table(Table $table): Table yang benar

    public static function canEdit($record): bool
    {
        $user = Auth::user();

        if ($user->role === 'admin' || $user->role === 'pembimbing') {
            return true;
        }

        if ($user->role === 'mahasiswa' && $user->mahasiswa) {
            // Hanya Mahasiswa terkait dengan status PENDING yang bisa mengedit
            return $record->mahasiswa_id === $user->mahasiswa->id && $record->status === PengajuanMagang::STATUS_PENDING;
        }

        return false;
    }

    public static function canDelete($record): bool
    {
        $user = Auth::user();

        if ($user->role === 'admin' || $user->role === 'pembimbing') {
            return true;
        }

        if ($user->role === 'mahasiswa' && $user->mahasiswa) {
            // Hanya Mahasiswa terkait dengan status PENDING yang bisa menghapus
            return $record->mahasiswa_id === $user->mahasiswa->id && $record->status === PengajuanMagang::STATUS_PENDING;
        }

        return false;
    }

    public static function canView($record): bool
    {
        $user = Auth::user();

        if ($user->role === 'admin' || $user->role === 'pembimbing') {
            return true;
        }

        if ($user->role === 'mahasiswa' && $user->mahasiswa) {
            return $record->mahasiswa_id === $user->mahasiswa->id;
        }

        return false;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPengajuanMagangs::route('/'),
            'create' => Pages\CreatePengajuanMagang::route('/create'),
            'edit' => Pages\EditPengajuanMagang::route('/{record}/edit'),
            'view' => Pages\ViewPengajuanMagang::route('/{record}/view'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user) return null;

        if ($user->isMahasiswa()) {
            return (string) static::getModel()::where('mahasiswa_id', $user->mahasiswa?->id)->count();
        }

        if ($user->isPembimbing()) {
            return (string) static::getModel()::where('pembimbing_id', $user->pembimbing?->id)->count();
        }

        return (string) static::getModel()::count();
    }
}
