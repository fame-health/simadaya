<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atur Ulang Password - SIMADAYA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .custom-input {
            transition: all 0.2s ease-in-out;
        }

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

        .animate-fade-in {
            animation: fadeIn 0.6s ease-out forwards;
        }
    </style>
</head>
<body class="bg-white min-h-screen overflow-x-hidden">
    <div class="flex flex-col lg:flex-row min-h-screen">

        <!-- Left Side: Branding (Hidden on Mobile) -->
        <div class="relative hidden lg:flex w-full lg:w-3/5 items-center justify-center p-8 lg:p-12 overflow-hidden min-h-screen">
            <!-- Background Image -->
            <div class="absolute inset-0 z-0">
                <img src="https://disbud.riau.go.id/storage/images/content/hero-background/1733462001-675287f13a9ad.png"
                     alt="Background"
                     class="w-full h-full object-cover">
                <div class="absolute inset-0 branding-overlay backdrop-blur-sm"></div>
            </div>

            <!-- Branding Content -->
            <div class="relative z-10 text-center lg:text-left max-w-xl animate-fade-in">
                <div class="flex justify-center lg:justify-start items-center gap-4 mb-8">
                    <div class="p-3 bg-white/95 rounded-2xl shadow-xl">
                        <img src="https://disbud.riau.go.id/assets/guest/img/image/logo-riau.png" alt="Logo Riau" class="h-12 lg:h-16 w-auto">
                    </div>
                    <div class="p-3 bg-white/95 rounded-2xl shadow-xl">
                        <img src="https://disbud.riau.go.id/assets/guest/img/image/logo-disbud.png" alt="Logo Disbud" class="h-12 lg:h-16 w-auto">
                    </div>
                </div>

                <h1 class="text-4xl lg:text-6xl font-extrabold text-white mb-4 tracking-tight">
                    SIMADAYA
                </h1>
                <p class="text-xl lg:text-2xl text-emerald-50 font-medium opacity-90 mb-6">
                    Amankan Akun Anda dengan Password Baru
                </p>
                <div class="hidden lg:block h-1 w-24 bg-emerald-400 rounded-full"></div>
            </div>

            <!-- Desktop Footer Info -->
            <div class="hidden lg:block absolute bottom-12 left-12 text-emerald-100/50 text-sm font-medium">
                &copy; {{ date('Y') }} Dinas Kebudayaan Provinsi Riau.
            </div>
        </div>

        <!-- Right Side: Reset Password Form -->
        <div class="w-full lg:w-2/5 flex items-center justify-center p-6 sm:p-12 lg:p-20 bg-gray-50 lg:bg-white overflow-y-auto">
            <div class="w-full max-w-md animate-fade-in" style="animation-delay: 0.2s;">
                <div class="mb-10 text-center lg:text-left">
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">Atur Ulang Password</h2>
                    <p class="text-gray-500 font-medium text-base leading-relaxed">
                        Silakan buat password baru yang kuat untuk mengamankan akun Anda.
                    </p>
                </div>

                <form method="POST" action="{{ route('password.store') }}" class="space-y-6">
                    @csrf

                    <!-- Password Reset Token -->
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    {{-- Email Address (Readonly or Prefilled) --}}
                    <div>
                        <label for="email" class="block text-sm font-bold text-gray-700 mb-2 ml-1">
                            Email
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-emerald-500">
                                <i data-lucide="mail" class="w-5 h-5"></i>
                            </div>
                            <input
                                id="email"
                                class="custom-input block w-full pl-11 pr-4 py-4 bg-gray-100 border border-gray-200 rounded-2xl text-gray-500 text-sm focus:outline-none shadow-sm cursor-not-allowed"
                                type="email"
                                name="email"
                                value="{{ old('email', $request->email) }}"
                                required
                                readonly
                                autocomplete="username"
                            >
                        </div>
                        @error('email')
                            <p class="mt-2 text-xs text-red-500 ml-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- New Password --}}
                    <div>
                        <label for="password" class="block text-sm font-bold text-gray-700 mb-2 ml-1">
                            Password Baru
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-emerald-500">
                                <i data-lucide="lock" class="w-5 h-5"></i>
                            </div>
                            <input
                                id="password"
                                class="custom-input block w-full pl-11 pr-12 py-4 bg-white lg:bg-gray-50 border border-gray-200 rounded-2xl text-gray-900 text-sm focus:outline-none placeholder:text-gray-400 shadow-sm"
                                type="password"
                                name="password"
                                placeholder="Masukkan password baru"
                                required
                                autofocus
                                autocomplete="new-password"
                            >
                            <button
                                type="button"
                                onclick="toggleVisibility('password', 'eyeIcon1', 'eyeOffIcon1')"
                                class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-emerald-600 transition-colors"
                            >
                                <i data-lucide="eye" id="eyeIcon1" class="w-5 h-5"></i>
                                <i data-lucide="eye-off" id="eyeOffIcon1" class="w-5 h-5 hidden"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-2 text-xs text-red-500 ml-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Confirm Password --}}
                    <div>
                        <label for="password_confirmation" class="block text-sm font-bold text-gray-700 mb-2 ml-1">
                            Konfirmasi Password Baru
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-emerald-500">
                                <i data-lucide="check-square" class="w-5 h-5"></i>
                            </div>
                            <input
                                id="password_confirmation"
                                class="custom-input block w-full pl-11 pr-12 py-4 bg-white lg:bg-gray-50 border border-gray-200 rounded-2xl text-gray-900 text-sm focus:outline-none placeholder:text-gray-400 shadow-sm"
                                type="password"
                                name="password_confirmation"
                                placeholder="Ulangi password baru"
                                required
                                autocomplete="new-password"
                            >
                            <button
                                type="button"
                                onclick="toggleVisibility('password_confirmation', 'eyeIcon2', 'eyeOffIcon2')"
                                class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-emerald-600 transition-colors"
                            >
                                <i data-lucide="eye" id="eyeIcon2" class="w-5 h-5"></i>
                                <i data-lucide="eye-off" id="eyeOffIcon2" class="w-5 h-5 hidden"></i>
                            </button>
                        </div>
                        @error('password_confirmation')
                            <p class="mt-2 text-xs text-red-500 ml-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-4">
                        <button
                            type="submit"
                            class="btn-primary w-full py-4 px-6 text-white font-bold rounded-2xl focus:outline-none focus:ring-4 focus:ring-emerald-200 transition-all text-base shadow-lg shadow-emerald-200"
                        >
                            Reset Password Sekarang
                        </button>
                    </div>
                </form>

                <!-- Mobile Copyright -->
                <p class="lg:hidden text-center mt-12 text-gray-400 text-xs font-medium">
                    &copy; {{ date('Y') }} Dinas Kebudayaan Provinsi Riau.
                </p>
            </div>
        </div>
    </div>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        // Password visibility toggle function
        function toggleVisibility(inputId, eyeId, eyeOffId) {
            const input = document.getElementById(inputId);
            const eye = document.getElementById(eyeId);
            const eyeOff = document.getElementById(eyeOffId);

            if (input.type === "password") {
                input.type = "text";
                eye.classList.add('hidden');
                eyeOff.classList.remove('hidden');
            } else {
                input.type = "password";
                eye.classList.remove('hidden');
                eyeOff.classList.add('hidden');
            }
        }
    </script>
</body>
</html>
