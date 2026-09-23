<?php

namespace Tests\Feature;

use App\Models\Mahasiswa;
use App\Models\Pembimbing;
use App\Models\PengajuanMagang;
use App\Models\TemplateSurat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengajuanVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_verify_pengajuan_magang_without_404_error()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_active' => true]);

        $mentorUser = User::factory()->create(['role' => User::ROLE_PEMBIMBING, 'is_active' => true]);
        $mentor = Pembimbing::create([
            'user_id' => $mentorUser->id,
            'nip' => '11112222',
            'jabatan' => 'Pembimbing Utama',
            'bidang_keahlian' => 'IT',
        ]);

        $studentUser = User::factory()->create(['role' => User::ROLE_MAHASISWA, 'is_active' => true]);
        $student = Mahasiswa::create([
            'user_id' => $studentUser->id,
            'nim' => '77777',
            'universitas' => 'Universitas Riau',
            'fakultas' => 'Teknik',
            'jurusan' => 'Informatika',
            'semester' => 6,
            'ipk' => 3.8,
            'alamat' => 'Pekanbaru',
            'tanggal_lahir' => '2000-01-01',
            'jenis_kelamin' => 'L',
        ]);

        $pengajuan = PengajuanMagang::create([
            'mahasiswa_id' => $student->id,
            'surat_permohonan' => 'surat.pdf',
            'ktm' => 'ktm.jpg',
            'tanggal_mulai' => now()->addDay()->toDateString(),
            'tanggal_selesai' => now()->addDays(30)->toDateString(),
            'durasi_magang' => 30,
            'bidang_diminati' => 'IT',
            'status' => PengajuanMagang::STATUS_PENDING,
        ]);

        $response = $this->actingAs($admin)
            ->get('/dashboard/pengajuan-magangs/' . $pengajuan->id . '/view');

        $response->assertOk();
    }
}
