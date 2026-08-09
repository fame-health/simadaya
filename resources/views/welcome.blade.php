<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMADAYA - Portal Magang Resmi Dinas Kebudayaan Provinsi Riau</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        melayu: {
                            50: '#f0f9f1',
                            600: '#15803d',
                            700: '#166534',
                            800: '#14532d',
                            gold: '#d97706',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        .glass-header { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(8px); }
        .hero-pattern { background-color: #ffffff; background-image: radial-gradient(#166534 0.5px, transparent 0.5px); background-size: 24px 24px; }
        .step-card:hover .step-number { transform: scale(1.1) rotate(10deg); background-color: #d97706; }
    </style>
</head>
<body class="antialiased text-slate-800 bg-white selection:bg-melayu-600 selection:text-white" x-data="{ mobileMenu: false }">

<!-- Top Brand Bar -->
<div class="bg-melayu-800 text-white py-2 px-6 hidden sm:block">
    <div class="max-w-7xl mx-auto flex justify-between items-center text-[10px] font-bold uppercase tracking-[0.2em]">
        <span>Pemerintah Provinsi Riau</span>
        <span>Layanan Informasi Terpadu Kebudayaan</span>
    </div>
</div>

<!-- Main Navigation -->
<nav class="sticky top-0 z-[100] glass-header border-b border-slate-100 py-4 px-6 shadow-sm">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo-riau.png') }}"
                     alt="Logo Provinsi Riau"
                     class="h-10 sm:h-12 w-auto object-contain"
                     onerror="this.src='https://disbud.riau.go.id/assets/guest/img/image/logo-riau.png';">
            </div>
            <div class="hidden lg:block border-l border-slate-200 pl-4">
                <h1 class="text-sm font-extrabold text-melayu-800 leading-none tracking-tight uppercase">SIMADAYA</h1>
                <p class="text-[9px] text-slate-500 font-bold uppercase tracking-widest mt-1">Dinas Kebudayaan Riau</p>
            </div>
        </div>

        <div class="hidden md:flex items-center gap-8">
            <div class="flex gap-6 text-[11px] font-extrabold text-slate-600 uppercase tracking-widest">
                <a href="#beranda" class="hover:text-melayu-600 transition-colors">Beranda</a>
                <a href="#profil" class="hover:text-melayu-600 transition-colors">Profil</a>
                <a href="#alur" class="hover:text-melayu-600 transition-colors">Alur Magang</a>
            </div>
            @if (Route::has('login'))
                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="bg-melayu-700 text-white px-6 py-2.5 rounded-full text-xs font-bold hover:bg-melayu-800 shadow-lg shadow-melayu-100 transition-all flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            Buka Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-xs font-bold text-slate-700 hover:text-melayu-600 px-3">Login</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="bg-melayu-600 text-white px-6 py-2.5 rounded-full text-xs font-bold hover:bg-melayu-700 shadow-md transition-all">Daftar</a>
                        @endif
                    @endauth
                </div>
            @endif
        </div>

        <button class="md:hidden p-2 text-slate-600" @click="mobileMenu = !mobileMenu">
            <svg class="w-6 h-6" x-show="!mobileMenu" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            <svg class="w-6 h-6" x-show="mobileMenu" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <div class="md:hidden absolute top-full inset-x-0 bg-white border-b border-slate-100 shadow-xl p-6" x-show="mobileMenu" x-cloak x-transition>
        <div class="flex flex-col gap-5 text-xs font-bold text-slate-700 uppercase tracking-widest text-center">
            <a href="#beranda" @click="mobileMenu = false">Beranda</a>
            <a href="#profil" @click="mobileMenu = false">Profil</a>
            <a href="#alur" @click="mobileMenu = false">Alur Magang</a>
            <hr class="border-slate-50">
            @auth
                <a href="{{ url('/dashboard') }}" class="text-melayu-600">Dashboard</a>
            @else
                <a href="{{ route('login') }}">Login</a>
                <a href="{{ route('register') }}" class="text-melayu-600">Daftar Magang</a>
            @endauth
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section id="beranda" class="relative pt-12 pb-24 lg:pt-20 lg:pb-40 overflow-hidden hero-pattern">
    <div class="max-w-7xl mx-auto px-6 grid lg:grid-cols-2 gap-16 items-center">
        <div class="z-10 text-center lg:text-left">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-melayu-50 border border-melayu-100 text-melayu-700 text-[10px] font-extrabold uppercase tracking-[0.2em] mb-8">
                <span class="w-2 h-2 rounded-full bg-melayu-600"></span>
                Digitalisasi Warisan Budaya
            </div>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-slate-900 leading-[1.1] mb-6">
                Pelajari dan Lestarikan <br><span class="text-melayu-600 italic">Warisan Budaya</span> <br>Untuk Masa Depan
            </h1>
            <p class="text-sm sm:text-base text-slate-600 leading-relaxed mb-10 max-w-xl mx-auto lg:mx-0">
                Dinas Kebudayaan Provinsi Riau berkomitmen tinggi dalam menjaga identitas kolektif daerah melalui inovasi digital. Bergabunglah dalam ekosistem pelestarian tradisi yang profesional.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                @auth
                    <a href="{{ url('/dashboard') }}" class="w-full sm:w-auto bg-melayu-800 text-white px-10 py-4 rounded-xl text-sm font-bold hover:bg-black transition-all shadow-xl shadow-melayu-100 flex items-center justify-center gap-2">
                        Masuk ke Dashboard
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 7l5 5m0 0l-5 5m5-5H6" stroke-width="3"/></svg>
                    </a>
                @else
                    <a href="{{ route('register') }}" class="w-full sm:w-auto bg-melayu-800 text-white px-10 py-4 rounded-xl text-sm font-bold hover:bg-black transition-all shadow-xl shadow-melayu-100 flex items-center justify-center gap-2">
                        Daftar Magang Sekarang
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 7l5 5m0 0l-5 5m5-5H6" stroke-width="3"/></svg>
                    </a>
                    <a href="#alur" class="w-full sm:w-auto bg-white border border-slate-200 text-slate-700 px-10 py-4 rounded-xl text-sm font-bold hover:bg-slate-50 transition-all">
                        Pelajari Alur
                    </a>
                @endauth
            </div>
        </div>

        <div class="relative group">
            <div class="absolute -inset-4 bg-melayu-50 rounded-[3rem] -rotate-3 transition-transform group-hover:rotate-0"></div>
            <div class="relative rounded-[2.5rem] overflow-hidden shadow-2xl border-8 border-white bg-slate-100">
                <img src="https://disbud.riau.go.id/storage/images/content/sejarah/1731456737-6733eee1b40b6.jpg"
                     alt="Kebudayaan Riau"
                     class="w-full h-full object-cover min-h-[400px]"
                     onerror="this.src='https://pekanbaru.go.id/asset/berita/original/71180216447-tari-persembahan.jpg';">
                <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/80 p-8 text-white">
                    <p class="text-xs font-bold uppercase tracking-widest opacity-80 mb-2">Warisan Budaya</p>
                    <h3 class="text-xl font-bold">Lestarikan Sejarah Riau</h3>
                    <p class="text-[10px] italic opacity-60 mt-1">Membangun masa depan melalui pelestarian akar budaya Melayu.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section: Profil -->
<section id="profil" class="py-24 bg-white relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-6">
        <div class="grid lg:grid-cols-2 gap-20 items-center">
            <div class="space-y-8">
                <div class="space-y-4">
                    <h2 class="text-xs font-black text-melayu-gold uppercase tracking-[0.4em]">Tentang Kami</h2>
                    <p class="text-3xl font-extrabold text-slate-900 leading-tight">Dinas Kebudayaan <br><span class="text-melayu-600 font-medium">Provinsi Riau</span></p>
                </div>
                <p class="text-sm text-slate-600 leading-relaxed font-medium">
                    Kami adalah institusi yang berdedikasi penuh untuk melestarikan dan mengembangkan kekayaan kebudayaan Melayu. Melalui platform digital ini, kami memfasilitasi pertukaran ide serta memperkuat rasa cinta masyarakat terhadap akar tradisi, seni, dan adat istiadat Riau.
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-4">
                    <div class="flex items-start gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-100 hover:border-melayu-200 transition-all">
                        <div class="p-2 bg-white rounded-xl shadow-sm text-melayu-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m4 0h1m-5 10h5m-5 4h5m2-10h1m2 0h1"/></svg>
                        </div>
                        <div>
                            <h4 class="text-xs font-black uppercase tracking-widest text-slate-900">Pelestarian Budaya</h4>
                            <p class="text-[10px] text-slate-500 mt-1">Menjaga warisan sejarah tetap relevan.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-100 hover:border-melayu-200 transition-all">
                        <div class="p-2 bg-white rounded-xl shadow-sm text-melayu-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                        <div>
                            <h4 class="text-xs font-black uppercase tracking-widest text-slate-900">Bimbingan Ahli</h4>
                            <p class="text-[10px] text-slate-500 mt-1">Edukasi dari praktisi berpengalaman.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="relative">
                <div class="absolute -inset-10 bg-melayu-50 rounded-full blur-[100px] opacity-50"></div>
                <div class="relative rounded-[2rem] overflow-hidden shadow-2xl border-4 border-white bg-slate-50">
                    <img src="https://disbud.riau.go.id/storage/images/content/sambutan/1783484373-6a4dcfd59568a.jpg"
                         alt="Sambutan Dinas Kebudayaan Riau"
                         class="w-full h-full object-cover min-h-[300px]"
                         onerror="this.src='https://disbud.riau.go.id/storage/images/content/sejarah/1731456100-6733ec641e7d8.jpg';">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section: Alur -->
<section id="alur" class="py-24 bg-slate-50 border-t border-slate-100">
    <div class="max-w-7xl mx-auto px-6 text-center">
        <h2 class="text-xs font-black text-melayu-gold uppercase tracking-[0.4em] mb-4">Prosedur Magang</h2>
        <p class="text-3xl font-extrabold text-slate-900 leading-tight">Alur Pendaftaran <span class="text-melayu-600">Simadaya</span></p>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mt-20 relative">
            <div class="absolute top-1/2 left-0 w-full h-0.5 bg-slate-200 -translate-y-1/2 hidden lg:block"></div>
            <div class="step-card relative z-10 space-y-6">
                <div class="step-number w-14 h-14 bg-melayu-800 text-white rounded-2xl flex items-center justify-center mx-auto text-lg font-black shadow-xl transition-all duration-300">1</div>
                <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
                    <h4 class="text-sm font-extrabold text-slate-900 mb-2">Registrasi</h4>
                    <p class="text-[10px] text-slate-500 font-medium">Buat akun & unggah berkas KTM/Surat.</p>
                </div>
            </div>
            <div class="step-card relative z-10 space-y-6 lg:mt-12">
                <div class="step-number w-14 h-14 bg-melayu-800 text-white rounded-2xl flex items-center justify-center mx-auto text-lg font-black shadow-xl transition-all duration-300">2</div>
                <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
                    <h4 class="text-sm font-extrabold text-slate-900 mb-2">Seleksi</h4>
                    <p class="text-[10px] text-slate-500 font-medium">Verifikasi dokumen oleh Admin Disbud.</p>
                </div>
            </div>
            <div class="step-card relative z-10 space-y-6">
                <div class="step-number w-14 h-14 bg-melayu-800 text-white rounded-2xl flex items-center justify-center mx-auto text-lg font-black shadow-xl transition-all duration-300">3</div>
                <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
                    <h4 class="text-sm font-extrabold text-slate-900 mb-2">Magang</h4>
                    <p class="text-[10px] text-slate-500 font-medium">Absensi QR & pengisian logbook rutin.</p>
                </div>
            </div>
            <div class="step-card relative z-10 space-y-6 lg:mt-12">
                <div class="step-number w-14 h-14 bg-melayu-800 text-white rounded-2xl flex items-center justify-center mx-auto text-lg font-black shadow-xl transition-all duration-300">4</div>
                <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
                    <h4 class="text-sm font-extrabold text-slate-900 mb-2">Sertifikat</h4>
                    <p class="text-[10px] text-slate-500 font-medium">Unduh sertifikat resmi digital.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="bg-melayu-800 text-white pt-24 pb-12">
    <div class="max-w-7xl mx-auto px-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-20 mb-20">
            <div class="space-y-8">
                <div class="flex items-center gap-4">
                    <div class="bg-white p-2 rounded-xl">
                        <img src="{{ asset('images/logo-riau.png') }}"
                             alt="Logo Riau"
                             class="h-10 w-auto"
                             onerror="this.src='https://disbud.riau.go.id/assets/guest/img/image/logo-riau.png';">
                    </div>
                    <div>
                        <h4 class="text-xl font-extrabold tracking-tight">SIMADAYA</h4>
                        <p class="text-[10px] text-melayu-50/60 font-bold uppercase tracking-widest mt-1">Dinas Kebudayaan Riau</p>
                    </div>
                </div>
                <p class="text-melayu-50/70 text-base leading-relaxed max-w-md">
                    Portal terpadu pengembangan kapasitas sumber daya manusia di bidang kebudayaan Melayu. Dedikasi untuk negeri, inovasi untuk tradisi.
                </p>
            </div>
            <div class="grid grid-cols-2 gap-12">
                <div>
                    <h5 class="text-[10px] font-black uppercase tracking-[0.3em] mb-8 text-melayu-gold">Informasi</h5>
                    <ul class="space-y-4 text-xs font-bold opacity-60">
                        <li><a href="#beranda" class="hover:opacity-100 transition-opacity uppercase tracking-widest">Halaman Utama</a></li>
                        <li><a href="#profil" class="hover:opacity-100 transition-opacity uppercase tracking-widest">Profil Dinas</a></li>
                        <li><a href="#alur" class="hover:opacity-100 transition-opacity uppercase tracking-widest">Alur Pendaftaran</a></li>
                    </ul>
                </div>
                <div>
                    <h5 class="text-[10px] font-black uppercase tracking-[0.3em] mb-8 text-melayu-gold">Hubungi Kami</h5>
                    <div class="space-y-6 text-xs font-bold opacity-60">
                        <div class="flex items-start gap-4">
                            <svg class="w-5 h-5 shrink-0 text-melayu-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>Jl. Jenderal Sudirman No. 275, Pekanbaru</span>
                        </div>
                        <div class="flex items-center gap-4">
                            <svg class="w-5 h-5 shrink-0 text-melayu-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span>disbud@riau.go.id</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="pt-12 border-t border-white/10 text-center">
            <p class="text-[10px] font-black uppercase tracking-[0.3em] opacity-40">
                &copy; 2025 SIMADAYA &bull; DINAS KEBUDAYAAN RIAU. All rights reserved.
            </p>
        </div>
    </div>
</footer>

<script>
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                window.scrollTo({
                    top: target.offsetTop - 80,
                    behavior: 'smooth'
                });
            }
        });
    });
</script>

</body>
</html>
