<?php

namespace App\Filament\Resources\FinalLaporanResource\Pages;

use App\Filament\Resources\FinalLaporanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFinalLaporans extends ListRecords
{
    protected static string $resource = FinalLaporanResource::class;

    public function mount(): void
    {
        parent::mount();

        $user = auth()->user();
        if ($user && $user->role === 'mahasiswa' && $user->mahasiswa) {
            $record = \App\Models\PengajuanMagang::where('mahasiswa_id', $user->mahasiswa->id)
                ->whereIn('status', [
                    \App\Models\PengajuanMagang::STATUS_DITERIMA,
                    \App\Models\PengajuanMagang::STATUS_SELESAI
                ])
                ->latest()
                ->first();

            if ($record) {
                redirect($this->getResource()::getUrl('edit', ['record' => $record]));
            }
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
