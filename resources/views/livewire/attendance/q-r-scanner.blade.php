<div class="w-full max-w-md mx-auto py-4">
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
        <div class="bg-primary-600 px-6 py-4 flex items-center justify-between">
            <h2 class="text-white font-bold text-lg">Absensi Magang</h2>
            <div class="w-2 h-2 rounded-full bg-red-500 animate-pulse shadow-[0_0_8px_rgba(239,68,68,1)]"></div>
        </div>

        <div class="p-6">
            @if ($status === 'scanning')
                <div class="flex flex-col items-center justify-center py-12">
                    <div class="w-16 h-16 border-4 border-primary-500 border-t-transparent rounded-full animate-spin mb-4"></div>
                    <p class="text-gray-600 font-medium">Memproses absensi...</p>
                </div>
            @elseif ($status === 'success')
                <div class="flex flex-col items-center text-center py-6">
                    <div class="w-20 h-20 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-10 h-10 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Absensi Berhasil!</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm mb-6">{{ $message }}</p>

                    <div class="grid grid-cols-2 gap-3 w-full mb-6 text-left">
                        <div class="rounded-xl bg-emerald-50 dark:bg-emerald-950 p-3">
                            <span class="block text-xs text-gray-500 dark:text-gray-400">Jam masuk</span>
                            <span class="font-bold text-gray-900 dark:text-white">{{ $checkInTime ?? '-' }}</span>
                        </div>
                        <div class="rounded-xl bg-blue-50 dark:bg-blue-950 p-3">
                            <span class="block text-xs text-gray-500 dark:text-gray-400">Jam pulang</span>
                            <span class="font-bold text-gray-900 dark:text-white">{{ $checkOutTime ?? 'Belum tercatat' }}</span>
                        </div>
                    </div>

                    @if ($hasCheckedInToday && ! $hasCheckedOutToday)
                        <button wire:click="startCheckout" wire:loading.attr="disabled"
                            class="w-full bg-primary-600 hover:bg-primary-700 text-white py-3 rounded-xl font-bold transition-transform active:scale-95 shadow-lg mb-3">
                            <span wire:loading.remove wire:target="startCheckout">Absen Pulang</span>
                            <span wire:loading wire:target="startCheckout">Menyiapkan kamera...</span>
                        </button>
                    @endif

                    <a href="{{ \App\Filament\Resources\AttendanceLogResource::getUrl('index') }}"
                       class="w-full bg-gray-900 dark:bg-white text-white dark:text-gray-900 py-3 rounded-xl font-bold transition-transform active:scale-95 shadow-lg">
                        Lihat Riwayat Absensi
                    </a>
                </div>
            @elseif ($status === 'error')
                <div class="flex flex-col items-center text-center py-6">
                    <div class="w-20 h-20 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-10 h-10 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Absensi Gagal</h3>
                    <p class="text-red-600 dark:text-red-400 text-sm mb-8 font-medium">{{ $message }}</p>
                    <button wire:click="resetScanner" wire:loading.attr="disabled"
                        class="w-full bg-primary-600 text-white py-3 rounded-xl font-bold transition-transform active:scale-95 shadow-lg">
                        Ulangi Scan
                    </button>
                </div>
            @elseif ($hasCheckedInToday && ! $hasCheckedOutToday && ! $isScanning)
                <div class="py-5 text-center">
                    <div class="w-16 h-16 mx-auto bg-emerald-100 dark:bg-emerald-900 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Kehadiran Sudah Tercatat</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm mb-5">Jam masuk Anda {{ $checkInTime ?? '-' }}. Scan QR kembali saat pulang untuk mencatat jam kepulangan.</p>
                    <button wire:click="startCheckout" wire:loading.attr="disabled"
                        class="w-full bg-primary-600 hover:bg-primary-700 text-white py-3 rounded-xl font-bold transition-transform active:scale-95 shadow-lg">
                        <span wire:loading.remove wire:target="startCheckout">Mulai Absensi Pulang</span>
                        <span wire:loading wire:target="startCheckout">Menyiapkan kamera...</span>
                    </button>
                </div>
            @elseif ($isScanning)
                <div class="mb-6 text-center">
                    <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">
                        Arahkan kamera ke QR Code yang ditampilkan pembimbing untuk mencatat absensi {{ $hasCheckedInToday ? 'kepulangan' : 'kehadiran' }}.
                    </p>
                </div>

                <div class="relative w-full aspect-square bg-black rounded-xl overflow-hidden border-2 border-gray-100 dark:border-gray-700 shadow-inner">
                    <div id="reader" class="w-full h-full"></div>
                    <div class="absolute inset-0 pointer-events-none flex items-center justify-center z-20">
                        <div class="w-64 h-64 border-2 border-primary-500/50 rounded-lg relative">
                            <div class="absolute -top-1 -left-1 w-6 h-6 border-t-4 border-l-4 border-primary-500"></div>
                            <div class="absolute -top-1 -right-1 w-6 h-6 border-t-4 border-r-4 border-primary-500"></div>
                            <div class="absolute -bottom-1 -left-1 w-6 h-6 border-b-4 border-l-4 border-primary-500"></div>
                            <div class="absolute -bottom-1 -right-1 w-6 h-6 border-b-4 border-r-4 border-primary-500"></div>
                        </div>
                    </div>
                    <div class="absolute top-0 left-0 w-full h-1 bg-primary-500/50 blur-[2px] z-30 animate-scan-line"></div>
                </div>
            @endif
        </div>
    </div>

    <div class="mt-6 text-center flex flex-col gap-2">
        <a href="{{ \App\Filament\Resources\AttendanceLogResource::getUrl('index') }}" class="text-xs font-bold text-primary-600 hover:text-primary-500 underline decoration-dotted">
            Tidak bisa hadir? Klik di sini untuk ajukan izin/sakit
        </a>
        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-none">SIMADAYA APP v2.1</span>
    </div>

    <style>
        @keyframes scanLine {
            from { transform: translateY(0); }
            to { transform: translateY(350px); }
        }
        .animate-scan-line { animation: scanLine 2.5s linear infinite; }
        #reader__status_span { display: none !important; }
        #reader__dashboard { padding: 10px !important; border: none !important; }
        video { border-radius: 12px !important; }
    </style>

    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        let attendanceQrScanner = null;
        let attendanceQrSubmitting = false;

        function startAttendanceScanner() {
            const reader = document.getElementById('reader');
            if (!reader || attendanceQrScanner || typeof Html5Qrcode === 'undefined') return;

            const scanner = new Html5Qrcode('reader');
            attendanceQrScanner = scanner;

            scanner.start(
                { facingMode: 'environment' },
                { fps: 20, qrbox: { width: 250, height: 250 }, aspectRatio: 1.0 },
                (decodedText) => {
                    if (attendanceQrSubmitting) return;
                    attendanceQrSubmitting = true;

                    scanner.stop().catch(() => {}).finally(() => {
                        if (attendanceQrScanner === scanner) attendanceQrScanner = null;
                        @this.call('processResult', decodedText).finally(() => {
                            attendanceQrSubmitting = false;
                        });
                    });
                }
            ).catch((error) => {
                console.error('Camera error:', error);
                if (attendanceQrScanner === scanner) attendanceQrScanner = null;
            });
        }

        document.addEventListener('livewire:initialized', () => {
            @if ($isScanning)
                startAttendanceScanner();
            @endif

            @this.on('scanner-start', () => setTimeout(startAttendanceScanner, 150));
            @this.on('scanner-reset', () => setTimeout(startAttendanceScanner, 150));
        });
    </script>
</div>
