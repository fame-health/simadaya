<?php

namespace App\Filament\Resources\TemplateSuratResource\Pages;

use App\Filament\Resources\TemplateSuratResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTemplateSurats extends ListRecords
{
    protected static string $resource = TemplateSuratResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
