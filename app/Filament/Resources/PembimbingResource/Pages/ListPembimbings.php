<?php

namespace App\Filament\Resources\PembimbingResource\Pages;

use App\Filament\Resources\PembimbingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPembimbings extends ListRecords
{
    protected static string $resource = PembimbingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
