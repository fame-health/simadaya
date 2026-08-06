@php
    $steps = $this->getStepsData();
    $activeStep = collect($steps)->firstWhere('active', true);
    $activeStepIndex = collect($steps)->search(fn($step) => $step['active']);
    $completedSteps = collect($steps)->where('completed', true);
    $totalSteps = count($steps);
    $progress = $totalSteps > 0 ? round(($completedSteps->count() / $totalSteps) * 100) : 0;

    // Get additional data
    $user = Auth::user();
    $mahasiswa = $user ? \App\Models\Mahasiswa::where('user_id', $user->id)->first() : null;
    $pengajuan = $mahasiswa ? \App\Models\PengajuanMagang::where('mahasiswa_id', $mahasiswa->id)->latest()->first() : null;
    $penilaian = $mahasiswa ? \App\Models\Penilaian::where('mahasiswa_id', $mahasiswa->id)->first() : null;

    // --- LOGIKA WARNA KONTEN KANAN (FIXED) ---
    $currentColor = $activeStep['color'] ?? 'primary';

    // Tema Konten Kanan
    $themes = [
        'primary' => [ 'badge_bg' => 'bg-primary-50', 'badge_text' => 'text-primary-600', 'ping_bg' => 'bg-primary-400', 'dot_bg' => 'bg-primary-500', 'alert_bg' => 'bg-primary-50', 'alert_border' => 'border-primary-200', 'alert_icon_bg' => 'bg-white', 'alert_icon_border' => 'border-primary-100', 'alert_icon_text' => 'text-primary-500', 'alert_title' => 'text-primary-700' ],
        'danger'  => [ 'badge_bg' => 'bg-red-50', 'badge_text' => 'text-red-600', 'ping_bg' => 'bg-red-400', 'dot_bg' => 'bg-red-500', 'alert_bg' => 'bg-red-50', 'alert_border' => 'border-red-200', 'alert_icon_bg' => 'bg-white', 'alert_icon_border' => 'border-red-100', 'alert_icon_text' => 'text-red-500', 'alert_title' => 'text-red-700' ],
        'success' => [ 'badge_bg' => 'bg-green-50', 'badge_text' => 'text-green-600', 'ping_bg' => 'bg-green-400', 'dot_bg' => 'bg-green-500', 'alert_bg' => 'bg-green-50', 'alert_border' => 'border-green-200', 'alert_icon_bg' => 'bg-white', 'alert_icon_border' => 'border-green-100', 'alert_icon_text' => 'text-green-500', 'alert_title' => 'text-green-700' ],
        'warning' => [ 'badge_bg' => 'bg-orange-50', 'badge_text' => 'text-orange-600', 'ping_bg' => 'bg-orange-400', 'dot_bg' => 'bg-orange-500', 'alert_bg' => 'bg-orange-50', 'alert_border' => 'border-orange-200', 'alert_icon_bg' => 'bg-white', 'alert_icon_border' => 'border-orange-100', 'alert_icon_text' => 'text-orange-500', 'alert_title' => 'text-orange-700' ],
        'info'    => [ 'badge_bg' => 'bg-sky-50', 'badge_text' => 'text-sky-600', 'ping_bg' => 'bg-sky-400', 'dot_bg' => 'bg-sky-500', 'alert_bg' => 'bg-sky-50', 'alert_border' => 'border-sky-200', 'alert_icon_bg' => 'bg-white', 'alert_icon_border' => 'border-sky-100', 'alert_icon_text' => 'text-sky-500', 'alert_title' => 'text-sky-700' ],
    ];

    $theme = $themes[$currentColor] ?? $themes['primary'];
@endphp

