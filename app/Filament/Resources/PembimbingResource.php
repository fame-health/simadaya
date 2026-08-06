<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PembimbingResource\Pages;
use App\Models\Pembimbing;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PembimbingResource extends Resource
{
    protected static ?string $model = Pembimbing::class;

        protected static ?string $navigationGroup = 'ALUR PELAKSANAAN PKL';

    public static function getNavigationSort(): ?int
{
    return 3; // Ganti X dengan angka sesuai urutan yang kamu inginkan
}

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    // Hide menu if user is mahasiswa
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->role !== 'mahasiswa';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('user_id')
                    ->default(Auth::id()),
                Forms\Components\TextInput::make('nip')->required(),
                Forms\Components\TextInput::make('jabatan')->required(),
                Forms\Components\TextInput::make('bidang_keahlian')->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('user.photo')
                    ->label('Foto')->circular(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Pembimbing')
                    ->searchable()->sortable(),

                Tables\Columns\TextColumn::make('nip')
                    ->icon('heroicon-o-identification')
                    ->tooltip('Nomor Induk Pegawai'),

                Tables\Columns\TextColumn::make('jabatan')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Dosen' => 'success',
                        'Koordinator' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('bidang_keahlian')
                    ->searchable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->visible(fn () => static::canCreate()),
            ]);
    }

    // Modify query to allow admin to see all Pembimbing records
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // If user is admin, return all records; otherwise, filter by user_id
        if (Auth::user()?->role === 'admin') {
            return $query;
        }

        return $query->where('user_id', Auth::id());
    }

    // Hide create button if data already exists for non-admin users
    public static function canCreate(): bool
    {
        if (Auth::user()?->role === 'mahasiswa') {
            return false;
        }

        // Allow admin to create regardless of existing data
        if (Auth::user()?->role === 'admin') {
            return true;
        }

        return !Pembimbing::where('user_id', Auth::id())->exists();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPembimbings::route('/'),
            'create' => Pages\CreatePembimbing::route('/create'),
            'edit' => Pages\EditPembimbing::route('/{record}/edit'),
        ];
    }
}
