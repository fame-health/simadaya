<?php

namespace App\Filament\Resources\WeeklyLogbookResource\Pages;

use App\Filament\Resources\WeeklyLogbookResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateWeeklyLogbook extends CreateRecord
{
    protected static string $resource = WeeklyLogbookResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Jika user yang login adalah mahasiswa, pastikan mahasiswa_id terisi
        if (auth()->user()->role === 'mahasiswa') {
            $data['mahasiswa_id'] = auth()->user()->mahasiswa?->id;
        }

        return $data;
    }
}
