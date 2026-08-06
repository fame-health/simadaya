<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PengajuanMagang;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Blade;

class GenerateSertifikat extends Command
{
    // Perintah Artisan → php artisan generate:sertifikat {id}
    protected $signature = 'generate:sertifikat {id}';
    protected $description = 'Generate sertifikat PDF untuk pengajuan magang tertentu';

    public function handle()
    {
        $id = $this->argument('id');

        // Ambil data pengajuan + relasi mahasiswa & pembimbing
        $record = PengajuanMagang::with(['mahasiswa.user', 'pembimbing.user'])->find($id);

        if (!$record) {
            $this->error("❌ PengajuanMagang dengan ID $id tidak ditemukan!");
            return 1;
        }

        // Ambil user admin (untuk ditampilkan sebagai verifikator)
        $adminUser = \App\Models\User::where('role', 'admin')->first();

        // Ambil template sertifikat aktif
        $template = \App\Models\TemplateSurat::where('jenis_surat', \App\Models\TemplateSurat::JENIS_SERTIFIKAT)
            ->where('is_active', true)
            ->first();

        if (!$template) {
            $this->error('❌ Tidak ada template sertifikat aktif!');
            return 1;
        }

        // === Data yang dikirim ke view (Blade) ===
        $pdfData = [
            'id_pengajuan'       => $id, // 🔥 ini penting untuk nomor sertifikat
            'nomer_surat'        => "070/DISBUD/" . date('Y') . "/" . str_pad($id, 3, '0', STR_PAD_LEFT),
            'mahasiswa_name'     => $record->mahasiswa->user->name ?? '-',
            'nim'                => $record->mahasiswa->nim ?? '-',
            'pembimbing_name'    => $record->pembimbing->user->name ?? 'N/A',
            'tanggal_mulai'      => $record->tanggal_mulai?->format('d F Y') ?? '-',
            'tanggal_selesai'    => $record->tanggal_selesai?->format('d F Y') ?? '-',
            'bidang_diminati'    => $record->bidang_diminati ?? '-',
            'qr_code_path'       => public_path('images/sample-qr.png'), // QR dummy sementara
            'tanggal_verifikasi' => now()->format('d F Y'),
            'verified_by'        => $adminUser->name ?? 'Admin Dinas Kebudayaan',
            'template'           => $template,
        ];

        // Render template ke HTML (pakai Blade)
        $renderedContent = Blade::render($template->content_template, $pdfData);

        // Generate PDF dari HTML
        $pdf = Pdf::loadHTML($renderedContent);

        // Simpan ke storage/public
        $fileName = "pengajuan-magang/sertifikat_{$id}.pdf";
        Storage::disk('public')->put($fileName, $pdf->output());

        $this->info("✅ Sertifikat berhasil dibuat!");
        $this->info("📄 Lokasi file: storage/app/public/{$fileName}");

        return 0;
    }
}
