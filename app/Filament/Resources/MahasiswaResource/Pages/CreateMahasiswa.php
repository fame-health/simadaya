<?php

namespace App\Filament\Resources\MahasiswaResource\Pages;

use App\Filament\Resources\MahasiswaResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateMahasiswa extends CreateRecord
{
    protected static string $resource = MahasiswaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();
        if ($user && $user->role === 'mahasiswa') {
            $data['name'] = $user->name;
            $data['email'] = $user->email;
        }
        return $data;
    }
}
