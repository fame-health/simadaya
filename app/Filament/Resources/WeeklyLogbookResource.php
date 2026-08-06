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

class WeeklyLogbookResource extends Resource
{
    protected static ?string $model = WeeklyLogbook::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'ALUR PELAKSANAAN PKL';

    protected static ?string $label = 'Logbook Mingguan';

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

        return $form
            ->schema([
                Forms\Components\Grid::make(3) // Gunakan grid untuk membagi layar
                    ->schema([
                        // KOLOM KIRI (UTAMA) - 2/3 Lebar
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Section::make('📋 Detail Kegiatan Mingguan')
                                    ->description('Isi laporan kegiatan Anda selama satu minggu ini.')
                                    ->schema([
                                        Forms\Components\RichEditor::make('activities')
                                            ->label('Ringkasan Kegiatan')
                                            ->placeholder('Apa saja yang Anda lakukan minggu ini?')
                                            ->required()
                                            ->disabled($isPembimbing || $isView)
                                            ->columnSpanFull(),

                                        Forms\Components\RichEditor::make('achievements')
                                            ->label('Hasil yang Dicapai')
                                            ->placeholder('Target atau output apa yang berhasil diselesaikan?')
                                            ->disabled($isPembimbing || $isView)
                                            ->columnSpanFull(),

                                        Forms\Components\RichEditor::make('problems')
                                            ->label('Kendala & Solusi')
                                            ->placeholder('Hambatan apa yang dihadapi dan bagaimana mengatasinya?')
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
                                Forms\Components\Section::make('ℹ️ Informasi Laporan')
                                    ->schema([
                                        Forms\Components\Select::make('mahasiswa_id')
                                            ->relationship('mahasiswa', 'nim')
                                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->nim} - {$record->user->name}")
                                            ->required()
                                            ->hidden($isMahasiswa),

                                        Forms\Components\TextInput::make('week_number')
                                            ->label('Minggu Ke-')
                                            ->prefix('Ke-')
                                            ->required()
                                            ->numeric()
                                            ->disabled($isPembimbing || $isView),

                                        Forms\Components\DatePicker::make('start_date')
                                            ->label('Mulai')
                                            ->required()
                                            ->native(false)
                                            ->disabled($isPembimbing || $isView),

                                        Forms\Components\DatePicker::make('end_date')
                                            ->label('Selesai')
                                            ->required()
                                            ->native(false)
                                            ->disabled($isPembimbing || $isView),
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
            ->columns([
                Tables\Columns\TextColumn::make('mahasiswa.user.name')
                    ->label('Mahasiswa')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('week_number')
                    ->label('Minggu')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Mulai')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Selesai')
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
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
}