<x-filament-widgets::widget class="fi-account-widget">
    <style>
        /* Sembunyikan judul "Dashboard" bawaan Filament untuk Mahasiswa */
        .fi-header-heading,
        header.fi-header > h1 {
            display: none !important;
        }
        /* Kurangi ruang kosong di bagian atas */
        header.fi-header {
            padding-bottom: 0 !important;
            margin-bottom: 0 !important;
        }
        main.fi-main,
        .fi-main-ctn {
            padding-top: 0.5rem !important;
        }
    </style>
    <x-filament::section class="p-0 overflow-hidden ring-1 ring-gray-950/5 dark:ring-white/10">
        <div class="flex flex-col md:flex-row min-h-[350px]">

            {{-- ================================================= --}}
            {{-- KOLOM KIRI (SIDEBAR) --}}
            {{-- ================================================= --}}
            <div class="w-full md:w-5/12 border-b md:border-b-0 md:border-r border-gray-200 dark:border-white/10 bg-gray-50/50 dark:bg-gray-900/50 flex flex-col">

                {{-- Header Sidebar --}}
                <div class="p-4 border-b border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-primary-50 dark:bg-primary-900/20 rounded-lg relative overflow-hidden">
                                <x-heroicon-m-clipboard-document-list class="w-4 h-4 text-primary-600 dark:text-primary-400 relative z-10" />
                            </div>
                            <h3 class="text-sm font-bold text-gray-950 dark:text-white">Progress Magang</h3>
                        </div>
                        <span class="text-xs font-bold text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20 px-2 py-0.5 rounded-full">
                            {{ $progress }}%
                        </span>
                    </div>
                    <div class="h-1.5 w-full bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                        <div class="h-full bg-primary-600 dark:bg-primary-500 transition-all duration-1000 ease-out" style="width: {{ $progress }}%"></div>
                    </div>
                </div>

                {{-- List Steps --}}
                <div class="flex-1 overflow-y-auto custom-scrollbar p-3 space-y-1">
                    @foreach($steps as $index => $step)
                        @php
                            $isActive = $step['active'];
                            $isCompleted = $step['completed'];
                            $rawColor = $step['color'] ?? 'primary';

                            $style = [
                                'primary' => ['container' => 'border-primary-200', 'spinner' => 'border-primary-400/50', 'bg' => 'bg-primary-100', 'number' => 'text-primary-700', 'title' => 'text-primary-700', 'status' => 'text-primary-600', 'dot' => 'bg-primary-500', 'chevron' => 'text-primary-400', 'label' => 'Aktif'],
                                'danger'  => ['container' => 'border-red-200',     'spinner' => 'border-red-400/50',     'bg' => 'bg-red-100',     'number' => 'text-red-700',     'title' => 'text-red-700',     'status' => 'text-red-600',     'dot' => 'bg-red-500',     'chevron' => 'text-red-400',     'label' => 'Ditolak'],
                                'success' => ['container' => 'border-green-200',   'spinner' => 'border-green-400/50',   'bg' => 'bg-green-100',   'number' => 'text-green-700',   'title' => 'text-green-700',   'status' => 'text-green-600',   'dot' => 'bg-green-500',   'chevron' => 'text-green-400',   'label' => 'Selesai'],
                                'warning' => ['container' => 'border-orange-200',  'spinner' => 'border-orange-400/50',  'bg' => 'bg-orange-100',  'number' => 'text-orange-700',  'title' => 'text-orange-700',  'status' => 'text-orange-600',  'dot' => 'bg-orange-500',  'chevron' => 'text-orange-400',  'label' => 'Proses'],
                                'info'    => ['container' => 'border-sky-200',     'spinner' => 'border-sky-400/50',     'bg' => 'bg-sky-100',     'number' => 'text-sky-700',     'title' => 'text-sky-700',     'status' => 'text-sky-600',     'dot' => 'bg-sky-500',     'chevron' => 'text-sky-400',     'label' => 'Tinjau'],
                                'gray'    => ['container' => 'border-gray-200',    'spinner' => 'border-gray-400/50',    'bg' => 'bg-gray-100',    'number' => 'text-gray-700',    'title' => 'text-gray-700',    'status' => 'text-gray-600',    'dot' => 'bg-gray-500',    'chevron' => 'text-gray-400',    'label' => 'Menunggu'],
                            ];

                            $s = $style[$rawColor] ?? $style['primary'];
                        @endphp

                        <div class="group flex items-center gap-3 p-3 rounded-lg transition-all duration-300 border relative overflow-hidden
                            {{ $isActive
                                ? 'bg-white dark:bg-white/5 shadow-sm translate-x-1 ' . $s['container'] . ' dark:border-white/10'
                                : 'bg-transparent border-transparent hover:bg-gray-100 dark:hover:bg-white/5 text-gray-500'
                            }}">

                            {{-- LINGKARAN NOMOR --}}
                            <div class="flex-shrink-0 relative flex items-center justify-center w-8 h-8">
                                @if($isCompleted)
                                    <div class="w-6 h-6 rounded-full bg-success-100 text-success-600 dark:bg-success-500/20 dark:text-success-400 flex items-center justify-center">
                                        <x-heroicon-m-check class="w-3.5 h-3.5" />
                                    </div>
                                @elseif($isActive)
                                    <div class="absolute inset-0 w-8 h-8 border-2 border-dashed {{ $s['spinner'] }} rounded-full animate-[spin_6s_linear_infinite]"></div>
                                    <div class="relative w-6 h-6 rounded-full {{ $s['bg'] }} flex items-center justify-center shadow-sm z-10">
                                        <span class="text-gray-900 dark:text-gray-100 font-bold text-xs">{{ $index + 1 }}</span>
                                    </div>
                                    @else
    @php
        // ambil status step (lowercase untuk match aman)
        $stepStatus = strtolower($step['status'] ?? '');

        // tentukan kelas warna untuk lingkaran nomor
        $colorClass = match ($stepStatus) {
            'ditolak', 'ditolak' => 'bg-red-500 text-white border-red-600',            // merah
            'sedang diproses', 'pending', 'proses' => 'bg-amber-400 text-black border-amber-500', // kuning/orange
            'selesai', 'terima', 'diterima', 'sukses' => 'bg-green-500 text-white border-green-600', // hijau
            default => 'bg-gray-50 text-gray-500 border-gray-200 dark:border-gray-700 dark:bg-gray-800',
        };

        // optional: teks status singkat untuk tooltip / aria
        $statusLabel = $step['status'] ?? ($isCompleted ? 'Selesai' : ($isActive ? 'Aktif' : 'Menunggu'));
    @endphp

    <div
        class="w-6 h-6 rounded-full flex items-center justify-center font-medium text-xs transition-colors {{ $colorClass }}"
        title="{{ $statusLabel }}"
        aria-label="Step {{ $index + 1 }} - {{ $statusLabel }}"
    >
        {{ $index + 1 }}
    </div>
