<?php

namespace Tests\Feature;

use App\Models\AttendanceLog;
use App\Models\Mahasiswa;
use App\Models\Pembimbing;
use App\Models\PengajuanMagang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermitApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_permit_submission_starts_as_pending_and_can_be_approved_by_mentor()
    {
        $mentorUser = User::factory()->create(['role' => User::ROLE_PEMBIMBING, 'is_active' => true]);
        $mentor = Pembimbing::create([
            'user_id' => $mentorUser->id,
            'nip' => '33334444',
            'jabatan' => 'Pembimbing Utama',
            'bidang_keahlian' => 'IT',
        ]);

        $studentUser = User::factory()->create(['role' => User::ROLE_MAHASISWA, 'is_active' => true]);
        $student = Mahasiswa::create([
            'user_id' => $studentUser->id,
            'nim' => '55555',
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

        $log = AttendanceLog::create([
            'student_id' => $student->id,
            'scan_time' => now(),
            'status' => AttendanceLog::STATUS_PENDING_PERMIT,
            'reason' => 'Izin Ada Keperluan Keluarga',
            'document_path' => 'attendance-permits/surat_izin.pdf',
        ]);

        $this->assertEquals(AttendanceLog::STATUS_PENDING_PERMIT, $log->status);

        // Mentor approves permit
        $log->update(['status' => AttendanceLog::STATUS_PERMIT]);

        $this->assertEquals(AttendanceLog::STATUS_PERMIT, $log->fresh()->status);
    }
}
