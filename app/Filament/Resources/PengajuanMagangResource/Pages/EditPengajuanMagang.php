<?php

namespace App\Filament\Resources\PengajuanMagangResource\Pages;

use App\Filament\Resources\PengajuanMagangResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;

class EditPengajuanMagang extends EditRecord
{
    protected static string $resource = PengajuanMagangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Tombol hapus data
            Actions\DeleteAction::make(),

            // Tombol kembali
            Action::make('back')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),
        ];
    }
}
