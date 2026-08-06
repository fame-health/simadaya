<?php

namespace App\Filament\Resources;

namespace App\Filament\Resources\MahasiswaResource\Pages;

use App\Filament\Resources\MahasiswaResource;
use Filament\Actions\Action;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;
use Carbon\Carbon;

class ViewMahasiswa extends ViewRecord
{
    protected static string $resource = MahasiswaResource::class;

    protected static ?string $title = 'Detail Mahasiswa';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('edit')
                ->label('Edit Data')
                ->icon('heroicon-o-pencil-square')
                ->color('warning')
                ->url(fn () => static::getResource()::getUrl('edit', ['record' => $this->record])),

            Action::make('delete')
                ->label('Hapus Data')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Hapus Data Mahasiswa')
                ->modalDescription('Apakah Anda yakin ingin menghapus data ini?')
                ->modalSubmitActionLabel('Ya, Hapus')
                ->action(fn () => $this->record->delete())
                ->successRedirectUrl(static::getResource()::getUrl('index')),

            Action::make('back')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-left')
                ->url(fn () => \Filament\Facades\Filament::getUrl())
                ->color('gray'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                /* ================================
                 * HEADER: MINIMALIST IDENTITY
                 * ================================ */
                Section::make()
                    ->schema([
                        \Filament\Infolists\Components\Split::make([
                            \Filament\Infolists\Components\Group::make([
                                \Filament\Infolists\Components\Grid::make(1)
                                    ->schema([
                                        \Filament\Infolists\Components\Group::make([
                                            ImageEntry::make('profile_photo_path')
                                                ->hiddenLabel()
                                                ->disk('public')
                                                ->height(120)
                                                ->circular()
                                                ->extraAttributes([
                                                    'class' => 'ring-4 ring-gray-50 shadow-sm border border-gray-100',
                                                ]),
                                            \Filament\Infolists\Components\Group::make([
                                                TextEntry::make('user.name')
                                                    ->hiddenLabel()
                                                    ->weight(FontWeight::Bold)
                                                    ->size(TextEntry\TextEntrySize::Large)
                                                    ->extraAttributes(['class' => 'text-gray-900 text-3xl tracking-tight']),
                                                TextEntry::make('nim')
                                                    ->hiddenLabel()
                                                    ->icon('heroicon-m-identification')
                                                    ->weight(FontWeight::Medium)
                                                    ->extraAttributes(['class' => 'text-gray-500 mt-1']),
                                                \Filament\Infolists\Components\Group::make([
                                                    TextEntry::make('ipk')
                                                        ->label('IPK')
                                                        ->badge()
                                                        ->color(fn($state) => $state >= 3.5 ? 'success' : 'warning'),
                                                    TextEntry::make('semester')
                                                        ->label('Semester')
                                                        ->badge()
                                                        ->color('info'),
                                                ])->extraAttributes(['class' => 'flex items-center gap-x-2 mt-4']),
                                            ]),
                                        ])->extraAttributes(['class' => 'flex items-center gap-x-10']),
                                    ]),
                            ])->grow(true),

                            \Filament\Infolists\Components\Group::make([
                                TextEntry::make('created_at')
                                    ->label('Terdaftar Sejak')
                                    ->date('d F Y')
                                    ->alignEnd()
                                    ->extraAttributes(['class' => 'text-gray-400 text-xs']),
                            ])->grow(false)->extraAttributes(['class' => 'flex items-start']),
                        ])->from('md'),
                    ])
                    ->extraAttributes([
                        'class' => 'bg-white border-b border-gray-100 shadow-none p-10 mb-8',
                    ]),

                /* ================================
                 * BODY: STRUCTURED BIODATA
                 * ================================ */
                \Filament\Infolists\Components\Grid::make(3)
                    ->schema([
                        /* LEFT COLUMN: PRIMARY DATA */
                        \Filament\Infolists\Components\Group::make([
                            Section::make('Informasi Akademik')
                                ->icon('heroicon-o-academic-cap')
                                ->collapsible()
                                ->schema([
                                    TextEntry::make('universitas')
                                        ->label('Universitas / Sekolah')
                                        ->weight(FontWeight::Medium)
                                        ->icon('heroicon-s-building-library'),
                                    TextEntry::make('fakultas')
                                        ->label('Fakultas / Departemen')
                                        ->icon('heroicon-s-academic-cap'),
                                    TextEntry::make('jurusan')
                                        ->label('Program Studi')
                                        ->icon('heroicon-s-book-open')
                                        ->color('gray'),
                                ])->columns(1),

                            Section::make('Domisili & Alamat')
                                ->icon('heroicon-o-map-pin')
                                ->collapsible()
                                ->schema([
                                    TextEntry::make('alamat')
                                        ->hiddenLabel()
                                        ->icon('heroicon-s-map-pin')
                                        ->extraAttributes(['class' => 'text-gray-600 italic']),
                                ]),
                        ])->columnSpan(2),

                        /* RIGHT COLUMN: SECONDARY DATA */
                        \Filament\Infolists\Components\Group::make([
                            Section::make('Kontak Personal')
                                ->icon('heroicon-o-phone')
                                ->schema([
                                    TextEntry::make('nomor_hp')
                                        ->label('WhatsApp')
                                        ->icon('heroicon-s-phone')
                                        ->copyable(),
                                    TextEntry::make('user.email')
                                        ->label('Email Akun')
                                        ->icon('heroicon-s-envelope')
                                        ->copyable(),
                                ]),

                            Section::make('Data Pribadi')
                                ->icon('heroicon-o-user')
                                ->schema([
                                    TextEntry::make('jenis_kelamin')
                                        ->label('Jenis Kelamin')
                                        ->formatStateUsing(fn($state) => $state === 'L' ? 'Laki-laki' : 'Perempuan')
                                        ->icon('heroicon-s-user'),
                                    TextEntry::make('tanggal_lahir')
                                        ->label('Tanggal Lahir')
                                        ->date('d/m/Y')
                                        ->icon('heroicon-s-cake'),
                                ]),

                            \Filament\Infolists\Components\Group::make([
                                Section::make()
                                    ->schema([
                                        TextEntry::make('portal_footer')
                                            ->default('Portal Mahasiswa SIMADAYA')
                                            ->hiddenLabel()
                                            ->extraAttributes(['class' => 'text-[10px] text-center text-gray-300 uppercase tracking-widest']),
                                    ])->extraAttributes(['class' => 'bg-transparent border-none shadow-none']),
                            ]),
                        ])->columnSpan(1),
                    ]),
            ]);
    }
}
