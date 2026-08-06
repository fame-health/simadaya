<?php

namespace App\Filament\Resources\MahasiswaResource\Pages;

use App\Filament\Resources\MahasiswaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Auth;

class EditMahasiswa extends EditRecord
{
    protected static string $resource = MahasiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ❌ Mahasiswa tidak bisa hapus, hanya admin
            Actions\DeleteAction::make()
                ->visible(fn () => Auth::check() && Auth::user()->role === 'admin'),
        ];
    }

    // ✅ Schema form edit
    public function form(Form $form): Form
    {
        $user = Auth::user();
        $isMahasiswa = $user && $user->role === 'mahasiswa';

        return $form->schema([
            // ✅ INFORMASI AKUN (Nama, Gmail, Password)
            Forms\Components\Section::make('Informasi Akun')
                ->icon('heroicon-o-lock-closed')
                ->description('Gunakan bagian ini untuk mengubah data login Anda (Email & Password)')
                ->relationship('user')
                ->visible(fn () => Auth::user()?->isAdmin() || Auth::user()?->isMahasiswa())
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nama Lengkap')
                        ->required()
                        ->maxLength(255)
                        ->autocomplete('none')
                        ->extraAttributes(['autocomplete' => 'none', 'name' => 'mahasiswa_full_name']),
                    Forms\Components\TextInput::make('email')
                        ->label('Email Akun (Gmail)')
                        ->email()
                        ->required()
                        ->maxLength(255)
                        ->autocomplete('none')
                        ->extraAttributes(['autocomplete' => 'none', 'name' => 'mahasiswa_account_email']),
                    Forms\Components\TextInput::make('password')
                        ->label('Ganti Password')
                        ->password()
                        ->placeholder('Kosongkan jika tidak ingin ganti password')
                        ->dehydrateStateUsing(fn ($state) => \Illuminate\Support\Facades\Hash::make($state))
                        ->dehydrated(fn ($state) => filled($state))
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->extraAttributes(['class' => 'shadow-md rounded-lg border border-blue-200 bg-blue-50/10']),

            Forms\Components\Section::make('Data Akademik')
                ->icon('heroicon-o-academic-cap')
                ->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('nim')
                            ->label('NIM / NIP Siswa'),

                        Forms\Components\TextInput::make('universitas')
                            ->label('Universitas / Sekolah'),

                        Forms\Components\TextInput::make('fakultas')
                            ->label('Fakultas'),

                        Forms\Components\TextInput::make('jurusan')
                            ->label('Jurusan/Program Studi'),

                        Forms\Components\TextInput::make('semester')
                            ->label('Semester / Kelas'),

                        Forms\Components\TextInput::make('ipk')
                            ->label('IPK / Nilai'),
                    ]),
                ])
                ->extraAttributes(['class' => 'shadow-md rounded-lg border border-gray-200']),

            Forms\Components\Section::make('Data Pribadi')
                ->icon('heroicon-o-user')
                ->schema([
                    Forms\Components\Textarea::make('alamat')
                        ->label('Domisili')
                        ->rows(4),

                    Forms\Components\DatePicker::make('tanggal_lahir')
                        ->label('Tanggal Lahir'),

                    Forms\Components\Select::make('jenis_kelamin')
                        ->label('Jenis Kelamin')
                        ->options([
                            'L' => 'Laki-laki',
                            'P' => 'Perempuan',
                        ]),
                ])
                ->extraAttributes(['class' => 'shadow-md rounded-lg border border-gray-200 mt-6']),
        ]);
    }

    // ✅ Simpan juga perubahan nama user
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Debugging: Kita biarkan data apa adanya dan pastikan Filament yang menangani via relationship()
        return $data;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $user = $this->record->user;
        if ($user) {
            $data['name'] = $user->name;
            $data['email'] = $user->email;
        }
        return $data;
    }

    // ✅ Akses kontrol (admin bebas, mahasiswa hanya data miliknya)
    protected function authorizeAccess(): void
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return; // admin bisa edit siapa saja
        }

        if ($user->role === 'mahasiswa') {
            if ($this->record->user_id !== $user->id) {
                abort(403, 'Anda tidak boleh mengedit data mahasiswa lain.');
            }
            return;
        }

        abort(403, 'Anda tidak memiliki akses.');
    }
}
