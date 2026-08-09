<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;

/**
 * @property-read User $authenticatedUser
 */
class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $modelLabel = 'Akun Penguna';

    protected static ?string $navigationGroup = 'SETTING';

    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Mahasiswa tidak bisa melihat menu Akun Pengguna
        return $user && !$user->isMahasiswa();
    }

    public static function form(Form $form): Form
    {
        /** @var User|null $authenticatedUser */
        $authenticatedUser = Filament::auth()->user();
        $isMahasiswa = $authenticatedUser && $authenticatedUser->isMahasiswa();
        $isPembimbing = $authenticatedUser && $authenticatedUser->isPembimbing();

        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pengguna')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\Select::make('role')
                            ->options([
                                User::ROLE_ADMIN => 'Admin',
                                User::ROLE_MAHASISWA => 'Mahasiswa',
                                User::ROLE_PEMBIMBING => 'Pembimbing',
                            ])
                            ->required()
                            ->disabled(fn (?User $record): bool =>
                                $isMahasiswa || $isPembimbing || ($record && ($record->isMahasiswa() || $record->isPembimbing()))),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(20)
                            ->rules(['nullable', 'regex:/^([0-9\s\-\+\(\)]*)$/']),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->inline(false)
                            ->disabled($isMahasiswa || $isPembimbing),
                    ])->columns(2),

                Forms\Components\Section::make('Keamanan')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->maxLength(255)
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        /** @var User|null $authenticatedUser */
        $authenticatedUser = Filament::auth()->user();
        $isMahasiswa = $authenticatedUser && $authenticatedUser->isMahasiswa();
        $isPembimbing = $authenticatedUser && $authenticatedUser->isPembimbing();

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        User::ROLE_ADMIN => 'danger',
                        User::ROLE_PEMBIMBING => 'warning',
                        User::ROLE_MAHASISWA => 'success',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        User::ROLE_ADMIN => 'Admin',
                        User::ROLE_MAHASISWA => 'Mahasiswa',
                        User::ROLE_PEMBIMBING => 'Pembimbing',
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Aktif'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('mahasiswa.nim')
                    ->label('NIM')
                    ->getStateUsing(fn (User $record): ?string => $record->mahasiswa?->nim)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('pembimbing.nip')
                    ->label('NIP')
                    ->getStateUsing(fn (User $record): ?string => $record->pembimbing?->nip)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        User::ROLE_ADMIN => 'Admin',
                        User::ROLE_MAHASISWA => 'Mahasiswa',
                        User::ROLE_PEMBIMBING => 'Pembimbing',
                    ])
                    ->hidden($isMahasiswa || $isPembimbing),
                Tables\Filters\Filter::make('is_active')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true))
                    ->label('Aktif saja')
                    ->hidden($isMahasiswa || $isPembimbing),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->hidden(fn (User $record): bool =>
                        ($isMahasiswa || $isPembimbing) && $record->id !== $authenticatedUser->id),
                Tables\Actions\DeleteAction::make()
                    ->hidden($isMahasiswa || $isPembimbing),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->hidden($isMahasiswa || $isPembimbing),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->hidden($isMahasiswa || $isPembimbing),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        /** @var User|null $authenticatedUser */
        $authenticatedUser = Filament::auth()->user();

        if (!$authenticatedUser) {
            return $query->whereNull('id'); // Return empty result if no authenticated user
        }

        if ($authenticatedUser->isMahasiswa() || $authenticatedUser->isPembimbing()) {
            return $query->where('id', $authenticatedUser->id); // Mahasiswa and Pembimbing can only see their own record
        }

        // Admins can see all users
        return $query;
    }

    public static function canCreate(): bool
    {
        /** @var User|null $authenticatedUser */
        $authenticatedUser = Filament::auth()->user();
        return $authenticatedUser && $authenticatedUser->isAdmin();
    }

    public static function canEdit(Model $record): bool
    {
        /** @var User|null $authenticatedUser */
        $authenticatedUser = Filament::auth()->user();
        return $authenticatedUser && (
            $authenticatedUser->isAdmin() ||
            (($authenticatedUser->isMahasiswa() || $authenticatedUser->isPembimbing()) && $authenticatedUser->id === $record->id)
        );
    }

    public static function canDelete(Model $record): bool
    {
        /** @var User|null $authenticatedUser */
        $authenticatedUser = Filament::auth()->user();
        return $authenticatedUser && $authenticatedUser->isAdmin();
    }
}
