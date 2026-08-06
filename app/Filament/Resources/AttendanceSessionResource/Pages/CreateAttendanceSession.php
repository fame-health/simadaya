<?php

namespace App\Filament\Resources\AttendanceSessionResource\Pages;

use App\Filament\Resources\AttendanceSessionResource;
use App\Models\Pembimbing;
use App\Services\TokenGeneratorService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateAttendanceSession extends CreateRecord
{
    protected static string $resource = AttendanceSessionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();
        $pembimbing = Pembimbing::where('user_id', $user->id)->first();

        $data['mentor_id'] = $pembimbing ? $pembimbing->id : null;
        $data['current_token'] = app(TokenGeneratorService::class)->generate();
        $data['expires_at'] = now()->addSeconds(10);
        $data['started_at'] = now();
        $data['status'] = 'active';

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view-qr', ['record' => $this->record]);
    }
}
