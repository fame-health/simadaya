<?php

namespace App\Filament\Resources\TemplateSuratResource\Pages;

use App\Filament\Resources\TemplateSuratResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTemplateSurat extends EditRecord
{
    protected static string $resource = TemplateSuratResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
