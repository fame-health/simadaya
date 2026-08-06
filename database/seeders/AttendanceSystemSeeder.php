<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Pembimbing;
use App\Models\Mahasiswa;
use App\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AttendanceSystemSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Admin
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // 2. Create Location
        $location = Location::updateOrCreate(
            ['code' => 'HQ-01'],
            [
                'name' => 'Kantor Pusat',
                'address' => 'Jl. Jenderal Sudirman No. 1',
                'is_active' => true,
            ]
        );

        // 3. Create Mentor (Pembimbing)
        $userMentor = User::updateOrCreate(
            ['email' => 'mentor@gmail.com'],
            [
                'name' => 'Budi Mentor',
                'password' => Hash::make('password'),
                'role' => 'pembimbing',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        Pembimbing::updateOrCreate(
            ['user_id' => $userMentor->id],
            [
                'nip' => '198001012023011001',
                'jabatan' => 'Senior Developer',
                'bidang_keahlian' => 'Software Engineering',
            ]
        );

        // 4. Create Student (Mahasiswa)
        $userStudent = User::updateOrCreate(
            ['email' => 'student@gmail.com'],
            [
                'name' => 'Iwan Mahasiswa',
                'password' => Hash::make('password'),
                'role' => 'mahasiswa',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        Mahasiswa::updateOrCreate(
            ['user_id' => $userStudent->id],
            [
                'nim' => '20210001',
                'universitas' => 'Universitas Indonesia',
                'fakultas' => 'Ilmu Komputer',
                'jurusan' => 'Teknik Informatika',
                'semester' => 7,
                'ipk' => 3.75,
                'alamat' => 'Depok, Jawa Barat',
                'tanggal_lahir' => '2002-05-20',
                'jenis_kelamin' => 'L',
            ]
        );
    }
}
