<?php

namespace App\Filament\Resources\FinalLaporanResource\Pages;

use App\Filament\Resources\FinalLaporanResource;
use App\Models\PengajuanMagang;
use App\Models\TemplateSurat;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Blade;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Filament\Notifications\Notification;

class ListFinalLaporans extends ListRecords
{
    protected static string $resource = FinalLaporanResource::class;

    public function mount(): void
    {
        parent::mount();

        $user = auth()->user();
        if ($user && $user->role === 'mahasiswa' && $user->mahasiswa) {
            $record = PengajuanMagang::where('mahasiswa_id', $user->mahasiswa->id)
                ->whereIn('status', [
                    PengajuanMagang::STATUS_DITERIMA,
                    PengajuanMagang::STATUS_SELESAI
                ])
                ->latest()
                ->first();

            if ($record) {
                redirect($this->getResource()::getUrl('edit', ['record' => $record]));
            }
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('verify_all')
                ->label('Verifikasi Semua Laporan')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => Auth::user()->role === 'admin')
                ->action(function () {
                    $records = PengajuanMagang::where('status', PengajuanMagang::STATUS_DITERIMA)
                        ->whereNotNull('final_laporan')
                        ->whereNull('sertifikat')
                        ->get();

                    if ($records->isEmpty()) {
                        Notification::make()
                            ->title('Tidak Ada Laporan')
                            ->body('Tidak ada laporan baru yang perlu diverifikasi saat ini.')
                            ->info()
                            ->send();
                        return;
                    }

                    $template = TemplateSurat::where('jenis_surat', TemplateSurat::JENIS_SERTIFIKAT)
                        ->where('is_active', true)
                        ->first();

                    if (!$template) {
                        Notification::make()
                            ->title('Template Sertifikat Tidak Ditemukan')
                            ->body('Pastikan ada template sertifikat yang aktif di Manajemen Surat.')
                            ->danger()
                            ->send();
                        return;
                    }

                    $count = 0;
                    foreach ($records as $record) {
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
                                'tanggal_mulai' => \Illuminate\Support\Carbon::parse($record->tanggal_mulai)->format('d F Y'),
                                'tanggal_selesai' => \Illuminate\Support\Carbon::parse($record->tanggal_selesai)->format('d F Y'),
                                'bidang_diminati' => $record->bidang_diminati,
                                'qr_code_path' => Storage::disk('public')->path($qrCodePath),
                                'tanggal_verifikasi' => now()->format('d F Y'),
                                'verified_by' => Auth::user()->name,
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
                            \Log::error("Failed to auto-verify report for ID {$record->id}: " . $e->getMessage());
                        }
                    }

                    Notification::make()
                        ->title("Berhasil Memproses {$count} Laporan")
                        ->success()
                        ->send();
                }),
        ];
    }
}
