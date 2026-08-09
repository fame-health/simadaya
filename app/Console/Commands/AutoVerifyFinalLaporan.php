<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PengajuanMagang;
use App\Models\TemplateSurat;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Blade;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Carbon;

class AutoVerifyFinalLaporan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-verify-laporan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically verify final reports and generate certificates for students';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Checking for pending final reports...");

        $records = PengajuanMagang::where('status', PengajuanMagang::STATUS_DITERIMA)
            ->whereNotNull('final_laporan')
            ->whereNull('sertifikat')
            ->get();

        if ($records->isEmpty()) {
            $this->info("No reports found to verify.");
            return;
        }

        $template = TemplateSurat::where('jenis_surat', TemplateSurat::JENIS_SERTIFIKAT)
            ->where('is_active', true)
            ->first();

        if (!$template) {
            $this->error("No active certificate template found.");
            return;
        }

        // Find a fallback admin for 'verified_by'
        $admin = User::where('role', 'admin')->first();
        $adminName = $admin ? $admin->name : 'System Automator';

        $count = 0;
        foreach ($records as $record) {
            $this->info("Processing: " . $record->mahasiswa->user->name);

            try {
                // Generate QR code
                $validationUrl = url('/validate-certificate/' . $record->id);
                $qrCode = new QrCode($validationUrl);
                $qrCode->setSize(120);
                $qrCode->setMargin(10);
                $writer = new PngWriter();
                $qrCodeImage = $writer->write($qrCode);
                $qrCodePath = 'pengajuan-magang/qr-codes/qr_sertifikat_' . $record->id . '.png';
                Storage::disk('public')->put($qrCodePath, $qrCodeImage->getString());

                // Prepare PDF Data
                $pdfData = [
                    'mahasiswa_name' => $record->mahasiswa->user->name,
                    'nim' => $record->mahasiswa->nim,
                    'pembimbing_name' => $record->pembimbing->user->name ?? 'N/A',
                    'tanggal_mulai' => Carbon::parse($record->tanggal_mulai)->format('d F Y'),
                    'tanggal_selesai' => Carbon::parse($record->tanggal_selesai)->format('d F Y'),
                    'bidang_diminati' => $record->bidang_diminati,
                    'qr_code_path' => Storage::disk('public')->path($qrCodePath),
                    'tanggal_verifikasi' => now()->format('d F Y'),
                    'verified_by' => $adminName,
                    'id_pengajuan' => $record->id,
                    'nomer_surat' => "070/DISBUD/" . date('Y') . "/" . str_pad($record->id, 3, '0', STR_PAD_LEFT),
                    'template' => $template,
                ];

                $renderedContent = Blade::render($template->content_template, $pdfData);
                $pdf = Pdf::loadHTML($renderedContent);
                $pdfPath = 'pengajuan-magang/sertifikat/sertifikat_' . $record->id . '.pdf';
                Storage::disk('public')->put($pdfPath, $pdf->output());

                $record->update([
                    'sertifikat' => $pdfPath,
                    'status' => PengajuanMagang::STATUS_SELESAI,
                ]);

                $count++;
            } catch (\Exception $e) {
                $this->error("Failed to process record ID {$record->id}: " . $e->getMessage());
            }
        }

        $this->info("Successfully verified {$count} reports.");
    }
}
