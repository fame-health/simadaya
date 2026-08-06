<?php

namespace App\Filament\Resources\FinalLaporanResource\Pages;

use App\Filament\Resources\FinalLaporanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFinalLaporan extends EditRecord
{
    protected static string $resource = FinalLaporanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
