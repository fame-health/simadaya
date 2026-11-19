<?php

namespace App\Filament\Resources\WeeklyLogbookResource\Pages;

use App\Filament\Resources\WeeklyLogbookResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Carbon;

class EditWeeklyLogbook extends EditRecord
{
    protected static string $resource = WeeklyLogbookResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $date = Carbon::parse($data['start_date'] ?? $this->record->start_date)->toDateString();
        $originalDate = $this->record->start_date->toDateString();

        if ($date === $originalDate) {
            // Preserve the original date range when an older weekly entry is only reviewed.
            $data['end_date'] = $this->record->end_date->toDateString();
            $data['week_number'] = $this->record->week_number;
        } else {
            $data['start_date'] = $date;
            $data['end_date'] = $date;
            $data['week_number'] = 1;
        }

        return $data;
    }

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
