<?php

namespace App\Filament\Resources\PengajuanMagangResource\Pages;

use App\Filament\Resources\PengajuanMagangResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePengajuanMagang extends CreateRecord
{
    protected static string $resource = PengajuanMagangResource::class;

    protected function getCreateFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateFormAction()
            ->label('AJUKAN PERMOHONAN');
    }

    protected function getCreateAnotherFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateAnotherFormAction()
            ->hidden();
    }
}