@endif


                            </div>

                            <div class="flex-1 z-10">
                                <h4 class="text-xs font-semibold {{ $isActive ? $s['title'] . ' dark:text-gray-200' : 'text-gray-700 dark:text-gray-400' }}">
                                    {{ $step['title'] }}
                                </h4>

                                @if($isActive)
                                    <p class="text-[10px] font-medium flex items-center gap-1 mt-0.5 {{ $s['status'] }} dark:text-gray-400">
                                        <span class="w-1.5 h-1.5 rounded-full animate-pulse {{ $s['dot'] }}"></span>
                                        {{ $step['status'] ?? $s['label'] }}
                                    </p>
                                @endif
                            </div>

                            @if($isActive)
                                <x-heroicon-m-chevron-right class="w-3.5 h-3.5 {{ $s['chevron'] }}" />
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ================================================= --}}
            {{-- KOLOM KANAN (DETAIL CONTENT) --}}
            {{-- ================================================= --}}
            <div class="w-full md:w-7/12 bg-white dark:bg-gray-900 flex flex-col justify-center relative p-6 md:p-8 overflow-hidden">
                <div class="absolute -top-8 -right-8 p-4 opacity-[0.03] dark:opacity-[0.05] pointer-events-none">
                    <div class="animate-[spin_60s_linear_infinite]">
                        <x-heroicon-o-cog-6-tooth class="w-64 h-64 text-gray-900 dark:text-white" />
                    </div>
                </div>

                @if($activeStep)
                    <div class="max-w-md mx-auto w-full relative z-10">
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full {{ $theme['badge_bg'] }} {{ $theme['badge_text'] }} text-[10px] font-bold mb-3 tracking-wide uppercase shadow-sm">
                            <span class="relative flex h-1.5 w-1.5">
                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $theme['ping_bg'] }} opacity-75"></span>
                              <span class="relative inline-flex rounded-full h-1.5 w-1.5 {{ $theme['dot_bg'] }}"></span>
                            </span>

                        </div>

                        <h2 class="text-xl md:text-2xl font-bold text-gray-950 dark:text-white mb-2 leading-tight">
                            {{ $activeStep['title'] }}
                        </h2>

                        {{-- Tombol Absensi di Atas (Hanya muncul jika sudah Diterima) --}}
                        @if($pengajuan && $pengajuan->status === \App\Models\PengajuanMagang::STATUS_DITERIMA)
                            <div class="mb-5">
                                <x-filament::button
                                    tag="a"
                                    href="{{ \App\Filament\Pages\ScanAttendance::getUrl() }}"
                                    icon="heroicon-m-qr-code"
                                    color="success"
                                    size="lg"
                                    class="w-full shadow-lg shadow-green-500/20 hover:scale-[1.01] transition-all"
                                >
                                    Absensi Sekarang
                                </x-filament::button>
                                <div class="mt-2 flex items-center justify-center gap-2">
                                    <span class="relative flex h-2 w-2">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                                    </span>
                                    <p class="text-[10px] font-bold text-green-600 uppercase tracking-widest">Sistem Absensi Aktif</p>
                                </div>
                            </div>
                        @endif

                        <div class="prose prose-sm dark:prose-invert text-gray-600 dark:text-gray-400 mb-5 leading-relaxed">
                            <p>{{ $activeStep['description'] }}</p>
                        </div>

                                               {{-- Ucapan Selamat untuk Pengajuan Diterima (Step 3) --}}
                        @if($pengajuan && $pengajuan->status_approval === 'diterima' && ($activeStepIndex + 1) == 3)
                            <div class="relative rounded-xl bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border-2 border-green-200 dark:border-green-700 p-5 mb-5 shadow-lg overflow-hidden">
                                {{-- Background Decoration --}}
                                <div class="absolute top-0 right-0 w-32 h-32 bg-green-400/10 rounded-full blur-3xl -mr-10 -mt-10"></div>
                                <div class="absolute bottom-0 left-0 w-24 h-24 bg-emerald-400/10 rounded-full blur-2xl -ml-8 -mb-8"></div>

                                <div class="relative z-10">
                                    {{-- Icon & Badge --}}
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center gap-2">
                                            <div class="p-2 bg-green-100 dark:bg-green-800/30 rounded-lg animate-bounce">
                                                <x-heroicon-s-check-badge class="w-6 h-6 text-green-600 dark:text-green-400" />
                                            </div>
                                            <span class="text-xs font-bold text-green-700 dark:text-green-300 uppercase tracking-wide">Pengajuan Diterima</span>
                                        </div>
                                        <span class="px-2.5 py-1 bg-green-600 dark:bg-green-500 text-white text-xs font-bold rounded-full shadow-sm">
                                            ✓ Approved
                                        </span>
                                    </div>

                                    {{-- Congratulations Message --}}
                                    <h4 class="text-lg font-bold text-green-800 dark:text-green-200 mb-2">
                                        🎉 Selamat! Pengajuan Magang Anda Telah Diterima
                                    </h4>
                                    <p class="text-sm text-green-700 dark:text-green-300 mb-4 leading-relaxed">
                                        Persiapkan diri Anda untuk memulai pengalaman magang yang berharga. Pastikan semua dokumen dan kebutuhan sudah siap.
                                    </p>

                                    {{-- Info Cards Grid --}}
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                                        {{-- Tanggal Mulai --}}
                                        <div class="bg-white/80 dark:bg-gray-800/50 rounded-lg p-3 border border-green-200 dark:border-green-700">
                                            <div class="flex items-center gap-2 mb-1">
                                                <x-heroicon-m-calendar class="w-4 h-4 text-green-600 dark:text-green-400" />
                                                <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Mulai Magang</span>
                                            </div>
                                            <p class="text-base font-bold text-green-700 dark:text-green-300">
                                                {{ $pengajuan->tanggal_mulai ? $pengajuan->tanggal_mulai->format('d M Y') : '-' }}
                                            </p>
                                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">
                                                {{ $pengajuan->tanggal_mulai ? $pengajuan->tanggal_mulai->locale('id')->isoFormat('dddd') : '-' }}
                                            </p>
                                        </div>

                                        {{-- Durasi Magang --}}
                                        <div class="bg-white/80 dark:bg-gray-800/50 rounded-lg p-3 border border-green-200 dark:border-green-700">
                                            <div class="flex items-center gap-2 mb-1">
                                                <x-heroicon-m-clock class="w-4 h-4 text-green-600 dark:text-green-400" />
                                                <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Durasi</span>
                                            </div>
                                            <p class="text-base font-bold text-green-700 dark:text-green-300">
                                                @if($pengajuan->tanggal_mulai && $pengajuan->tanggal_selesai)
                                                    {{ $pengajuan->tanggal_mulai->diffInDays($pengajuan->tanggal_selesai) }} Hari
                                                @else
                                                    -
                                                @endif
                                            </p>
                                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">
                                                s/d {{ $pengajuan->tanggal_selesai ? $pengajuan->tanggal_selesai->format('d M Y') : '-' }}
                                            </p>
                                        </div>
                                    </div>
                                    {{-- Surat Balasan / Surat Penerimaan Resmi (Otomatis dari Admin) --}}
