<?php

namespace App\Filament\Resources\WeeklyLogbookResource\Pages;

use App\Filament\Resources\WeeklyLogbookResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWeeklyLogbook extends EditRecord
{
    protected static string $resource = WeeklyLogbookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
