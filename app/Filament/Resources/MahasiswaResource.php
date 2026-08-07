<?php

namespace App\Filament\Resources;

use App\Models\Mahasiswa;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class MahasiswaResource extends Resource
{
    protected static ?string $model = Mahasiswa::class;
    protected static ?string $navigationGroup = 'ALUR PELAKSANAAN PKL';

    public static function getNavigationSort(): ?int
    {
        return 1; // Ganti X dengan angka sesuai urutan yang kamu inginkan
    }

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    public static function getNavigationLabel(): string
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user?->isAdmin()) {
            return 'Biodata Mahasiswa';
        }

        if ($user?->isMahasiswa()) {
            return 'Biodata Mahasiswa';
        }

        if ($user?->isPembimbing()) {
            return 'Biodata Pembimbing';
        }

        return 'Isi Biodata';
    }

    protected static ?string $modelLabel = 'Mahasiswa';
    protected static ?string $pluralModelLabel = 'Mahasiswa';
    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::check() && Auth::user()->role !== 'pembimbing';
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (Auth::check() && Auth::user()->role !== 'admin') {
            $query->where('user_id', Auth::id());
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Akun')
                    ->description('Gunakan bagian ini untuk mengubah data login Anda (Email & Password)')
                    ->relationship('user')
                    ->visible(fn (string $context) => $context !== 'create' || Auth::user()?->isAdmin())
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255)
                            ->autocomplete('none')
                            ->extraAttributes(['autocomplete' => 'none', 'name' => 'mahasiswa_full_name']),
                        Forms\Components\TextInput::make('email')
                            ->label('Email Akun (Gmail)')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(table: 'users', column: 'email', ignoreRecord: true)
                            ->autocomplete('none')
                            ->extraAttributes(['autocomplete' => 'none', 'name' => 'mahasiswa_account_email']),
                        Forms\Components\TextInput::make('password')
                            ->label('Ganti Password')
                            ->password()
                            ->placeholder('Kosongkan jika tidak ingin ganti password')
                            ->dehydrateStateUsing(fn ($state) => \Illuminate\Support\Facades\Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Data Akademik')
                    ->schema([
                        Forms\Components\TextInput::make('nim')
                            ->label('NIM / NIP Siswa')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(20)
                            ->placeholder('Masukkan NIM mahasiswa')
                            ->rule('regex:/^[0-9]+$/'),

                        Forms\Components\TextInput::make('universitas')
                            ->label('Universitas / Sekolah')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Nama universitas'),

                        Forms\Components\TextInput::make('fakultas')
                            ->label('Fakultas /')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Nama fakultas / Siswa di kasih tanda -'),

                        Forms\Components\TextInput::make('jurusan')
                            ->label('Jurusan/Program Studi')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Nama jurusan atau program studi'),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('semester')
                                    ->label('Semester / Kelas')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->maxValue(14)
                                    ->placeholder('1-14'),

                                Forms\Components\TextInput::make('ipk')
                                    ->label('IPK / Nilai')
                                    ->numeric()
                                    ->required()
                                    ->step(0.01)
                                    ->minValue(0.00)
                                    ->maxValue(4.00)
                                    ->placeholder('0.00 - 4.00'),
                            ]),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Data Pribadi')
                    ->schema([
                        // --- INPUT FOTO PROFIL (BARU) ---
                        Forms\Components\FileUpload::make('profile_photo_path')
                            ->label('Foto Profil Mahasiswa')
                            ->disk('public') // Pastikan ini sesuai dengan disk Anda
                            ->directory('mahasiswa/profile-photos') // Folder penyimpanan
                            ->image()
                            ->imageEditor() // Memungkinkan crop/edit
                            ->maxSize(1024) // Maks 1MB
                            ->columnSpanFull()
                            ->placeholder('Unggah foto profil'),
                        // ----------------------------------

                        Forms\Components\Textarea::make('alamat')
                            ->label('Domisili')
                            ->required()
                            ->maxLength(500)
                            ->rows(3)
                            ->placeholder('Alamat lengkap mahasiswa'),

                        // --- INPUT NOMOR HP (BARU) ---
                        Forms\Components\TextInput::make('nomor_hp')
                            ->label('Nomor Handphone')
                            ->required()
                            ->tel()
                            ->maxLength(15)
                            ->rule('regex:/^[0-9]+$/')
                            ->placeholder('Contoh: 081234567890'),
                        // -----------------------------

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('tanggal_lahir')
                                    ->label('Tanggal Lahir')
                                    ->required()
                                    ->native(false)
                                    ->maxDate(now()->subYears(16))
                                    ->displayFormat('d/m/Y'),

                                Forms\Components\Select::make('jenis_kelamin')
                                    ->label('Jenis Kelamin')
                                    ->options([
                                        'L' => 'Laki-laki',
                                        'P' => 'Perempuan',
                                    ])
                                    ->required()
                                    ->native(false),
                            ]),

                        // Hidden field untuk user_id
                        Forms\Components\Hidden::make('user_id')
                            ->default(Auth::id()),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // --- KOLOM FOTO PROFIL (BARU) ---
                Tables\Columns\ImageColumn::make('profile_photo_path')
                    ->label('Foto')
                    ->disk('public')
                    ->toggleable(isToggledHiddenByDefault: false) // Tampilkan secara default
                    ->circular()
                    ->size(40),
                // ----------------------------------

                Tables\Columns\TextColumn::make('nim')
                    ->label('NIM')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold')
                    ->size('sm'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Mahasiswa')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->size('sm'),

                // --- KOLOM NOMOR HP (BARU) ---
                Tables\Columns\TextColumn::make('nomor_hp')
                    ->label('No. HP')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: false) // Tampilkan secara default
                    ->size('sm'),
                // -----------------------------

                Tables\Columns\TextColumn::make('universitas')
                    ->label('Universitas')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(function ($record) {
                        return $record->universitas;
                    })
                    ->size('sm'),

                Tables\Columns\TextColumn::make('jurusan')
                    ->label('Program Studi')
                    ->searchable()
                    ->limit(25)
                    ->tooltip(function ($record) {
                        return $record->jurusan;
                    })
                    ->size('sm'),

                Tables\Columns\TextColumn::make('semester')
                    ->label('Sem')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->size('sm')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('ipk')
                    ->label('IPK')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->size('sm')
                    ->color(fn($state) => match (true) {
                        $state >= 3.50 => 'success',
                        $state >= 3.00 => 'warning',
                        default => 'danger'
                    }),

                Tables\Columns\TextColumn::make('fakultas')
                    ->label('Fakultas')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->limit(20)
                    ->size('sm'),

                Tables\Columns\TextColumn::make('alamat')
                    ->label('Alamat')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->limit(30)
                    ->size('sm'),

                Tables\Columns\TextColumn::make('tanggal_lahir')
                    ->label('Tgl Lahir')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->size('sm'),

                Tables\Columns\TextColumn::make('jenis_kelamin')
                    ->label('L/P')
                    ->formatStateUsing(fn($state) => $state === 'L' ? 'L' : 'P')
                    ->badge()
                    ->color(fn($state) => $state === 'L' ? 'blue' : 'pink')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->size('sm'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->size('sm'),
            ])
            ->filters([
                SelectFilter::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->options([
                        'L' => 'Laki-laki',
                        'P' => 'Perempuan',
                    ]),

                SelectFilter::make('semester')
                    ->label('Semester')
                    ->options(collect(range(1, 14))->mapWithKeys(fn($i) => [$i => "Semester $i"])),

                Filter::make('ipk_tinggi')
                    ->label('IPK ≥ 3.50')
                    ->query(fn(Builder $query): Builder => $query->where('ipk', '>=', 3.50))
                    ->toggle(),

                Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->color('info'),
                Tables\Actions\EditAction::make()
                    ->visible(fn($record) => Auth::check() && (
                        Auth::user()->role === 'admin' ||
                        (Auth::user()->role === 'mahasiswa' && $record->user_id === Auth::id())
                    )),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn() => Auth::check() && Auth::user()->role === 'admin'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => Auth::check() && Auth::user()->role === 'admin'),
                ]),
            ])

            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50])
            ->extremePaginationLinks()
            ->defaultPaginationPageOption(25);
    }

    public static function getRelations(): array
    {
        return [
            // Add relations here if needed
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\MahasiswaResource\Pages\ListMahasiswas::route('/'),
            'create' => \App\Filament\Resources\MahasiswaResource\Pages\CreateMahasiswa::route('/create'),
            'view' => \App\Filament\Resources\MahasiswaResource\Pages\ViewMahasiswa::route('/{record}'),
            'edit' => \App\Filament\Resources\MahasiswaResource\Pages\EditMahasiswa::route('/{record}/edit'),
        ];

    }

    public static function canViewAny(): bool
    {
        return Auth::check() && Auth::user()->role !== 'pembimbing';
    }

    public static function canView($record): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();

        return match ($user->role) {
            'admin' => true,
            'mahasiswa' => $record->user_id === $user->id,
            default => false,
        };
    }

    public static function canCreate(): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();

        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'mahasiswa') {
            // Mahasiswa hanya bisa create jika belum punya data
            return !Mahasiswa::where('user_id', $user->id)->exists();
        }

        return false;
    }

    public static function canEdit($record): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();

        return match ($user->role) {
            'admin' => true,
            'mahasiswa' => $record->user_id === $user->id,
            default => false,
        };
    }

    public static function canDelete($record): bool
    {
        return Auth::check() && Auth::user()->role === 'admin';
    }

    public static function canDeleteAny(): bool
    {
        return Auth::check() && Auth::user()->role === 'admin';
    }

    public static function getNavigationBadge(): ?string
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->role === 'admin') {
                return static::getModel()::count();
            }

            if ($user->role === 'mahasiswa') {
                $hasData = Mahasiswa::where('user_id', $user->id)->exists();
                return $hasData ? '✓' : '!';
            }
        }

        return null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->role === 'admin') {
                return 'primary';
            }

            if ($user->role === 'mahasiswa') {
                $hasData = Mahasiswa::where('user_id', $user->id)->exists();
                return $hasData ? 'success' : 'warning';
            }
        }

        return 'primary';
    }
}
