<?php

namespace App\Filament\Resources\FinalLaporanResource\Pages;

use App\Filament\Resources\FinalLaporanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFinalLaporans extends ListRecords
{
    protected static string $resource = FinalLaporanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
