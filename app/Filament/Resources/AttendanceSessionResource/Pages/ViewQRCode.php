<?php

namespace App\Filament\Resources\AttendanceSessionResource\Pages;

use App\Filament\Resources\AttendanceSessionResource;
use App\Models\AttendanceSession;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;

class ViewQRCode extends Page
{
    use InteractsWithRecord;

    protected static string $resource = AttendanceSessionResource::class;

    protected static string $view = 'filament.resources.attendance-session-resource.pages.view-q-r-code';

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function getTitle(): string
    {
        return "QR Code Absensi: " . $this->record->session_name;
    }
}