@if($pengajuan->surat_balasan)
    <div class="bg-gradient-to-r from-green-100 to-emerald-100 dark:from-green-900/40 dark:to-emerald-900/40 rounded-xl p-6 border-2 border-green-300 dark:border-green-700 shadow-lg">
        <div class="flex items-start gap-4">
            <div class="p-3 bg-white dark:bg-gray-800 rounded-xl shadow-md">
                <x-heroicon-o-check-badge class="w-8 h-8 text-green-600 dark:text-green-400" />
            </div>
            <div class="flex-1">
                <h5 class="text-lg font-bold text-green-800 dark:text-green-100 mb-2">
                    Selamat! Pengajuan Magang Anda <span class="text-green-600">Diterima</span>
                </h5>
                <p class="text-sm text-green-700 dark:text-green-300 mb-4 leading-relaxed">
                    Surat balasan resmi telah diterbitkan. Silakan unduh surat berikut untuk diserahkan ke tempat magang sebagai bukti penerimaan.
                </p>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ asset('storage/' . $pengajuan->surat_balasan) }}"
                       target="_blank"
                       class="inline-flex items-center gap-2.5 px-5 py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-semibold text-sm rounded-lg shadow-md hover:shadow-xl transition-all duration-300 hover:scale-105">
                        <x-heroicon-m-arrow-down-tray class="w-5 h-5" />
                        Unduh Surat Balasan Resmi
                    </a>

                    <span class="text-xs text-green-600 dark:text-green-400 font-medium">
                        <x-heroicon-o-shield-check class="inline w-4 h-4" /> Dilengkapi QR Code Verifikasi
                    </span>
                </div>
            </div>
        </div>
    </div>
