<div class="w-full max-w-md mx-auto py-4">
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="p-4 sm:p-5">
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
                <div class="mb-4 text-center">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Scan QR {{ $hasCheckedInToday ? 'Kepulangan' : 'Kehadiran' }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Arahkan kamera ke QR yang ditampilkan pembimbing.
                    </p>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        Izinkan kamera saat diminta. Chrome akan mengingat izin jika setelan situs diatur ke <strong>Izinkan</strong>.
                    </p>
                </div>

                <div class="relative mx-auto aspect-square w-full max-w-sm overflow-hidden rounded-xl bg-black ring-1 ring-gray-200 dark:ring-gray-700">
                    <div id="reader" class="w-full h-full"></div>
                    <div class="absolute inset-0 pointer-events-none flex items-center justify-center z-20">
                        <div class="h-56 w-56 rounded-lg border-2 border-primary-500/60 relative sm:h-64 sm:w-64">
                            <div class="absolute -top-1 -left-1 w-6 h-6 border-t-4 border-l-4 border-primary-500"></div>
                            <div class="absolute -top-1 -right-1 w-6 h-6 border-t-4 border-r-4 border-primary-500"></div>
                            <div class="absolute -bottom-1 -left-1 w-6 h-6 border-b-4 border-l-4 border-primary-500"></div>
                            <div class="absolute -bottom-1 -right-1 w-6 h-6 border-b-4 border-r-4 border-primary-500"></div>
                        </div>
                    </div>
                    <div class="absolute top-0 left-0 w-full h-1 bg-primary-500/50 blur-[2px] z-30 animate-scan-line"></div>
                </div>
                <p id="camera-feedback" role="status" aria-live="polite" class="mt-3 rounded-lg bg-gray-50 px-3 py-2 text-center text-sm text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                    Mengaktifkan kamera...
                </p>
                <button id="camera-retry" type="button" hidden
                    class="mt-3 w-full rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-700">
                    Coba nyalakan kamera lagi
                </button>
            @endif
        </div>
    </div>

    <div class="mt-4 text-center">
        <a href="{{ \App\Filament\Resources\AttendanceLogResource::getUrl('index') }}" class="text-sm font-medium text-primary-600 hover:text-primary-500 underline underline-offset-2">
            Ajukan izin atau sakit
        </a>
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

    </div>

    <script>
        setTimeout(() => {
            const feedback = document.getElementById('camera-feedback');
            if (feedback?.textContent.trim() !== 'Mengaktifkan kamera...') return;

            feedback.textContent = 'Pemindai belum dijalankan oleh Livewire. Muat ulang halaman; jika tetap muncul, pastikan versi terbaru sudah diunggah dan cache view hosting dibersihkan.';
            const retryButton = document.getElementById('camera-retry');
            if (retryButton) retryButton.hidden = false;
        }, 8000);
    </script>

@assets
    <script src="https://unpkg.com/html5-qrcode@2.3.8"></script>
@endassets

@script
    <script>
        let attendanceQrScanner = null;
        let attendanceQrSubmitting = false;
        let attendanceQrStartTimer = null;
        let attendanceQrPermissionTimer = null;
        let attendanceQrStartAttempts = 0;

        function attendanceCameraElement(id) {
            return $wire.$el?.querySelector(`#${id}`) ?? document.getElementById(id);
        }

        function setAttendanceCameraFeedback(message, allowRetry = false) {
            const feedback = attendanceCameraElement('camera-feedback');
            const retryButton = attendanceCameraElement('camera-retry');

            if (feedback) feedback.textContent = message;
            if (retryButton) retryButton.hidden = !allowRetry;
        }

        function startAttendanceScanner() {
            if (attendanceQrScanner) return;

            const reader = attendanceCameraElement('reader');
            if (!reader) {
                if (attendanceQrStartAttempts >= 20) {
                    setAttendanceCameraFeedback('Area kamera tidak ditemukan. Muat ulang halaman; jika tetap muncul, unggah versi terbaru aplikasi ke hosting.', true);
                    return;
                }

                attendanceQrStartAttempts++;
                setAttendanceCameraFeedback('Menyiapkan area kamera...');
                if (!attendanceQrStartTimer) {
                    attendanceQrStartTimer = setTimeout(() => {
                        attendanceQrStartTimer = null;
                        startAttendanceScanner();
                    }, 150);
                }

                return;
            }

            attendanceQrStartAttempts = 0;

            if (!window.isSecureContext) {
                setAttendanceCameraFeedback('Kamera perlu dibuka melalui HTTPS. Aktifkan SSL pada domain hosting lalu muat ulang halaman.', true);
                return;
            }

            if (!navigator.mediaDevices?.getUserMedia) {
                setAttendanceCameraFeedback('Browser ini tidak mendukung akses kamera. Coba buka halaman dengan Chrome atau browser terbaru.', true);
                return;
            }

            if (typeof Html5Qrcode === 'undefined') {
                setAttendanceCameraFeedback('Library pemindai QR gagal dimuat. Periksa koneksi internet lalu coba lagi.', true);
                return;
            }

            const scanner = new Html5Qrcode('reader');
            attendanceQrScanner = scanner;
            setAttendanceCameraFeedback('Meminta izin kamera...');
            attendanceQrPermissionTimer = setTimeout(() => {
                if (attendanceQrScanner === scanner) {
                    setAttendanceCameraFeedback('Permintaan kamera belum dijawab. Periksa apakah dialog izin kamera muncul di browser.', true);
                }
            }, 12000);

            scanner.start(
                { facingMode: 'environment' },
                { fps: 20, qrbox: { width: 250, height: 250 }, aspectRatio: 1.0 },
                (decodedText) => {
                    if (attendanceQrSubmitting) return;
                    attendanceQrSubmitting = true;
                    setAttendanceCameraFeedback('QR terbaca. Sedang memproses absensi...');

                    scanner.stop().catch(() => {}).finally(() => {
                        if (attendanceQrScanner === scanner) attendanceQrScanner = null;
                        $wire.call('processResult', decodedText).finally(() => {
                            attendanceQrSubmitting = false;
                        });
                    });
                }
            ).then(() => {
                clearTimeout(attendanceQrPermissionTimer);
                attendanceQrPermissionTimer = null;
                setAttendanceCameraFeedback('Kamera aktif. Arahkan kamera ke QR Code.');
            }).catch((error) => {
                clearTimeout(attendanceQrPermissionTimer);
                attendanceQrPermissionTimer = null;
                console.error('Camera error:', error);
                if (attendanceQrScanner === scanner) attendanceQrScanner = null;

                const messages = {
                    NotAllowedError: 'Izin kamera ditolak. Izinkan kamera untuk situs ini dari ikon pengaturan di samping alamat browser, lalu coba lagi.',
                    PermissionDeniedError: 'Izin kamera ditolak. Izinkan kamera untuk situs ini dari pengaturan browser, lalu coba lagi.',
                    NotFoundError: 'Kamera tidak ditemukan pada perangkat ini.',
                    NotReadableError: 'Kamera sedang dipakai aplikasi lain. Tutup aplikasi tersebut lalu coba lagi.',
                    OverconstrainedError: 'Kamera belakang tidak tersedia. Coba gunakan perangkat atau browser lain.',
                    SecurityError: 'Browser memblokir kamera. Pastikan situs menggunakan HTTPS.'
                };

                setAttendanceCameraFeedback(messages[error?.name] ?? 'Kamera gagal dibuka. Pastikan izin kamera aktif dan kamera tidak dipakai aplikasi lain.', true);
            });
        }

        // Start from the rendered scanner element instead of a Blade-time condition.
        // This also covers first-load timing differences in nested Filament/Livewire views.
        requestAnimationFrame(() => setTimeout(startAttendanceScanner, 150));

        $wire.on('scanner-start', () => setTimeout(startAttendanceScanner, 150));
        $wire.on('scanner-reset', () => setTimeout(startAttendanceScanner, 150));

        document.addEventListener('click', (event) => {
            if (!event.target.closest('#camera-retry')) return;

            if (attendanceQrScanner) {
                const scanner = attendanceQrScanner;
                attendanceQrScanner = null;
                clearTimeout(attendanceQrPermissionTimer);
                attendanceQrPermissionTimer = null;
                scanner.stop().catch(() => {}).finally(startAttendanceScanner);
                return;
            }

            startAttendanceScanner();
        });
    </script>
@endscript
