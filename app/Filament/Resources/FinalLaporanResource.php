<?php

namespace App\Filament\Resources;
use App\Filament\Resources\FinalLaporanResource\Pages;
use App\Models\PengajuanMagang;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Actions\Action;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Blade;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;


class FinalLaporanResource extends Resource
{
    protected static ?string $model = PengajuanMagang::class;

    protected static ?string $navigationGroup = 'ALUR PELAKSANAAN PKL';

    public static function getNavigationSort(): ?int
    {
        return 6;
    }

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Laporan Akhir';
    protected static ?string $pluralModelLabel = 'Laporan Akhir';

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        if ($user->role === 'mahasiswa' && $user->mahasiswa) {
            $hasValidPengajuan = PengajuanMagang::where('mahasiswa_id', $user->mahasiswa->id)
                ->whereIn('status', [PengajuanMagang::STATUS_DITERIMA, PengajuanMagang::STATUS_SELESAI])
                ->exists();
            return $hasValidPengajuan;
        }

        return $user->role === 'admin';
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery()->with(['mahasiswa.user', 'pembimbing.user']);
        if (Auth::user()->role === 'mahasiswa' && Auth::user()->mahasiswa) {
            $query->where('mahasiswa_id', Auth::user()->mahasiswa->id)
                  ->whereIn('status', [PengajuanMagang::STATUS_DITERIMA, PengajuanMagang::STATUS_SELESAI]);
        } elseif (Auth::user()->role === 'admin') {
            $query->whereIn('status', [PengajuanMagang::STATUS_DITERIMA, PengajuanMagang::STATUS_SELESAI]);
        }
        return $query;
    }

    public static function form(Form $form): Form
    {
        $user = Auth::user();
        $isAdmin = $user->role === 'admin';
        $isMahasiswa = $user->role === 'mahasiswa';

        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->visible($isMahasiswa)
                    ->compact()
                    ->schema([
                        Forms\Components\Placeholder::make('status_info')
                            ->label('')
                            ->content(function ($record) {
                                if (!$record) return null;

                                if ($record->final_laporan && !$record->sertifikat) {
                                    return new HtmlString('
                                        <div style="display: flex; align-items: center; gap: 12px; padding: 12px; color: #92400e; background-color: #fef3c7; border: 1px solid #fde68a; border-radius: 8px; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);">
                                            <div style="flex-shrink: 0; padding: 4px; background-color: #fde68a; border-radius: 9999px; display: flex;">
                                                <svg style="width: 16px; height: 16px; color: #d97706;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            </div>
                                            <div style="display: flex; flex-direction: column; text-align: left;">
                                                <span style="font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.1em; opacity: 0.8;">Status Laporan</span>
                                                <span style="font-size: 14px; font-weight: 600;">Laporan Terkirim. Menunggu Verifikasi & Sertifikat.</span>
                                            </div>
                                        </div>
                                    ');
                                }

                                if ($record->sertifikat) {
                                    return new HtmlString('
                                        <div style="display: flex; align-items: center; gap: 12px; padding: 12px; color: #065f46; background-color: #d1fae5; border: 1px solid #a7f3d0; border-radius: 8px; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);">
                                            <div style="flex-shrink: 0; padding: 4px; background-color: #a7f3d0; border-radius: 9999px; display: flex;">
                                                <svg style="width: 16px; height: 16px; color: #059669;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            </div>
                                            <div style="display: flex; flex-direction: column; text-align: left;">
                                                <span style="font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.1em; opacity: 0.8;">Status Magang</span>
                                                <span style="font-size: 14px; font-weight: 600;">Selesai! Sertifikat sudah terbit.</span>
                                            </div>
                                        </div>
                                    ');
                                }

                                return new HtmlString('
                                    <div style="display: flex; align-items: center; gap: 12px; padding: 12px; color: #1e40af; background-color: #dbeafe; border: 1px solid #bfdbfe; border-radius: 8px; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);">
                                        <div style="flex-shrink: 0; padding: 4px; background-color: #bfdbfe; border-radius: 9999px; display: flex;">
                                            <svg style="width: 16px; height: 16px; color: #2563eb;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        </div>
                                        <div style="display: flex; flex-direction: column; text-align: left;">
                                            <span style="font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.1em; opacity: 0.8;">Instruksi</span>
                                            <span style="font-size: 14px; font-weight: 600;">Silakan unggah laporan akhir magang Anda (PDF).</span>
                                        </div>
                                    </div>
                                ');
                            }),
                    ]),

                Forms\Components\Section::make('Informasi Pengajuan')
                    ->schema([
                        Forms\Components\Select::make('mahasiswa_id')
                            ->relationship('mahasiswa', 'user_id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->user->name ?? 'Tanpa Nama')
                            ->label('Mahasiswa')
                            ->disabled()
                            ->required()
                            ->default(fn () => $isMahasiswa && $user->mahasiswa ? $user->mahasiswa->id : null)
                            ->options(function () {
                                return \App\Models\Mahasiswa::query()
                                    ->join('users', 'mahasiswa.user_id', '=', 'users.id')
                                    ->where('users.role', 'mahasiswa')
                                    ->pluck('users.name', 'mahasiswa.id')
                                    ->toArray();
                            }),
                        Forms\Components\Select::make('pembimbing_id')
                            ->relationship('pembimbing', 'user_id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->user->name ?? 'Tanpa Nama')
                            ->label('Pembimbing')
                            ->disabled()
                            ->visible($isAdmin),
                        Forms\Components\TextInput::make('bidang_diminati')
                            ->label('Bidang Diminati')
                            ->disabled(),
                        Forms\Components\TextInput::make('durasi_magang')
                            ->label('Durasi Magang')
                            ->suffix('minggu')
                            ->disabled(),
                        Forms\Components\TextInput::make('status')
                            ->label('Status Pengajuan')
                            ->disabled(),
                    ])
                    ->columns(3)
                    ->visible(!$isMahasiswa),

                Forms\Components\Section::make('Laporan Akhir')
                    ->description('Silakan unggah laporan akhir magang Anda.')
                    ->schema([
                        Forms\Components\FileUpload::make('final_laporan')
                            ->label('File Laporan Akhir')
                            ->directory('pengajuan-magang/laporan')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(2048) // 2MB
                            ->helperText('Format file wajib PDF dengan ukuran maksimal 2MB.')
                            ->visible($isMahasiswa || $isAdmin)
                            ->disabled(fn ($record) => !$record || !$record->isDiterima() || ($record->final_laporan && $isMahasiswa))
                            ->required(fn ($record) => $record instanceof PengajuanMagang && $isMahasiswa && $record->isDiterima() && !$record->final_laporan)
                            ->hintAction(
                                Forms\Components\Actions\Action::make('view_laporan')
                                    ->label('Lihat Laporan')
                                    ->icon('heroicon-o-eye')
                                    ->color('info')
                                    ->visible(fn ($record) => $record && $record->final_laporan)
                                    ->url(fn ($record) => asset('storage/' . $record->final_laporan))
                                    ->openUrlInNewTab()
                            ),
                    ])
                    ->visible($isMahasiswa || $isAdmin),

                Forms\Components\Section::make('Sertifikat Magang')
                    ->description('Bagian verifikasi dan unduh sertifikat resmi.')
                    ->schema([
                        Forms\Components\FileUpload::make('sertifikat')
                            ->label('File Sertifikat')
                            ->directory('pengajuan-magang/sertifikat')
                            ->acceptedFileTypes(['application/pdf'])
                            ->visible($isAdmin || ($isMahasiswa && fn ($record) => $record?->sertifikat))
                            ->disabled(true)
                            ->hintAction(
                                Forms\Components\Actions\Action::make('view_sertifikat')
                                    ->label('Lihat Sertifikat')
                                    ->icon('heroicon-o-document-check')
                                    ->color('success')
                                    ->visible(fn ($record) => $record && $record->sertifikat)
                                    ->url(fn ($record) => asset('storage/' . $record->sertifikat))
                                    ->openUrlInNewTab()
                            ),

                        Forms\Components\Placeholder::make('verification_action')
                            ->label('Aksi Admin')
                            ->visible($isAdmin)
                            ->content(function ($record) {
                                if (!$record || !$record->final_laporan || $record->sertifikat) {
                                    return new HtmlString('<span class="text-gray-500 italic">Sertifikat sudah diproses atau laporan belum tersedia.</span>');
                                }
                                return new HtmlString('
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-600 mb-4">Laporan sudah tersedia. Klik tombol di bawah untuk menerbitkan sertifikat resmi beserta QR Code.</p>
                                    </div>
                                ');
                            })
                            ->hintAction(
                                Forms\Components\Actions\Action::make('verify_sertifikat_form')
                                    ->label('Terbitkan Sertifikat Sekarang')
                                    ->icon('heroicon-o-check-badge')
                                    ->color('success')
                                    ->visible(fn ($record) => Auth::user()->role === 'admin' && $record && $record->final_laporan && !$record->sertifikat)
                                    ->requiresConfirmation()
                                    ->action(function ($record) {
                                        // Generate QR code for certificate
                                        $validationUrl = url('/validate-certificate/' . $record->id);
                                        $qrCode = new QrCode($validationUrl);
                                        $qrCode->setSize(120);
                                        $qrCode->setMargin(10);
                                        $writer = new PngWriter();
                                        $qrCodeImage = $writer->write($qrCode);
                                        $qrCodePath = 'pengajuan-magang/qr-codes/qr_sertifikat_' . $record->id . '.png';
                                        Storage::disk('public')->put($qrCodePath, $qrCodeImage->getString());

                                        // Fetch the active template for 'sertifikat'
                                        $template = \App\Models\TemplateSurat::where('jenis_surat', \App\Models\TemplateSurat::JENIS_SERTIFIKAT)
                                            ->where('is_active', true)
                                            ->first();

                                        if (!$template) {
                                            \Filament\Notifications\Notification::make()
                                                ->title('Template Sertifikat Tidak Ditemukan')
                                                ->body('Pastikan ada template sertifikat yang aktif di Manajemen Surat.')
                                                ->danger()
                                                ->send();
                                            return;
                                        }

                                        // Prepare data for the template
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

                                        // Render the template content
                                        $renderedContent = Blade::render($template->content_template, $pdfData);

                                        // Generate PDF for certificate
                                        $pdf = Pdf::loadHTML($renderedContent);
                                        $pdfPath = 'pengajuan-magang/sertifikat/sertifikat_' . $record->id . '.pdf';
                                        Storage::disk('public')->put($pdfPath, $pdf->output());

                                        $record->sertifikat = $pdfPath;
                                        $record->status = PengajuanMagang::STATUS_SELESAI;
                                        $record->save();

                                        \Filament\Notifications\Notification::make()
                                            ->title('Sertifikat Berhasil Diterbitkan')
                                            ->success()
                                            ->send();
                                    }),
                            ),
                    ])
                    ->visible(fn ($record) => $record instanceof PengajuanMagang && (Auth::user()->role === 'admin' || (Auth::user()->role === 'mahasiswa' && $record->sertifikat))),
            ]);
    }

    public static function table(Table $table): Table
    {
        $user = Auth::user();
        $isAdmin = $user->role === 'admin';
        $isMahasiswa = $user->role === 'mahasiswa';

        return $table
            ->heading(function () use ($user, $isMahasiswa) {
                if (!$isMahasiswa || !$user->mahasiswa) {
                    return null;
                }

                $pengajuan = PengajuanMagang::where('mahasiswa_id', $user->mahasiswa->id)
                    ->whereIn('status', [PengajuanMagang::STATUS_DITERIMA, PengajuanMagang::STATUS_SELESAI])
                    ->orderBy('created_at', 'desc')
                    ->first();

                if (!$pengajuan) {
                    return null;
                }

                $html = '';

                if ($pengajuan->isDiterima() && !$pengajuan->final_laporan) {
                    $html = '
                        <div style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
                                   border: 2px solid #f59e0b;
                                   border-radius: 12px;
                                   padding: 20px;
                                   margin: 16px 0;
                                   box-shadow: 0 4px 6px -1px rgba(245, 158, 11, 0.1);">
                            <div style="display: flex; align-items: center; margin-bottom: 12px;">
                                <div style="background: #f59e0b;
                                           color: white;
                                           border-radius: 50%;
                                           width: 32px;
                                           height: 32px;
                                           display: flex;
                                           align-items: center;
                                           justify-content: center;
                                           font-weight: bold;
                                           margin-right: 12px;">📋</div>
                                <h3 style="color: #92400e;
                                          font-size: 18px;
                                          font-weight: 700;
                                          margin: 0;">UPLOAD LAPORAN AKHIR</h3>
                            </div>
                            <div style="background: #fde68a;
                                       border-left: 4px solid #f59e0b;
                                       padding: 12px 16px;
                                       border-radius: 6px;">
                                <p style="color: #92400e;
                                         font-size: 16px;
                                         margin: 0;
                                         line-height: 1.5;">
                                    📄 Pengajuan magang Anda telah diterima. Silakan upload laporan akhir Anda.
                                </p>
                            </div>
                        </div>';
                } elseif ($pengajuan->isDiterima() && $pengajuan->final_laporan && !$pengajuan->sertifikat) {
                    $html = '
                        <div style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
                                   border: 2px solid #f59e0b;
                                   border-radius: 12px;
                                   padding: 20px;
                                   margin: 16px 0;
                                   box-shadow: 0 4px 6px -1px rgba(245, 158, 11, 0.1);">
                            <div style="display: flex; align-items: center; margin-bottom: 12px;">
                                <div style="background: #f59e0b;
                                           color: white;
                                           border-radius: 50%;
                                           width: 32px;
                                           height: 32px;
                                           display: flex;
                                           align-items: center;
                                           justify-content: center;
                                           font-weight: bold;
                                           margin-right: 12px;">⏳</div>
                                <h3 style="color: #92400e;
                                          font-size: 18px;
                                          font-weight: 700;
                                          margin: 0;">MENUNGGU SERTIFIKAT</h3>
                            </div>
                            <div style="background: #fde68a;
                                       border-left: 4px solid #f59e0b;
                                       padding: 12px 16px;
                                       border-radius: 6px;">
                                <p style="color: #92400e;
                                         font-size: 16px;
                                         margin: 0;
                                         line-height: 1.5;">
                                    📋 Laporan akhir Anda telah diupload. Menunggu admin untuk mengupload sertifikat.
                                </p>
                            </div>
                        </div>';
                } elseif ($pengajuan->status === PengajuanMagang::STATUS_SELESAI && $pengajuan->sertifikat) {
                    $html = '
                        <div style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
                                   border: 2px solid #16a34a;
                                   border-radius: 12px;
                                   padding: 20px;
                                   margin: 16px 0;
                                   box-shadow: 0 4px 6px -1px rgba(22, 163, 74, 0.1);">
                            <div style="display: flex; align-items: center; margin-bottom: 12px;">
                                <div style="background: #16a34a;
                                           color: white;
                                           border-radius: 50%;
                                           width: 32px;
                                           height: 32px;
                                           display: flex;
                                           align-items: center;
                                           justify-content: center;
                                           font-weight: bold;
                                           margin-right: 12px;">✅</div>
                                <h3 style="color: #15803d;
                                          font-size: 18px;
                                          font-weight: 700;
                                          margin: 0;">MAGANG SELESAI</h3>
                            </div>
                            <div style="background: #bbf7d0;
                                       border-left: 4px solid #16a34a;
                                       padding: 12px 16px;
                                       border-radius: 6px;">
                                <p style="color: #15803d;
                                         font-size: 16px;
                                         margin: 0;
                                         line-height: 1.5;">
                                    🎉 Magang Anda telah selesai! Sertifikat tersedia untuk diunduh.
                                </p>
                            </div>
                            <div style="margin-top: 12px; background: rgba(22, 163, 74, 0.1); border-radius: 6px; padding: 12px;">
                                <a href="' . asset('storage/' . $pengajuan->sertifikat) . '"
                                   target="_blank"
                                   style="background: #16a34a; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 14px; display: inline-flex; align-items: center; gap: 6px; transition: background-color 0.2s;"
                                   onmouseover="this.style.background=\'#059669\'"
                                   onmouseout="this.style.background=\'#16a34a\'">
                                    📥 Unduh Sertifikat
                                </a>
                            </div>
                        </div>';
                }

                return new HtmlString($html);
            })
            ->columns([
                Tables\Columns\TextColumn::make('mahasiswa.user.name')
                    ->label('Nama Mahasiswa')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('mahasiswa.nim')
                    ->label('NIM')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        PengajuanMagang::STATUS_DITERIMA => 'success',
                        PengajuanMagang::STATUS_SELESAI => 'info',
                    })
                    ->sortable(),
                Tables\Columns\IconColumn::make('final_laporan')
                    ->label('Laporan')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->sortable(),
                Tables\Columns\IconColumn::make('sertifikat')
                    ->label('Sertifikat')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-clock')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        PengajuanMagang::STATUS_DITERIMA => 'Diterima',
                        PengajuanMagang::STATUS_SELESAI => 'Selesai',
                    ])
                    ->visible($isAdmin),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Upload')
                    ->visible(fn ($record) => $isAdmin || ($isMahasiswa && $record->isDiterima() && !$record->final_laporan)),
                Action::make('view_laporan')
                    ->label('Lihat Laporan')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->visible(fn ($record) => $isAdmin && $record->final_laporan)
                    ->url(fn ($record) => asset('storage/' . $record->final_laporan))
                    ->openUrlInNewTab(),
                Action::make('download_sertifikat')
                    ->label('Unduh Sertifikat')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->visible(fn ($record) => $isMahasiswa && $record->status === PengajuanMagang::STATUS_SELESAI && $record->sertifikat)
                    ->url(fn ($record) => asset('storage/' . $record->sertifikat))
                    ->openUrlInNewTab(),
                Action::make('verify_sertifikat')
                    ->label('Verifikasi Sertifikat')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $isAdmin && $record->final_laporan && !$record->sertifikat)
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        // Generate QR code for certificate
                        $validationUrl = url('/validate-certificate/' . $record->id);
                        $qrCode = new QrCode($validationUrl); // Use constructor for endroid/qr-code < 4.0
                        $qrCode->setSize(120);
                        $qrCode->setMargin(10);
                        $writer = new PngWriter();
                        $qrCodeImage = $writer->write($qrCode);
                        $qrCodePath = 'pengajuan-magang/qr-codes/qr_sertifikat_' . $record->id . '.png';
                        Storage::disk('public')->put($qrCodePath, $qrCodeImage->getString());

                        // Fetch the active template for 'sertifikat'
                        $template = \App\Models\TemplateSurat::where('jenis_surat', \App\Models\TemplateSurat::JENIS_SERTIFIKAT)
                            ->where('is_active', true)
                            ->first();

                        if (!$template) {
                            throw new \Exception('No active template found for sertifikat');
                        }

                        // Prepare data for the template
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


                        // Render the template content
                        $renderedContent = Blade::render($template->content_template, $pdfData);

                        // Generate PDF for certificate
                        $pdf = Pdf::loadHTML($renderedContent);
                        $pdfPath = 'pengajuan-magang/sertifikat/sertifikat_' . $record->id . '.pdf';
                        Storage::disk('public')->put($pdfPath, $pdf->output());
                        $record->sertifikat = $pdfPath;
                        $record->status = PengajuanMagang::STATUS_SELESAI;
                        $record->save();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->visible($isAdmin)
                    ->requiresConfirmation(),
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        if (!$record instanceof PengajuanMagang) {
            return false;
        }

        $user = Auth::user();
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'mahasiswa' && $user->mahasiswa) {
            // Mahasiswa bisa akses halaman edit (view mereka) jika statusnya Diterima atau Selesai
            return $record->mahasiswa_id === $user->mahasiswa->id &&
                   in_array($record->status, [PengajuanMagang::STATUS_DITERIMA, PengajuanMagang::STATUS_SELESAI]);
        }
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        if (!$record instanceof PengajuanMagang) {
            return false;
        }

        return Auth::user()->role === 'admin';
    }

    public static function canView(Model $record): bool
    {
        if (!$record instanceof PengajuanMagang) {
            return false;
        }

        $user = Auth::user();
        if ($user->role === 'admin') {
            return true;
        }
        if ($user->role === 'mahasiswa' && $user->mahasiswa) {
            return $record->mahasiswa_id === $user->mahasiswa->id && in_array($record->status, [PengajuanMagang::STATUS_DITERIMA, PengajuanMagang::STATUS_SELESAI]);
        }
        return false;
    }

    public static function getPages(): array
    {
        $user = Auth::user();
        $pages = [
            'index' => Pages\ListFinalLaporans::route('/'),
            'edit' => Pages\EditFinalLaporan::route('/{record}/edit'),
        ];

        if ($user && $user->role === 'mahasiswa' && $user->mahasiswa) {
            $hasPendingOrRejected = PengajuanMagang::where('mahasiswa_id', $user->mahasiswa->id)
                ->whereIn('status', [PengajuanMagang::STATUS_PENDING, PengajuanMagang::STATUS_DITOLAK])
                ->exists();
            if ($hasPendingOrRejected) {
                \Filament\Notifications\Notification::make()
                    ->title('Pengajuan Magang Belum Diterima')
                    ->body('Resource Laporan Akhir hanya tersedia setelah pengajuan magang Anda diterima.')
                    ->warning()
                    ->send();
            }
        }

        return $pages;
    }

    public static function getNavigationBadge(): ?string
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user) return null;

        $query = static::getModel()::query();

        if ($user->isMahasiswa()) {
            $query->where('mahasiswa_id', $user->mahasiswa?->id)
                  ->whereIn('status', [\App\Models\PengajuanMagang::STATUS_DITERIMA, \App\Models\PengajuanMagang::STATUS_SELESAI]);
        } else {
            $query->whereIn('status', [\App\Models\PengajuanMagang::STATUS_DITERIMA, \App\Models\PengajuanMagang::STATUS_SELESAI]);
        }

        return (string) $query->count();
    }
}
