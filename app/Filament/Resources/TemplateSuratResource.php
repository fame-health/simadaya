<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TemplateSuratResource\Pages;
use App\Models\TemplateSurat;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Actions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TemplateSuratResource extends Resource
{
    protected static ?string $model = TemplateSurat::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Manajemen Surat';
    protected static ?string $navigationLabel = 'Template Surat';
    protected static ?string $pluralLabel = 'Template Surat';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Informasi Template')
                ->schema([
                    TextInput::make('nama_template')
                        ->label('Nama Template')
                        ->required(),

                    Select::make('jenis_surat')
                        ->label('Jenis Surat')
                        ->options([
                            TemplateSurat::JENIS_PENERIMAAN => 'Surat Penerimaan',
                            TemplateSurat::JENIS_SERTIFIKAT => 'Sertifikat',
                        ])
                        ->required(),

                    Select::make('created_by')
                        ->label('Dibuat Oleh')
                        ->options(
                            User::whereIn('role', ['admin', 'pembimbing'])
                                ->pluck('name', 'id')
                        )
                        ->searchable()
                        ->required()
                        ->default(Auth::id()),

                    Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(true),
                ])
                ->columns(3),

            Section::make('Desain Template (HTML)')
                ->schema([
                    Textarea::make('content_template')
                        ->label('Isi Template (HTML)')
                        ->rows(20)
                        ->required()
                        ->reactive()
                        ->hint('Gunakan HTML yang valid (contoh: &lt;html&gt;&lt;body&gt;...&lt;/body&gt;&lt;/html&gt;)'),

                    Placeholder::make('preview_template')
                        ->label('Preview Template')
                        ->content(view('filament.template-preview'))
                        ->columnSpanFull(),
                ])
                ->extraAttributes([
                    'x-data' => '{}',
                    'x-init' => "
                        document.querySelector('[name=\"content_template\"]').addEventListener('input', function() {
                            let html = this.value;
                            let data = {
                                'nomor_surat': '" . ($form->getRecord() && $form->getRecord()->nomer_surat ? addslashes($form->getRecord()->nomer_surat) : '123/SRT/2025') . "',
                                'nama': 'John Doe',
                                'nim': '123456789',
                                'periode_magang': '1 Januari 2025 - 30 Juni 2025',
                                'tanggal': '" . now()->format('d M Y') . "',
                                'nama_kepala_dinas': 'Dr. Ahmad Yani',
                                'nama_pembimbing': 'Prof. Budi Santoso'
                            };
                            for (let key in data) {
                                html = html.replace(new RegExp('{{ ' + key + ' }}', 'g'), data[key]);
                            }
                            document.querySelector('#preview-iframe').srcdoc = html;
                        });
                    ",
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('nama_template')
                ->label('Nama Template')
                ->searchable(),

            TextColumn::make('jenis_surat')
                ->label('Jenis')
                ->badge()
                ->color(fn(string $state): string => match ($state) {
                    TemplateSurat::JENIS_PENERIMAAN => 'success',
                    TemplateSurat::JENIS_SERTIFIKAT => 'info',
                    default => 'gray',
                })
                ->formatStateUsing(fn($state) => ucfirst($state)),

            TextColumn::make('creator.name')
                ->label('Dibuat Oleh')
                ->sortable()
                ->searchable(),

            IconColumn::make('is_active')
                ->label('Aktif')
                ->boolean(),

            TextColumn::make('created_at')
                ->label('Dibuat Pada')
                ->dateTime('d M Y'),
        ])
        ->actions([
            Actions\ViewAction::make(),
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ])
        ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTemplateSurats::route('/'),
            'create' => Pages\CreateTemplateSurat::route('/create'),
            'edit' => Pages\EditTemplateSurat::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        return $user && in_array($user->role, ['admin', 'pembimbing']);
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();
        return $data;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if (!$user) {
            return $query->whereNull('id');
        }

        if ($user->role === 'admin') {
            return $query;
        }

        if ($user->role === 'pembimbing') {
            return $query->where('created_by', $user->id);
        }

        return $query->whereNull('id');
    }
}
