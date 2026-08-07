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
            Actions\Action::make('approve')
                ->label('Setujui Laporan')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update(['status' => 'approved']);

                    \Filament\Notifications\Notification::make()
                        ->title('Laporan telah disetujui')
                        ->success()
                        ->send();

                    return redirect($this->getResource()::getUrl('index'));
                })
                ->visible(fn () =>
                    (auth()->user()->role === 'pembimbing' || auth()->user()->role === 'admin') &&
                    $this->record->status !== 'approved'
                ),
            Actions\DeleteAction::make(),
        ];
    }
}
