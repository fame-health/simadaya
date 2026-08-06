<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .input-focus {
            transition: all 0.3s ease;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
        }

        .input-focus:focus {
            outline: none;
            border-color: #16BC5C;
            box-shadow: 0 0 0 3px rgba(22, 188, 92, 0.1);
            transform: translateY(-1px);
        }

        .btn-hover {
            transition: all 0.3s ease;
            padding: 12px 24px;
        }

        .btn-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(22, 188, 92, 0.3);
            background-color: #059669;
        }

        .form-container {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }

        .dark .form-container {
            background: rgba(31, 41, 55, 0.95);
        }

        .floating-elements {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
        }

        .floating-circle {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .floating-circle:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .floating-circle:nth-child(2) {
            width: 120px;
            height: 120px;
            top: 60%;
            right: 10%;
            animation-delay: 2s;
        }

        .floating-circle:nth-child(3) {
            width: 60px;
            height: 60px;
            top: 80%;
            left: 20%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6 relative" style="background: url('https://disbud.riau.go.id/storage/images/content/hero-background/1733462001-675287f13a9ad.png') no-repeat center center / cover;">
    <div class="absolute inset-0 bg-black/40 z-0"></div>

    <div class="floating-elements">
        <div class="floating-circle"></div>
        <div class="floating-circle"></div>
        <div class="floating-circle"></div>
    </div>

    <div class="form-container dark:bg-gray-800 rounded-2xl shadow-2xl p-10 w-full max-w-md relative z-10">
        <div class="text-center mb-8">
            <div class="flex justify-center items-center gap-4 mb-4">
                <img src="https://disbud.riau.go.id/assets/guest/img/image/logo-riau.png" alt="Logo Riau" class="w-16 h-auto">
                <img src="https://disbud.riau.go.id/assets/guest/img/image/logo-disbud.png" alt="Logo Disbud" class="w-16 h-auto">
            </div>
            <h2 class="text-3xl font-bold text-gray-800 dark:text-gray-100 mb-2">SIMADAYA</h2>
            <p class="text-gray-600 dark:text-gray-400">Sistem Magang Dinas Kebudayaan</p>
        </div>

        {{-- Session Status --}}
        @if (session('status'))
            <div class="mb-4 text-green-600 text-sm text-center font-semibold">
                {{ session('status') }}
            </div>
        @endif

        {{-- Global Errors --}}
        @if ($errors->any())
            <div class="mb-4 text-red-500 text-sm text-center">
                {{ __('Terjadi kesalahan. Silakan periksa kembali.') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf

            {{-- Email --}}
            <div>
                <label for="email" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">
                    Email
                </label>
                <input
                    id="email"
                    class="input-focus block w-full rounded-xl border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="username"
                >
                @error('email')
                    <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password --}}
            <div>
                <label for="password" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">
                    Password
                </label>
                <input
                    id="password"
                    class="input-focus block w-full rounded-xl border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                >
                @error('password')
                    <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Remember Me --}}
            <div class="flex items-center">
                <input
                    id="remember_me"
                    type="checkbox"
                    class="rounded border-gray-300 dark:border-gray-700 text-green-600 focus:ring-green-500 dark:focus:ring-green-600 h-4 w-4"
                    name="remember"
                >
                <label for="remember_me" class="ml-3 text-sm text-gray-600 dark:text-gray-400">
                    Remember me
                </label>
            </div>

            {{-- Submit --}}

<div class="flex flex-col space-y-4">
    <button
        type="submit"
        class="btn-hover w-full bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl focus:outline-none focus:ring-4 focus:ring-green-300"
    >
        Sign In
    </button>

    @if (Route::has('register'))
        <a
            href="{{ route('register') }}"
            class="w-full inline-block text-center btn-hover border-2 border-green-600 text-green-700 hover:text-white hover:bg-green-600 font-semibold rounded-xl transition duration-200"
        >
            Register
        </a>
    @endif

    <div class="text-center">
        @if (Route::has('password.request'))
            <a
                class="text-sm text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 transition-colors duration-200 hover:underline"
                href="{{ route('password.request') }}"
            >
                Forgot your password?
            </a>
        @endif
    </div>
</div>
