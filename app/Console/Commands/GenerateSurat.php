<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PengajuanMagang;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon; // ✅ Tambahkan ini

class GenerateSurat extends Command
{
    protected $signature = 'generate:surat {id}';
    protected $description = 'Generate surat penerimaan magang untuk pengajuan tertentu';

    public function handle()
    {
        $id = $this->argument('id');

        $record = PengajuanMagang::with(['mahasiswa.user', 'pembimbing.user'])->find($id);

        if (!$record) {
            $this->error("PengajuanMagang dengan ID $id tidak ditemukan!");
            return 1;
        }

        // Ambil user admin sebagai verifikator
        $adminUser = \App\Models\User::where('role', 'admin')->first();

        // Ambil template aktif untuk surat penerimaan
        $template = \App\Models\TemplateSurat::where('jenis_surat', \App\Models\TemplateSurat::JENIS_PENERIMAAN)
            ->where('is_active', true)
            ->first();

        if (!$template) {
            $this->error('Tidak ada template surat penerimaan aktif!');
            return 1;
        }

        // ✅ Tambahkan path QR dummy agar tidak error
        $qrCodePath = 'images/sample-qr.png';

        $nimAkhir    = substr($record->mahasiswa->nim, -3);
        $tahun       = now()->format('Y');
        $nomerSurat  = $template->nomer_surat . '/' . $nimAkhir . '/' . $tahun;

        // Data untuk dimasukkan ke Blade
        $pdfData = [
            'id_pengajuan' => $record->id,
            'nomer_surat' => $nomerSurat,
            'mahasiswa_name' => $record->mahasiswa->user->name,
            'nim' => $record->mahasiswa->nim,
            'pembimbing_name' => $record->pembimbing->user->name ?? 'N/A',
            'tanggal_mulai' => Carbon::parse($record->tanggal_mulai)->format('d F Y'),
            'tanggal_selesai' => Carbon::parse($record->tanggal_selesai)->format('d F Y'),
            'bidang_diminati' => $record->bidang_diminati,
            'qr_code_path' => public_path($qrCodePath),
            'tanggal_verifikasi' => now()->format('d F Y'),
            'verified_by' => $adminUser->name ?? 'Admin',
            'template' => $template,
        ];

        // Render Blade dari template surat penerimaan
        $renderedContent = Blade::render($template->content_template, $pdfData);

        // Buat PDF
        $pdf = Pdf::loadHTML($renderedContent);

        $pdfPath = storage_path("app/public/pengajuan-magang/surat_penerimaan_{$id}.pdf");

        Storage::disk('public')->put("pengajuan-magang/surat_penerimaan_{$id}.pdf", $pdf->output());

        $this->info("Surat penerimaan berhasil dibuat di: " . $pdfPath);

        return 0;
    }
}

