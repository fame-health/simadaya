<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email - SIMADAYA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap');

        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .custom-input { transition: all 0.2s ease-in-out; }
        .custom-input:focus {
            box-shadow: 0 0 0 4px rgba(22, 188, 92, 0.1);
            border-color: #16BC5C;
        }
        .btn-primary {
            background: linear-gradient(135deg, #16BC5C 0%, #059669 100%);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(22, 188, 92, 0.3);
        }
        .branding-overlay {
            background: linear-gradient(135deg, rgba(5, 150, 105, 0.85) 0%, rgba(6, 78, 59, 0.9) 100%);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in { animation: fadeIn 0.6s ease-out forwards; }
    </style>
</head>
<body class="bg-white min-h-screen overflow-x-hidden">
    <div class="flex flex-col lg:flex-row min-h-screen">
        <div class="relative hidden lg:flex w-full lg:w-3/5 items-center justify-center p-8 lg:p-12 overflow-hidden min-h-screen">
            <div class="absolute inset-0 z-0">
                <img src="https://disbud.riau.go.id/storage/images/content/hero-background/1733462001-675287f13a9ad.png"
                     alt="" class="w-full h-full object-cover">
                <div class="absolute inset-0 branding-overlay backdrop-blur-sm"></div>
            </div>

            <div class="relative z-10 text-center lg:text-left max-w-xl animate-fade-in">
                <div class="flex justify-center lg:justify-start items-center gap-4 mb-8">
                    <div class="p-3 bg-white/95 rounded-2xl shadow-xl">
                        <img src="https://disbud.riau.go.id/assets/guest/img/image/logo-riau.png" alt="Logo Riau" class="h-12 lg:h-16 w-auto">
                    </div>
                    <div class="p-3 bg-white/95 rounded-2xl shadow-xl">
                        <img src="https://disbud.riau.go.id/assets/guest/img/image/logo-disbud.png" alt="Logo Disbud" class="h-12 lg:h-16 w-auto">
                    </div>
                </div>
                <h1 class="text-4xl lg:text-6xl font-extrabold text-white mb-4 tracking-tight">SIMADAYA</h1>
                <p class="text-xl lg:text-2xl text-emerald-50 font-medium opacity-90 mb-6">
                    Sistem Magang Dinas Kebudayaan Provinsi Riau
                </p>
                <div class="hidden lg:block h-1 w-24 bg-emerald-400 rounded-full"></div>
            </div>

            <div class="hidden lg:block absolute bottom-12 left-12 text-emerald-100/50 text-sm font-medium">
                &copy; {{ date('Y') }} Dinas Kebudayaan Provinsi Riau.
            </div>
        </div>

        <div class="w-full lg:w-2/5 flex items-center justify-center p-6 sm:p-12 lg:p-20 bg-gray-50 lg:bg-white min-h-screen">
            <div class="w-full max-w-md animate-fade-in" style="animation-delay: 0.2s;">
                <div class="mb-10 text-center lg:text-left">
                    <div class="mx-auto lg:mx-0 mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                        <i data-lucide="mail-check" class="h-7 w-7"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">Verifikasi Email</h2>
                    <p class="text-gray-500 font-medium text-base leading-relaxed">
                        Masukkan kode 6 digit yang kami kirim ke
                        <span class="font-bold text-gray-700 break-all">{{ auth()->user()->email }}</span>.
                        Kode berlaku selama 10 menit.
                    </p>
                </div>

                @if (session('status') === 'verification-code-sent')
                    <div class="mb-6 p-4 rounded-xl bg-emerald-50 text-emerald-700 text-sm font-medium border border-emerald-100 flex items-center gap-2">
                        <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0 text-emerald-500"></i>
                        Kode verifikasi baru telah dikirim ke email Anda.
                    </div>
                @elseif (session('status') === 'verification-email-failed')
                    <div class="mb-6 p-4 rounded-xl bg-red-50 text-red-700 text-sm font-medium border border-red-100 flex items-center gap-2">
                        <i data-lucide="alert-circle" class="w-4 h-4 shrink-0 text-red-500"></i>
                        Kode belum dapat dikirim. Silakan coba kirim ulang.
                    </div>
                @elseif (session('status') === 'verification-code-throttled')
                    <div class="mb-6 p-4 rounded-xl bg-amber-50 text-amber-700 text-sm font-medium border border-amber-100 flex items-center gap-2">
                        <i data-lucide="clock-3" class="w-4 h-4 shrink-0 text-amber-500"></i>
                        Terlalu banyak permintaan. Coba lagi dalam {{ max(1, ceil(session('retry_after', 60) / 60)) }} menit.
                    </div>
                @endif

                <form method="POST" action="{{ route('verification.code.verify') }}" class="space-y-6">
                    @csrf
                    <div>
                        <label for="code" class="block text-sm font-bold text-gray-700 mb-2 ml-1">Kode Verifikasi</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-emerald-500">
                                <i data-lucide="key-round" class="w-5 h-5"></i>
                            </div>
                            <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code"
                                maxlength="6" pattern="[0-9]{6}" value="{{ old('code') }}" required autofocus
                                placeholder="Masukkan 6 digit kode"
                                class="custom-input block w-full pl-11 pr-4 py-4 bg-white border border-gray-200 rounded-2xl text-gray-900 text-sm tracking-[0.35em] font-bold focus:outline-none placeholder:tracking-normal placeholder:font-normal placeholder:text-gray-400 shadow-sm">
                        </div>
                        @error('code')
                            <p class="mt-2 text-xs text-red-500 ml-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="btn-primary w-full py-4 px-6 text-white font-bold rounded-2xl focus:outline-none focus:ring-4 focus:ring-emerald-200 transition-all text-base shadow-lg shadow-emerald-200">
                        Verifikasi Email
                    </button>
                </form>

                <div class="mt-8 text-center space-y-4">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="text-sm font-bold text-emerald-600 hover:text-emerald-700 transition-colors">
                            Belum menerima kode? Kirim ulang
                        </button>
                    </form>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors">
                            Keluar dari akun
                        </button>
                    </form>
                </div>

                <p class="lg:hidden text-center mt-12 text-gray-400 text-xs font-medium">
                    &copy; {{ date('Y') }} Dinas Kebudayaan Provinsi Riau.
                </p>
            </div>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
