<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WeeklyLogbookResource\Pages;
use App\Models\WeeklyLogbook;
use App\Models\Mahasiswa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Unique;

class WeeklyLogbookResource extends Resource
{
    protected static ?string $model = WeeklyLogbook::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'ALUR PELAKSANAAN PKL';

    public static function getNavigationSort(): ?int
    {
        return 3;
    }

    protected static ?string $label = 'Logbook Harian';

    protected static ?string $pluralLabel = 'Logbook Harian';

    public static function canCreate(): bool
    {
        return Auth::user()?->role === 'mahasiswa';
    }

    public static function shouldRegisterNavigation(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        // Admin dan Pembimbing selalu bisa melihat menu Logbook
        if ($user->isAdmin() || $user->isPembimbing()) {
            return true;
        }

        // Mahasiswa hanya bisa melihat menu Logbook jika pengajuannya sudah DITERIMA atau SELESAI
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
        $isMahasiswa = $user->role === 'mahasiswa';
        $isPembimbing = $user->role === 'pembimbing';
        $isView = $form->getOperation() === 'view';
        $mahasiswaId = $form->getRecord()?->mahasiswa_id ?? $user->mahasiswa?->id;

        return $form
            ->schema([
                Forms\Components\Grid::make(3) // Gunakan grid untuk membagi layar
                    ->schema([
                        // KOLOM KIRI (UTAMA) - 2/3 Lebar
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Section::make('Detail Kegiatan Harian')
                                    ->description('Catat kegiatan dan hasil pekerjaan Anda untuk satu hari.')
                                    ->schema([
                                        Forms\Components\RichEditor::make('activities')
                                            ->label('Kegiatan Hari Ini')
                                            ->placeholder('Tuliskan pekerjaan yang Anda lakukan hari ini.')
                                            ->required()
                                            ->disabled($isPembimbing || $isView)
                                            ->columnSpanFull(),

                                        Forms\Components\RichEditor::make('achievements')
                                            ->label('Hasil Pekerjaan')
                                            ->placeholder('Tuliskan hasil atau target yang berhasil dicapai hari ini.')
                                            ->disabled($isPembimbing || $isView)
                                            ->columnSpanFull(),

                                        Forms\Components\RichEditor::make('problems')
                                            ->label('Kendala dan Solusi')
                                            ->placeholder('Tuliskan kendala yang ditemui dan cara mengatasinya.')
                                            ->disabled($isPembimbing || $isView)
                                            ->columnSpanFull(),
                                    ])->compact(),

                                Forms\Components\Section::make('📝 Review & Feedback Pembimbing')
                                    ->description('Bagian ini diisi oleh pembimbing lapangan.')
                                    ->schema([
                                        Forms\Components\Textarea::make('mentor_feedback')
                                            ->label('Komentar Pembimbing')
                                            ->rows(3)
                                            ->disabled($isMahasiswa || $isView),

                                        Forms\Components\Select::make('status')
                                            ->label('Status Review')
                                            ->options([
                                                'submitted' => '⏳ Menunggu Review',
                                                'approved' => '✅ Disetujui',
                                                'revision_needed' => '❌ Perlu Perbaikan',
                                            ])
                                            ->required()
                                            ->disabled($isMahasiswa || $isView)
                                            ->default('submitted'),
                                    ])
                                    ->compact()
                                    ->visible(!$isMahasiswa || $form->getOperation() !== 'create'),
                            ])
                            ->columnSpan(2),

                        // KOLOM KANAN (SIDEBAR) - 1/3 Lebar
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Section::make('Informasi Logbook')
                                    ->schema([
                                        Forms\Components\Select::make('mahasiswa_id')
                                            ->relationship('mahasiswa', 'nim')
                                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->nim} - {$record->user->name}")
                                            ->required()
                                            ->hidden($isMahasiswa),

                                        Forms\Components\DatePicker::make('start_date')
                                            ->label('Tanggal Kegiatan')
                                            ->required()
                                            ->native(false)
                                            ->maxDate(now())
                                            ->disabled($isPembimbing || $isView)
                                            ->default(now())
                                            ->unique(
                                                ignoreRecord: true,
                                                modifyRuleUsing: fn (Unique $rule) => $mahasiswaId
                                                    ? $rule->where('mahasiswa_id', $mahasiswaId)
                                                    : $rule,
                                            )
                                            ->validationMessages([
                                                'unique' => 'Logbook untuk tanggal ini sudah dibuat.',
                                            ]),
                                    ])->compact(),

                                Forms\Components\Section::make('📎 Lampiran')
                                    ->schema([
                                        Forms\Components\FileUpload::make('attachment')
                                            ->label('Unggah File')
                                            ->directory('logbooks')
                                            ->image()
                                            ->hidden($isView || $isPembimbing),

                                        Forms\Components\Placeholder::make('attachment_preview')
                                            ->label('File Terunggah')
                                            ->content(fn ($record) => $record && $record->attachment
                                                ? new \Illuminate\Support\HtmlString('
                                                    <div class="flex flex-col gap-2">
                                                        <a href="' . asset('storage/' . $record->attachment) . '" target="_blank" class="bg-primary-600 text-white text-center px-3 py-2 rounded-lg text-xs font-bold hover:bg-primary-700 transition block">
                                                            📄 LIHAT / UNDUH LAMPIRAN
                                                        </a>
                                                        ' . (str_ends_with(strtolower($record->attachment), '.jpg') || str_ends_with(strtolower($record->attachment), '.png') || str_ends_with(strtolower($record->attachment), '.jpeg')
                                                            ? '<div class="mt-1"><img src="' . asset('storage/' . $record->attachment) . '" class="w-full rounded shadow-sm border"></div>'
                                                            : '') . '
                                                    </div>')
                                                : '<span class="text-xs text-gray-400 font-medium">Belum ada file.</span>'),
                                    ])->compact(),
                            ])
                            ->columnSpan(1),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('start_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('mahasiswa.user.name')
                    ->label('Mahasiswa')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'submitted',
                        'success' => 'approved',
                        'danger' => 'revision_needed',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'submitted' => 'Menunggu Review',
                        'approved' => 'Disetujui',
                        'revision_needed' => 'Perlu Perbaikan',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'submitted' => 'Menunggu Review',
                        'approved' => 'Disetujui',
                        'revision_needed' => 'Perlu Perbaikan',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label(fn($record) => Auth::user()->role === 'pembimbing' ? 'Beri Feedback' : 'Edit'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approve_selected')
                        ->label('Setujui Laporan Terpilih')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn (\Illuminate\Database\Eloquent\Collection $records) => $records->each->update(['status' => 'approved']))
                        ->visible(fn () => Auth::user()->role === 'pembimbing' || Auth::user()->role === 'admin'),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('approve_all_pending')
                    ->label('Setujui Semua Laporan')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function () {
                        $user = Auth::user();
                        $query = \App\Models\WeeklyLogbook::where('status', 'submitted');

                        // Jika bukan admin, hanya setujui mahasiswa bimbingannya sendiri
                        if ($user->role !== 'admin') {
                            $query->whereHas('mahasiswa.pengajuanMagang', function ($q) use ($user) {
                                $q->where('pembimbing_id', $user->pembimbing?->id);
                            });
                        }

                        $query->update(['status' => 'approved']);
                    })
                    ->visible(fn () => Auth::user()->role === 'pembimbing' || Auth::user()->role === 'admin'),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if ($user->role === 'mahasiswa') {
            return $query->where('mahasiswa_id', $user->mahasiswa?->id);
        }

        if ($user->role === 'pembimbing') {
            return $query->whereHas('mahasiswa.pengajuanMagang', function ($q) use ($user) {
                $q->where('pembimbing_id', $user->pembimbing?->id);
            });
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWeeklyLogbooks::route('/'),
            'create' => Pages\CreateWeeklyLogbook::route('/create'),
            'view' => Pages\ViewWeeklyLogbook::route('/{record}'),
            'edit' => Pages\EditWeeklyLogbook::route('/{record}/edit'),
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
            return (string) static::getModel()::whereHas('mahasiswa.pengajuanMagang', function ($q) use ($user) {
                $q->where('pembimbing_id', $user->pembimbing?->id);
            })->count();
        }

        return (string) static::getModel()::count();
    }
}
