<?php

namespace Tests\Feature;

use App\Models\AttendanceLog;
use App\Models\AttendanceSession;
use App\Models\Location;
use App\Models\Mahasiswa;
use App\Models\Pembimbing;
use App\Models\PengajuanMagang;
use App\Models\User;
use App\Services\Attendance\AttendanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceRevisionTest extends TestCase
{
    use RefreshDatabase;

    public function test_auto_generate_alpha_logs_creates_alpha_entry_for_missed_session()
    {
        $location = Location::create([
            'name' => 'Kantor Utama',
            'code' => 'HQ',
            'latitude' => 0.5,
            'longitude' => 101.4,
            'radius_meters' => 100,
            'is_active' => true,
        ]);

        $mentorUser = User::factory()->create(['role' => User::ROLE_PEMBIMBING, 'is_active' => true]);
        $mentor = Pembimbing::create([
            'user_id' => $mentorUser->id,
            'nip' => '12345678',
            'jabatan' => 'Pembimbing Utama',
            'bidang_keahlian' => 'IT',
        ]);

        $studentUser = User::factory()->create(['role' => User::ROLE_MAHASISWA, 'is_active' => true]);
        $student = Mahasiswa::create([
            'user_id' => $studentUser->id,
            'nim' => '12345',
            'universitas' => 'Universitas Riau',
            'fakultas' => 'Teknik',
            'jurusan' => 'Informatika',
            'semester' => 6,
            'ipk' => 3.8,
            'alamat' => 'Pekanbaru',
            'tanggal_lahir' => '2000-01-01',
            'jenis_kelamin' => 'L',
        ]);

        PengajuanMagang::create([
            'mahasiswa_id' => $student->id,
            'pembimbing_id' => $mentor->id,
            'surat_permohonan' => 'surat.pdf',
            'ktm' => 'ktm.jpg',
            'tanggal_mulai' => now()->subDays(5)->toDateString(),
            'tanggal_selesai' => now()->addDays(20)->toDateString(),
            'durasi_magang' => 30,
            'bidang_diminati' => 'IT',
            'status' => PengajuanMagang::STATUS_DITERIMA,
        ]);

        $session = AttendanceSession::create([
            'mentor_id' => $mentor->id,
            'location_id' => $location->id,
            'session_name' => 'Sesi Pagi',
            'session_date' => now()->subDay()->toDateString(),
            'attendance_start_at' => now()->subDay()->subHours(2),
            'attendance_end_at' => now()->subDay()->subHour(),
            'started_at' => now()->subDay()->subHours(2),
            'ended_at' => now()->subDay()->subHour(),
            'status' => AttendanceSession::STATUS_ENDED,
        ]);

        $service = app(AttendanceService::class);
        $count = $service->autoGenerateAlphaLogs($session);

        $this->assertEquals(1, $count);
        $this->assertDatabaseHas('attendance_logs', [
            'session_id' => $session->id,
            'student_id' => $student->id,
            'status' => AttendanceLog::STATUS_ALPHA,
            'reason' => 'Tanpa Keterangan (Alpa)',
        ]);
    }

    public function test_riwayat_absensi_pdf_download_route_returns_pdf_stream()
    {
        $studentUser = User::factory()->create(['role' => User::ROLE_MAHASISWA, 'is_active' => true]);
        $student = Mahasiswa::create([
            'user_id' => $studentUser->id,
            'nim' => '99999',
            'universitas' => 'Universitas Riau',
            'fakultas' => 'Teknik',
            'jurusan' => 'Informatika',
            'semester' => 6,
            'ipk' => 3.8,
            'alamat' => 'Pekanbaru',
            'tanggal_lahir' => '2000-01-01',
            'jenis_kelamin' => 'L',
        ]);

        PengajuanMagang::create([
            'mahasiswa_id' => $student->id,
            'surat_permohonan' => 'surat.pdf',
            'ktm' => 'ktm.jpg',
            'tanggal_mulai' => now()->subDays(5)->toDateString(),
            'tanggal_selesai' => now()->addDays(20)->toDateString(),
            'durasi_magang' => 30,
            'bidang_diminati' => 'IT',
            'status' => PengajuanMagang::STATUS_DITERIMA,
        ]);

        AttendanceLog::create([
            'student_id' => $student->id,
            'scan_time' => now(),
            'status' => AttendanceLog::STATUS_PRESENT,
            'reason' => 'Hadir Tepat Waktu',
        ]);

        $response = $this->actingAs($studentUser)
            ->get(route('riwayat-absensi.pdf'));

        $response->assertOk();
    }
}
