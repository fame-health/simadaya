<?php

namespace App\Filament\Resources\WeeklyLogbookResource\Pages;

use App\Filament\Resources\WeeklyLogbookResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWeeklyLogbooks extends ListRecords
{
    protected static string $resource = WeeklyLogbookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
