<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use App\Models\Mahasiswa;
use App\Models\PengajuanMagang;
use App\Models\Penilaian;
use App\Models\AttendanceLog;
use Carbon\Carbon;

class ProgressSteps extends Widget
{
    protected static string $view = 'filament.widgets.progress-steps';
    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Auth::check() && Auth::user()->role === 'mahasiswa';
    }

    public function getStepsData(): array
    {
        $user = Auth::user();

        $steps = [
            [
                'title'        => 'Isi Data Mahasiswa',
                'description'  => 'Mahasiswa wajib mengisi data mahasiswa.',
                'completed'    => false,
                'active'       => false,
                'url'          => route('filament.dashboard.resources.mahasiswas.create'),
                'button_text'  => 'Isi Data',
                'status'       => null,
                'completed_at' => null,
                'keterangan'   => null,
                'color'        => 'gray',
            ],
            [
                'title'        => 'Pengajuan Magang',
                'description'  => 'Submit pengajuan magang dengan surat permohonan dan KTM.',
                'completed'    => false,
                'active'       => false,
                'url'          => route('filament.dashboard.resources.pengajuan-magangs.create'),
                'button_text'  => 'Ajukan Magang',
                'status'       => null,
                'completed_at' => null,
                'keterangan'   => null,
                'color'        => 'gray',
            ],
            [
                'title'        => 'Logbook Mingguan',
                'description'  => 'Isi laporan kegiatan mingguan Anda.',
                'completed'    => false,
                'active'       => false,
                'url'          => route('filament.dashboard.resources.weekly-logbooks.index'),
                'button_text'  => 'Isi Logbook',
                'status'       => null,
                'completed_at' => null,
                'keterangan'   => null,
                'color'        => 'gray',
            ],
            [
                'title'        => 'Penilaian',
                'description'  => 'Menunggu penilaian dari pembimbing.',
                'completed'    => false,
                'active'       => false,
                'url'          => route('filament.dashboard.resources.penilaians.index'),
                'button_text'  => 'Lihat Penilaian',
                'status'       => null,
                'completed_at' => null,
                'keterangan'   => null,
                'color'        => 'gray',
            ],
            [
                'title'        => 'Laporan Akhir',
                'description'  => 'Upload laporan akhir dan dapatkan sertifikat.',
                'completed'    => false,
                'active'       => false,
                'url'          => route('filament.dashboard.resources.final-laporans.index'),
                'button_text'  => 'Upload Laporan',
                'status'       => null,
                'completed_at' => null,
                'keterangan'   => null,
                'color'        => 'gray',
            ],
        ];

        if (!$user || $user->role !== 'mahasiswa') {
            return $this->finalizeSteps($steps);
        }

        $mahasiswa = Mahasiswa::where('user_id', $user->id)->first();

        // -----------------------------------------------------------
        // STEP 1: DATA MAHASISWA
        // -----------------------------------------------------------
        if ($mahasiswa) {
            $steps[0]['completed']    = true;
            $steps[0]['color']        = 'success';
            $steps[0]['url']          = route('filament.dashboard.resources.mahasiswas.edit', $mahasiswa->id);
            $steps[0]['button_text']  = 'Edit Data';
            $steps[0]['completed_at'] = $mahasiswa->created_at?->format('d M Y');
            $steps[0]['keterangan']   = 'Data mahasiswa telah diisi.';
        } else {
            $steps[0]['active']   = true;
            $steps[0]['color']    = 'warning';
            $steps[0]['status']   = 'Belum Lengkap';
            $steps[0]['keterangan'] = 'Lengkapi data mahasiswa terlebih dahulu.';
            return $this->finalizeSteps($steps);
        }

        // -----------------------------------------------------------
        // STEP 2: PENGAJUAN MAGANG
        // -----------------------------------------------------------
        $pengajuan = PengajuanMagang::where('mahasiswa_id', $mahasiswa->id)
            ->latest()
            ->first();

        if (!$pengajuan) {
            $steps[1]['active']     = true;
            $steps[1]['color']      = 'warning';
            $steps[1]['status']     = 'Belum Diajukan';
            $steps[1]['keterangan'] = 'Silakan ajukan magang dengan melampirkan dokumen.';
            return $this->finalizeSteps($steps);
        }

        $statusMap = [
            PengajuanMagang::STATUS_PENDING  => ['text' => 'Sedang Diproses', 'color' => 'warning'],
            PengajuanMagang::STATUS_DITERIMA => ['text' => 'Diterima',        'color' => 'success'],
            PengajuanMagang::STATUS_DITOLAK  => ['text' => 'Ditolak',         'color' => 'danger'],
            PengajuanMagang::STATUS_SELESAI  => ['text' => 'Selesai',         'color' => 'success'],
        ];

        $info = $statusMap[$pengajuan->status];
        $steps[1]['status']     = $info['text'];
        $steps[1]['color']      = $info['color'];
        $steps[1]['keterangan'] = match ($pengajuan->status) {
            PengajuanMagang::STATUS_PENDING  => 'Pengajuan sedang diverifikasi oleh admin.',
            PengajuanMagang::STATUS_DITERIMA => 'Pengajuan disetujui. Menunggu penilaian.',
            PengajuanMagang::STATUS_DITOLAK  => $pengajuan->alasan_penolakan ?? 'Pengajuan ditolak. Silakan ajukan ulang.',
            PengajuanMagang::STATUS_SELESAI  => 'Magang selesai. Upload laporan akhir.',
            default => 'Menunggu verifikasi.',
        };

        // -----------------------------------------------------------
        // Jika DITERIMA / SELESAI → STEP 2 completed
        // -----------------------------------------------------------
        if (in_array($pengajuan->status, [
            PengajuanMagang::STATUS_DITERIMA,
            PengajuanMagang::STATUS_SELESAI
        ])) {

            $steps[1]['completed']    = true;
            $steps[1]['completed_at'] = $pengajuan->tanggal_verifikasi?->format('d M Y');
            $steps[1]['url']          = route('filament.dashboard.resources.pengajuan-magangs.index');
            $steps[1]['button_text']  = 'Lihat Detail';

            // ------------------------------------------------------
            // 🔥 Tambahkan tombol UNDUH SURAT BALASAN
            // ------------------------------------------------------
            if (!empty($pengajuan->surat_balasan)) {
                $steps[1]['download_url']  = asset('storage/' . $pengajuan->surat_balasan);
                $steps[1]['download_label'] = 'Unduh Surat Balasan';
            }

            // ------------------------------------------------------
            // 🔥 Tambahkan UNDUH ID CARD MAHASISWA di Step 2
            // ------------------------------------------------------
            if ($mahasiswa && !empty($mahasiswa->id_card)) {
                $steps[1]['idcard_url']  = asset('storage/' . $mahasiswa->id_card);
                $steps[1]['idcard_label'] = 'Unduh ID Card';
            }

        }

        // STATUS DITOLAK
        elseif ($pengajuan->status === PengajuanMagang::STATUS_DITOLAK) {

            $steps[1]['active']      = true;
            $steps[1]['url']         = route('filament.dashboard.resources.pengajuan-magangs.create');
            $steps[1]['button_text'] = 'Ajukan Ulang';
            return $this->finalizeSteps($steps);
        }

        // STATUS PENDING
        elseif ($pengajuan->status === PengajuanMagang::STATUS_PENDING) {

            $steps[1]['active']      = true;
            $steps[1]['url']         = route('filament.dashboard.resources.pengajuan-magangs.edit', $pengajuan->id);
            $steps[1]['button_text'] = 'Edit Pengajuan';
            return $this->finalizeSteps($steps);
        }

        // -----------------------------------------------------------
        // STEP 3: LOGBOOK MINGGUAN
        // -----------------------------------------------------------
        $startDate = \Carbon\Carbon::parse($pengajuan->tanggal_mulai);
        $endDate = \Carbon\Carbon::parse($pengajuan->tanggal_selesai);
        $totalWeeks = max(1, (int)ceil($startDate->diffInDays($endDate) / 7));

        $approvedLogbooksCount = \App\Models\WeeklyLogbook::where('mahasiswa_id', $mahasiswa->id)
            ->where('status', 'approved')
            ->count();

        $steps[2]['keterangan'] = "Wajib mengisi {$totalWeeks} logbook (Disetujui: {$approvedLogbooksCount}/{$totalWeeks}).";

        if ($approvedLogbooksCount >= $totalWeeks) {
            $steps[2]['completed'] = true;
            $steps[2]['color'] = 'success';
            $steps[2]['status'] = 'Lengkap';
        } else {
            $steps[2]['active'] = true;
            $steps[2]['color'] = 'warning';
            $steps[2]['status'] = "Progres: {$approvedLogbooksCount}/{$totalWeeks}";
            return $this->finalizeSteps($steps);
        }

        // -----------------------------------------------------------
        // STEP 4: PENILAIAN
        // -----------------------------------------------------------
        $penilaian = Penilaian::where('mahasiswa_id', $mahasiswa->id)
            ->whereNotNull('nilai')
            ->first();

        if (!$penilaian) {
            $steps[3]['active']      = true;
            $steps[3]['status']      = 'Menunggu Penilaian';
            $steps[3]['color']       = 'warning';
            $steps[3]['keterangan']  = 'Logbook lengkap. Silakan hubungi pembimbing untuk penilaian.';
            return $this->finalizeSteps($steps);
        }

        $steps[3]['completed']    = true;
        $steps[3]['color']        = 'success';
        $steps[3]['completed_at'] = $penilaian->updated_at?->format('d M Y');
        $steps[3]['keterangan']   = 'Penilaian telah diberikan oleh pembimbing.';
        $steps[3]['status']       = 'Dinilai';

        // -----------------------------------------------------------
        // STEP 5: LAPORAN AKHIR + SERTIFIKAT
        // -----------------------------------------------------------
        $laporanUploaded = !empty($pengajuan->final_laporan);
        $sertifikatReady = !empty($pengajuan->sertifikat);

        if ($laporanUploaded && $sertifikatReady) {

            $steps[4]['completed']    = true;
            $steps[4]['color']        = 'success';
            $steps[4]['status']       = 'Selesai & Sertifikat Tersedia';
            $steps[4]['keterangan']   = 'Selamat! Magang selesai. Sertifikat telah diterbitkan.';
            $steps[4]['completed_at'] = $pengajuan->updated_at?->format('d M Y');
            $steps[4]['button_text']  = 'Unduh Sertifikat';
            $steps[4]['url']          = asset('storage/' . $pengajuan->sertifikat);
        }
        elseif ($laporanUploaded) {

            $steps[4]['active']       = true;
            $steps[4]['color']        = 'success';
            $steps[4]['status']       = 'Upload Berhasil';
            $steps[4]['keterangan']   = 'Laporan akhir berhasil diunggah. Menunggu sertifikat.';
            $steps[4]['button_text']  = 'Lihat Penilaian';
            $steps[4]['url']          = route('filament.dashboard.resources.penilaians.index');
        }
        else {

            $steps[4]['active']       = true;
            $steps[4]['color']        = 'warning';
            $steps[4]['status']       = 'Upload Laporan Akhir';
            $steps[4]['keterangan']   = 'Silakan upload laporan akhir.';
            $steps[4]['button_text']  = 'Upload Laporan Akhir';
            $steps[4]['url']          = route('filament.dashboard.resources.final-laporans.edit', $pengajuan->id);
        }

        return $this->finalizeSteps($steps);
    }

    public function getAttendanceStatus(): string
    {
        $user = Auth::user();
        if (!$user || !$user->mahasiswa) {
            return 'Belum Absen';
        }

        $hasAttended = AttendanceLog::where('student_id', $user->mahasiswa->id)
            ->whereDate('scan_time', Carbon::today())
            ->exists();

        return $hasAttended ? 'Sudah Absen' : 'Belum Absen';
    }

    private function finalizeSteps(array $steps): array
    {
        foreach ($steps as &$step) {
            $step['active'] = $step['active'] ?? false;
            $step['completed'] = $step['completed'] ?? false;
            $step['color'] = $step['color'] ?? 'gray';
            $step['status'] = $step['status'] ?? null;
            $step['keterangan'] = $step['keterangan'] ?? null;
            $step['completed_at'] = $step['completed_at'] ?? null;
        }
        unset($step);

        return $steps;
    }
}