@endif


                                    {{-- Tips Box --}}
                                    <div class="mt-4 p-3 bg-green-50/50 dark:bg-green-900/10 rounded-lg border border-green-200/50 dark:border-green-700/50">
                                        <div class="flex items-start gap-2">
                                            <x-heroicon-m-light-bulb class="w-4 h-4 text-green-600 dark:text-green-400 mt-0.5 flex-shrink-0" />
                                            <div>
                                                <p class="text-xs font-semibold text-green-800 dark:text-green-200 mb-1">Tips Persiapan:</p>
                                                <ul class="text-xs text-green-700 dark:text-green-300 space-y-1 list-disc list-inside">
                                                    <li>Cetak dan bawa surat penerimaan pada hari pertama</li>
                                                    <li>Siapkan dokumen identitas dan persyaratan lainnya</li>
                                                    <li>Catat jadwal dan kontak pembimbing magang</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif


                        @if($activeStep['keterangan'])
                            <div class="relative rounded-lg {{ $theme['alert_bg'] }} border {{ $theme['alert_border'] }} p-3 mb-5 shadow-sm overflow-hidden">
                                <div class="absolute top-0 right-0 w-12 h-12 rounded-full blur-xl -mr-2 -mt-2 pointer-events-none opacity-10 {{ str_replace('bg-', 'bg-', $theme['dot_bg']) }}"></div>
                                <div class="flex items-start gap-3 relative z-10">
                                    <div class="shrink-0 w-8 h-8 rounded-md {{ $theme['alert_icon_bg'] }} border {{ $theme['alert_icon_border'] }} flex items-center justify-center shadow-sm mt-0.5">
                                        @if($currentColor === 'danger')
                                            <x-heroicon-m-x-circle class="w-4 h-4 {{ $theme['alert_icon_text'] }}" />
                                        @elseif($currentColor === 'success')
                                            <x-heroicon-m-check-circle class="w-4 h-4 {{ $theme['alert_icon_text'] }}" />
                                        @elseif($currentColor === 'info')
                                            <x-heroicon-m-information-circle class="w-4 h-4 {{ $theme['alert_icon_text'] }}" />
                                        @else
                                            <x-heroicon-m-bell-alert class="w-4 h-4 {{ $theme['alert_icon_text'] }} animate-[wiggle_1s_ease-in-out_infinite]" />
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <h5 class="text-[10px] font-bold {{ $theme['alert_title'] }} uppercase tracking-wide mb-0.5">
                                            @if($currentColor === 'danger') Perbaiki Segera
                                            @elseif($currentColor === 'success') Selamat
                                            @else Perhatian @endif
                                        </h5>
                                        <p class="text-xs text-gray-600 dark:text-gray-300 leading-snug">
                                            {{ $activeStep['keterangan'] }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="flex flex-col gap-2">
                            <x-filament::button
                                tag="a"
                                href="{{ $activeStep['url'] }}"
                                size="lg"
                                color="{{ $currentColor }}"
                                class="w-full shadow-lg shadow-{{ $currentColor }}-500/10 hover:scale-[1.01] transition-transform duration-300 group"
                            >
                                <span class="flex items-center justify-center gap-2 text-sm">
                                    {{ $activeStep['button_text'] }}
                                    <x-heroicon-m-arrow-right class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                                </span>
                            </x-filament::button>
                            <p class="text-center text-[10px] text-gray-400 mt-1">
                                Proses tidak dapat dibatalkan.
                            </p>
                        </div>
                    </div>
                @else
                    <div class="text-center max-w-xs mx-auto relative z-10">
                        <div class="inline-flex p-4 bg-success-50 dark:bg-success-900/20 rounded-full mb-4 ring-1 ring-success-100 dark:ring-success-500/30 animate-[bounce_3s_infinite]">
                            <x-heroicon-s-check-badge class="w-10 h-10 text-success-600 dark:text-success-400" />
                        </div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-1">Sempurna!</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            Seluruh kegiatan magang selesai.
                        </p>
                    </div>
                @endif
            </div>
        </div>

        {{-- ================================================= --}}
        {{-- GRID NILAI & DOKUMEN (DENGAN STEP INDICATOR) --}}
        {{-- ================================================= --}}
        @if($penilaian || $pengajuan)
        @php
            // Definisi warna card menggunakan theme dari progress step
            $cardColors = [
                'nilai' => [
                    'bg' => 'bg-primary-50 dark:bg-primary-900/20',
                    'icon' => 'text-primary-600 dark:text-primary-400',
                    'text' => 'text-primary-600 dark:text-primary-400',
                    'hover' => 'hover:text-primary-700'
                ],
                'surat' => [
                    'bg' => 'bg-info-50 dark:bg-info-900/20',
                    'icon' => 'text-info-600 dark:text-info-400',
                    'text' => 'text-info-600 dark:text-info-400',
                    'hover' => 'hover:text-info-700'
                ],
                'ktm' => [
                    'bg' => 'bg-warning-50 dark:bg-warning-900/20',
                    'icon' => 'text-warning-600 dark:text-warning-400',
                    'text' => 'text-warning-600 dark:text-warning-400',
                    'hover' => 'hover:text-warning-700'
                ],
                'laporan' => [
                    'bg' => 'bg-success-50 dark:bg-success-900/20',
                    'icon' => 'text-success-600 dark:text-success-400',
                    'text' => 'text-success-600 dark:text-success-400',
                    'hover' => 'hover:text-success-700'
                ],
                'sertifikat' => [
                    'bg' => 'bg-danger-50 dark:bg-danger-900/20',
                    'icon' => 'text-danger-600 dark:text-danger-400',
                    'text' => 'text-danger-600 dark:text-danger-400',
                    'hover' => 'hover:text-danger-700'
                ],
                'periode' => [
                    'bg' => 'bg-sky-50 dark:bg-sky-900/20',
                    'icon' => 'text-sky-600 dark:text-sky-400',
                    'text' => 'text-sky-600 dark:text-sky-400',
                    'hover' => 'hover:text-sky-700'
                ],
            ];

            $badgeSuccess = 'bg-success-50 dark:bg-success-900/20 text-success-600 dark:text-success-400';
        @endphp

        <div class="border-t border-gray-200 dark:border-white/10 bg-gradient-to-br from-gray-50 to-white dark:from-gray-900 dark:to-gray-800 p-6">
            <div class="max-w-6xl mx-auto">
                {{-- Header dengan Step Indicator --}}
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <x-heroicon-m-document-chart-bar class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                        <h3 class="text-base font-bold text-gray-950 dark:text-white">Nilai & Dokumen</h3>
                    </div>

                    {{-- Step Progress Indicator --}}
                    @if($activeStep)
                    <div class="flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Step</span>
                            <div class="flex items-center gap-1">
                                <span class="text-sm font-bold text-primary-600 dark:text-primary-400">{{ $activeStepIndex + 1 }}</span>
                                <span class="text-xs text-gray-400">/</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $totalSteps }}</span>
                            </div>
                        </div>
                        <div class="h-4 w-px bg-gray-200 dark:bg-gray-700"></div>
                        <div class="flex items-center gap-1.5">
                            <div class="w-1.5 h-1.5 rounded-full {{ $theme['dot_bg'] }} animate-pulse"></div>
                            <span class="text-xs font-medium {{ $theme['badge_text'] }}">{{ $activeStep['status'] ?? 'Aktif' }}</span>
                        </div>
                    </div>
                    @else
                    <div class="flex items-center gap-2 px-3 py-1.5 bg-success-50 dark:bg-success-900/20 rounded-lg border border-success-200 dark:border-success-700 shadow-sm">
                        <x-heroicon-m-check-circle class="w-4 h-4 text-success-600 dark:text-success-400" />
                        <span class="text-xs font-bold text-success-600 dark:text-success-400">Semua Step Selesai</span>
                    </div>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    {{-- Card Nilai --}}
                    @if($penilaian && $penilaian->nilai)
                    <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:shadow-lg transition-all hover:scale-[1.02] duration-300">
                        <div class="flex items-start justify-between mb-3">
                            <div class="p-2 {{ $cardColors['nilai']['bg'] }} rounded-lg">
                                <x-heroicon-m-academic-cap class="w-5 h-5 {{ $cardColors['nilai']['icon'] }}" />
                            </div>
                            <span class="text-2xl font-bold {{ $cardColors['nilai']['text'] }}">{{ $penilaian->nilai }}</span>
                        </div>
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Nilai Akhir</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Penilaian dari pembimbing</p>
                        @if($penilaian->catatan)
                        <div class="mt-3 p-2 bg-gray-50 dark:bg-gray-800 rounded text-xs text-gray-600 dark:text-gray-300">
                            <span class="font-medium">Catatan:</span> {{ Str::limit($penilaian->catatan, 50) }}
                        </div>
                        @endif
                    </div>
                    @endif

                    {{-- Card Sertifikat --}}
                    @if($pengajuan && $pengajuan->sertifikat)
                    <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:shadow-lg transition-all hover:scale-[1.02] duration-300">
                        <div class="flex items-start justify-between mb-3">
                            <div class="p-2 {{ $cardColors['sertifikat']['bg'] }} rounded-lg">
                                <x-heroicon-m-trophy class="w-5 h-5 {{ $cardColors['sertifikat']['icon'] }}" />
                            </div>
                            <span class="text-xs font-medium {{ $badgeSuccess }} px-2 py-0.5 rounded-full">
                                Tersedia
                            </span>
                        </div>
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Sertifikat</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Sertifikat kelulusan magang</p>
                        <a href="{{ asset('storage/' . $pengajuan->sertifikat) }}" target="_blank" class="inline-flex items-center gap-1 text-xs font-medium {{ $cardColors['sertifikat']['text'] }} {{ $cardColors['sertifikat']['hover'] }}">
                            <x-heroicon-m-arrow-down-tray class="w-3.5 h-3.5" />
                            Unduh Sertifikat
                        </a>
                    </div>
                    @endif

                    {{-- Card Laporan Akhir --}}
                    @if($pengajuan && $pengajuan->final_laporan)
                    <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:shadow-lg transition-all hover:scale-[1.02] duration-300">
                        <div class="flex items-start justify-between mb-3">
                            <div class="p-2 {{ $cardColors['laporan']['bg'] }} rounded-lg">
                                <x-heroicon-m-document-check class="w-5 h-5 {{ $cardColors['laporan']['icon'] }}" />
                            </div>
                            <span class="text-xs font-medium {{ $badgeSuccess }} px-2 py-0.5 rounded-full">
                                Tersedia
                            </span>
                        </div>
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Laporan Akhir</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Laporan magang final</p>
                        <a href="{{ asset('storage/' . $pengajuan->final_laporan) }}" target="_blank" class="inline-flex items-center gap-1 text-xs font-medium {{ $cardColors['laporan']['text'] }} {{ $cardColors['laporan']['hover'] }}">
                            <x-heroicon-m-arrow-down-tray class="w-3.5 h-3.5" />
                            Unduh Dokumen
                        </a>
                    </div>
                    @endif

                    {{-- Card Periode Magang --}}
                    @if($pengajuan)
                    <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:shadow-lg transition-all hover:scale-[1.02] duration-300">
                        <div class="flex items-start justify-between mb-3">
                            <div class="p-2 {{ $cardColors['periode']['bg'] }} rounded-lg">
                                <x-heroicon-m-calendar-days class="w-5 h-5 {{ $cardColors['periode']['icon'] }}" />
                            </div>
                        </div>
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Periode Magang</h4>
                        <div class="space-y-1">
                            <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400">
                                <span class="font-medium">Mulai:</span>
                                <span>{{ $pengajuan->tanggal_mulai?->format('d M Y') ?? '-' }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400">
                                <span class="font-medium">Selesai:</span>
                                <span>{{ $pengajuan->tanggal_selesai?->format('d M Y') ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- ================================================= --}}
        {{-- FOOTER CETAK LAPORAN --}}
        {{-- ================================================= --}}
        <div class="p-3 border-t border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900 sticky bottom-0 z-20">
            <div class="flex items-center justify-between gap-2 bg-gray-50 dark:bg-gray-800 rounded-lg p-2 border border-gray-100 dark:border-gray-700 hover:border-gray-300 transition-colors group">
                <div class="text-[10px] text-gray-500 dark:text-gray-400 pl-1 font-medium flex items-center gap-1.5">
                    <x-heroicon-o-printer class="w-3.5 h-3.5 group-hover:text-primary-500 transition-colors" />
                    Cetak Laporan
                </div>
                <div>
                    <livewire:mahasiswa-print :steps="$steps" />
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #e5e7eb; border-radius: 10px; }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #374151; }
</style>
