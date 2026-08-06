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
        return 4;
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
                Forms\Components\Select::make('mahasiswa_id')
                    ->label('Mahasiswa')
                    ->relationship('mahasiswa', 'nim')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->user->name} ({$record->nim})")
                    ->required()
                    ->searchable()
                    ->preload()
                    ->disabled($isMahasiswa || $form->getOperation() === 'edit'),
                Forms\Components\Select::make('pembimbing_id')
                    ->label('Pembimbing')
                    ->relationship('pembimbing', 'nip')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->user->name} ({$record->nip})")
                    ->required()
                    ->searchable()
                    ->preload()
                    ->disabled($isMahasiswa),
                Forms\Components\TextInput::make('aspek_penilaian')
                    ->label('Aspek Penilaian')
                    ->required()
                    ->maxLength(255)
                    ->disabled($isMahasiswa),
                Forms\Components\TextInput::make('nilai')
                    ->label('Nilai')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->disabled($isMahasiswa),
                Forms\Components\TextInput::make('bobot')
                    ->label('Bobot')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(1)
                    ->step(0.01)
                    ->disabled($isMahasiswa),
                Forms\Components\Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->maxLength(65535)
                    ->columnSpanFull()
                    ->disabled($isMahasiswa),
                Forms\Components\TextInput::make('nilai_akhir')
                    ->label('Nilai Akhir')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\TextInput::make('grade')
                    ->label('Grade')
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\DateTimePicker::make('tanggal_penilaian')
                    ->label('Tanggal Penilaian')
                    ->required()
                    ->disabled($isMahasiswa),
            ]);
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
                ->with(['mahasiswa.user'])
                ->distinct('mahasiswa_id')
                ->get()
            : collect([]);
        $mahasiswaCount = $mahasiswaList->count();

        $ungradedCount = $isPembimbing && $pembimbing
            ? Penilaian::where('pembimbing_id', $pembimbing->id)
                ->whereNull('nilai')
                ->count()
            : 0;

        return $table
            ->heading(function () use ($isPembimbing, $mahasiswaCount, $ungradedCount, $mahasiswaList, $pembimbing) {
                if (!$isPembimbing) {
                    return null;
                }

                $html = '
                    <div style="background: linear-gradient(135deg, #e0f2fe 0%, #bfdbfe 100%); border: 2px solid #2563eb; border-radius: 12px; padding: 20px; margin: 16px 0; box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.1);">
                        <div style="display: flex; align-items: center; margin-bottom: 12px;">
                            <div style="background: #2563eb; color: white; border-radius: 50%; width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; font-size: 28px; margin-right: 16px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); transition: transform 0.2s;" title="Daftar Mahasiswa Bimbingan">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                            </div>
                            <h3 style="color: #1e40af; font-size: 22px; font-weight: 700; margin: 0;">DAFTAR MAHASISWA BIMBINGAN (' . e($mahasiswaCount) . ' MAHASISWA, ' . e($ungradedCount) . ' BELUM DINILAI)</h3>
                        </div>
                        <div style="background: #bfdbfe; border-left: 4px solid #2563eb; padding: 12px 16px; border-radius: 6px;">
                            <p style="color: #1e40af; font-size: 16px; margin: 0; line-height: 1.5;">
                                Tinjau dan masukkan nilai untuk mahasiswa yang Anda bimbing. Gunakan filter di bawah untuk memilih mahasiswa tertentu.
                            </p>
                        </div>';

                if ($mahasiswaCount > 0) {
                    $html .= '
                        <div style="margin-top: 16px;">
                            <table style="width: 100%; border-collapse: collapse; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
                                <thead>
                                    <tr style="background: #2563eb; color: white;">
                                        <th style="padding: 12px; text-align: left; font-size: 14px; font-weight: 600;">No</th>
                                        <th style="padding: 12px; text-align: left; font-size: 14px; font-weight: 600;">Nama Mahasiswa</th>
                                        <th style="padding: 12px; text-align: left; font-size: 14px; font-weight: 600;">NIM</th>
                                        <th style="padding: 12px; text-align: left; font-size: 14px; font-weight: 600;">Status Magang</th>
                                        <th style="padding: 12px; text-align: left; font-size: 14px; font-weight: 600;">Status Penilaian</th>
                                        <th style="padding: 12px; text-align: left; font-size: 14px; font-weight: 600;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>';
                    foreach ($mahasiswaList as $index => $pengajuan) {
                        $penilaian = Penilaian::where('mahasiswa_id', $pengajuan->mahasiswa_id)
                            ->where('pembimbing_id', $pembimbing->id)
                            ->first();
                        $statusPenilaian = $penilaian && $penilaian->nilai !== null ? 'Sudah Dinilai' : 'Belum Dinilai';
                        $statusColor = $penilaian && $penilaian->nilai !== null ? '#dcfce7' : '#fef2f2';
                        $statusTextColor = $penilaian && $penilaian->nilai !== null ? '#15803d' : '#991b1b';

                        $html .= '
                            <tr style="border-bottom: 1px solid #e5e7eb;">
                                <td style="padding: 12px; font-size: 14px; color: #1e40af;">' . ($index + 1) . '</td>
                                <td style="padding: 12px; font-size: 14px; color: #1e40af;">' . e($pengajuan->mahasiswa->user->name) . '</td>
                                <td style="padding: 12px; font-size: 14px; color: #1e40af;">' . e($pengajuan->mahasiswa->nim) . '</td>
                                <td style="padding: 12px; font-size: 14px;">
                                    <span style="padding: 4px 8px; border-radius: 4px; background: #dcfce7; color: #15803d; font-weight: 600;">
                                        ' . e($pengajuan->status) . '
                                    </span>
                                </td>
                                <td style="padding: 12px; font-size: 14px;">
                                    <span style="padding: 4px 8px; border-radius: 4px; background: ' . $statusColor . '; color: ' . $statusTextColor . '; font-weight: 600;">
                                        ' . e($statusPenilaian) . '
                                    </span>
                                </td>
                                <td style="padding: 12px; font-size: 14px;">
                                    <button type="button"
                                            wire:loading.attr="disabled"
                                            wire:click="mountAction(\'create\', { data: { mahasiswa_id: ' . $pengajuan->mahasiswa_id . ' } })"
                                            class="filament-button filament-button-size-sm"
                                            style="background: #2563eb; color: white; padding: 8px 16px; font-size: 14px; font-weight: 600; border-radius: 6px; border: none; cursor: pointer; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); transition: background 0.2s;"
                                            title="Buat Penilaian Baru untuk ' . e($pengajuan->mahasiswa->user->name) . '">
                                        <svg class="w-5 h-5 inline-block mr-1 animate-spin" fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" wire:loading wire:target="mountAction(\'create\')">
                                            <path clip-rule="evenodd" d="M12 19C15.866 19 19 15.866 19 12C19 8.13401 15.866 5 12 5C8.13401 5 5 8.13401 5 12C5 15.866 8.13401 19 12 19ZM12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" fill-rule="evenodd" fill="currentColor" opacity="0.2"></path>
                                            <path d="M2 12C2 6.47715 6.47715 2 12 2V5C8.13401 5 5 8.13401 5 12H2Z" fill="currentColor"></path>
                                        </svg>
                                        <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" wire:loading.remove wire:target="mountAction(\'create\')">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        New Penilaian
                                    </button>
                                </td>
                            </tr>';
                    }
                    $html .= '
                                </tbody>
                            </table>
                        </div>';
                } else {
                    $html .= '
                        <div style="margin-top: 16px; background: #fef2f2; border-left: 4px solid #dc2626; padding: 12px 16px; border-radius: 6px;">
                            <p style="color: #991b1b; font-size: 16px; margin: 0; line-height: 1.5;">
                                Tidak ada mahasiswa bimbingan yang ditemukan.
                            </p>
                        </div>';
                }

                $html .= '
                    <div style="margin-top: 16px;">
                        <button type="button"
                                class="filament-button filament-button-size-lg"
                                style="background: #2563eb; color: white; padding: 12px 24px; font-size: 18px; font-weight: 600; border-radius: 8px; border: none; cursor: pointer; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); transition: background 0.2s;"
                                title="Filter berdasarkan nama mahasiswa"
                                onclick="document.querySelector(\'[wire\\\\:model=\\\"tableFilters.mahasiswa.value\\\"]\').focus();">
                            <svg class="w-6 h-6 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                            </svg>
                            Table dibawah adalah nama mahasiswa yang sudah dinilai
                        </button>
                    </div>
                </div>';

                return new HtmlString($html);
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
                    ->label('Mahasiswa')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pembimbing.user.name')
                    ->label('Pembimbing')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('aspek_penilaian')
                    ->label('Aspek Penilaian')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nilai')
                    ->label('Nilai')
                    ->sortable(),
                Tables\Columns\TextColumn::make('bobot')
                    ->label('Bobot')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nilai_akhir')
                    ->label('Nilai Akhir')
                    ->sortable(),
                Tables\Columns\TextColumn::make('grade')
                    ->label('Grade')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_penilaian')
                    ->label('Tanggal Penilaian')
                    ->dateTime()
                    ->sortable(),
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
        ];
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();
        return $user && $user->role === 'pembimbing';
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
