<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMDAYA - Sistem Magang Dinas Kebudayaan Provinsi Riau</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'riau-green': '#16BC5C',
                        'riau-green-light': '#059669',
                        'riau-gold': '#d97706',
                        'pastel-bg': '#f0fdf4',
                        'pastel-card': '#f5f5f4'
                    }
                }
            }
        }
    </script>
    <style>
        .slide-in { animation: slideIn 1s ease-out forwards; }
        @keyframes slideIn { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { transform: translateY(-5px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        .mobile-menu { transform: translateY(-100%); transition: transform 0.3s ease-in-out; }
        .mobile-menu.open { transform: translateY(0); }
    </style>
</head>
<body class="font-sans bg-pastel-bg">
<!-- Navigation -->
<nav class="bg-white shadow-sm fixed w-full z-50 top-0">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20 sm:h-24">
            <div class="flex items-center space-x-3">
                <div class="flex items-center space-x-2">
                    <img src="https://disbud.riau.go.id/assets/guest/img/image/logo-riau.png" alt="Logo Dinas Kebudayaan Provinsi Riau" class="h-12 w-auto object-contain" onerror="this.src='/images/fallback-logo-riau.png';">
                    <img src="https://disbud.riau.go.id/assets/guest/img/image/logo-disbud.png" alt="Logo SIMADAYA" class="h-12 w-auto object-contain" onerror="this.src='/images/fallback-logo-disbud.png';">
                </div>
                <div class="text-gray-700">
                    <h1 class="font-bold text-lg text-teal-300">SIMADAYA</h1>
                    <p class="text-xs">Sistem Magang Dinas Kebudayaan</p>
                </div>
            </div>
            <div class="hidden md:flex space-x-8 items-center">
                <a href="#beranda" class="text-gray-700 hover:text-[#16BC5C] transition-colors duration-300 font-medium">Beranda</a>
                <a href="#tentang" class="text-gray-700 hover:text-[#16BC5C] transition-colors duration-300 font-medium">Tentang</a>
                <a href="#program" class="text-gray-700 hover:text-[#16BC5C] transition-colors duration-300 font-medium">Program</a>
                <a href="#kontak" class="text-gray-700 hover:text-[#16BC5C] transition-colors duration-300 font-medium">Kontak</a>
                <a href='/dashboard' class="bg-[#16BC5C] text-white px-6 py-2 rounded-lg font-medium hover:bg-riau-green-light transition-colors duration-300">
                    Dashboard
                </a>
            </div>
            <button class="md:hidden text-gray-700 text-2xl focus:outline-none" id="mobile-menu-toggle">
                ☰
            </button>
        </div>
        <div class="mobile-menu hidden md:hidden bg-white shadow-md w-full absolute left-0 top-20 sm:top-24" id="mobile-menu">
            <div class="flex flex-col items-center space-y-4 py-4">
                <a href="#beranda" class="text-gray-700 hover:text-[#16BC5C] font-medium text-lg">Beranda</a>
                <a href="#tentang" class="text-gray-700 hover:text-[#16BC5C] font-medium text-lg">Tentang</a>
                <a href="#program" class="text-gray-700 hover:text-[#16BC5C] font-medium text-lg">Program</a>
                <a href="#kontak" class="text-gray-700 hover:text-[#16BC5C] font-medium text-lg">Kontak</a>
                <a href='/dashboard' class="bg-[#16BC5C] text-white px-6 py-2 rounded-lg font-medium hover:bg-riau-green-light transition-colors duration-300 w-full max-w-xs">
                    Dashboard
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section id="beranda" class="bg-white pt-24 sm:pt-32 pb-12 sm:pb-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div class="slide-in">
                <div class="inline-block bg-gray-100 text-[#16BC5C] px-4 py-2 rounded-lg text-sm font-medium mb-4 sm:mb-6">
                    Program Resmi Pemerintah Provinsi Riau
                </div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold mb-4 sm:mb-6 leading-tight text-gray-900">
                    Bergabunglah dalam Program Magang Kebudayaan yang Menginspirasi
                </h1>
                <p class="text-base sm:text-lg mb-6 sm:mb-8 text-gray-600 leading-relaxed">
                    Kembangkan potensi diri dan wawasan kebudayaan melalui program magang resmi Dinas Kebudayaan Provinsi Riau. Dapatkan pengalaman berharga dalam pelestarian dan pengembangan budaya Melayu.
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <button onclick="document.getElementById('program').scrollIntoView({ behavior: 'smooth' });"
                        class="bg-[#16BC5C] text-white px-6 sm:px-8 py-3 rounded-lg font-medium hover:bg-riau-green-light transition-all duration-300">
                        Jelajahi Program
                    </button>
                    <button class="border-2 border-[#16BC5C] text-[#16BC5C] px-6 sm:px-8 py-3 rounded-lg font-medium hover:bg-[#16BC5C] hover:text-white transition-all duration-300">
                        Pelajari Lebih Lanjut
                    </button>
                </div>
            </div>
            <div class="relative">
                <div class="bg-pastel-card rounded-2xl p-6 sm:p-8 shadow-lg">
                    <div class="text-center">
                        <div class="w-16 sm:w-20 h-16 sm:h-20 bg-[#16BC5C] rounded-xl mx-auto mb-4 sm:mb-6 flex items-center justify-center">
                            <svg class="w-8 sm:w-10 h-8 sm:h-10 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3z"></path>
                                <path d="M3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762z"></path>
                                <path d="M9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0z"></path>
                                <path d="M6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg sm:text-xl font-bold text-gray-800 mb-4">Platform Terintegrasi</h3>
                        <p class="text-gray-600 mb-4 sm:mb-6 text-sm sm:text-base">Sistem digital terpadu untuk pengelolaan program magang yang profesional dan akuntabel</p>
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div>
                                <div class="text-xl sm:text-2xl font-bold text-[#16BC5C]">150</div>
                                <div class="text-xs sm:text-sm text-gray-600">Peserta+</div>
                            </div>
                            <div>
                                <div class="text-xl sm:text-2xl font-bold text-[#16BC5C]">12</div>
                                <div class="text-xs sm:text-sm text-gray-600">Program</div>
                            </div>
                            <div>
                                <div class="text-xl sm:text-2xl font-bold text-riau-gold">95</div>
                                <div class="text-xs sm:text-sm text-gray-600">Tingkat Kepuasan%</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="tentang" class="py-12 sm:py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12 sm:mb-16">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-6">Tentang SIMADAYA</h2>
            <p class="text-base sm:text-lg text-gray-600 max-w-3xl mx-auto">
                SIMADAYA adalah sistem terintegrasi untuk mengelola program magang di Dinas Kebudayaan Provinsi Riau. Kami menyediakan platform digital yang memudahkan pengelolaan, monitoring, dan evaluasi program magang dengan standar profesional.
            </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
            <!-- Feature 1 -->
            <div class="bg-pastel-card border border-gray-200 p-6 rounded-xl card-hover">
                <div class="w-12 h-12 bg-riau-green rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Proses Mudah & Cepat</h3>
                <p class="text-gray-600 text-sm sm:text-base">Pendaftaran online yang sederhana dengan proses verifikasi yang efisien dan transparan untuk semua peserta.</p>
            </div>

            <!-- Feature 2 -->
            <div class="bg-pastel-card border border-gray-200 p-6 rounded-xl card-hover">
                <div class="w-12 h-12 bg-riau-green rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Pembimbing Profesional</h3>
                <p class="text-gray-600 text-sm sm:text-base">Didampingi oleh mentor berpengalaman dari Dinas Kebudayaan untuk memastikan pembelajaran yang optimal.</p>
            </div>

            <!-- Feature 3 -->
            <div class="bg-pastel-card border border-gray-200 p-6 rounded-xl card-hover">
                <div class="w-12 h-12 bg-riau-gold rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Sertifikat Resmi</h3>
                <p class="text-gray-600 text-sm sm:text-base">Dapatkan sertifikat resmi dari Dinas Kebudayaan Provinsi Riau yang diakui untuk pengembangan karir.</p>
            </div>

            <!-- Feature 4 -->
            <div class="bg-pastel-card border border-gray-200 p-6 rounded-xl card-hover">
                <div class="w-12 h-12 bg-riau-green rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Monitoring Real-time</h3>
                <p class="text-gray-600 text-sm sm:text-base">Sistem pemantauan progres pembelajaran secara real-time untuk memastikan pencapaian target kompetensi.</p>
            </div>

            <!-- Feature 5 -->
            <div class="bg-pastel-card border border-gray-200 p-6 rounded-xl card-hover">
                <div class="w-12 h-12 bg-riau-green rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Komunikasi Terintegrasi</h3>
                <p class="text-gray-600 text-sm sm:text-base">Platform komunikasi yang memudahkan koordinasi antara peserta, pembimbing, dan pengelola program.</p>
            </div>

            <!-- Feature 6 -->
            <div class="bg-pastel-card border border-gray-200 p-6 rounded-xl card-hover">
                <div class="w-12 h-12 bg-riau-green rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M12 14l9-5-9-5-9 5 9 5z"></path>
                        <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.825-2.998 12.083 12.083 0 01.665-6.479L12 14z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Peluang Karir</h3>
                <p class="text-gray-600 text-sm sm:text-base">Jaringan profesional dan peluang karir di bidang kebudayaan setelah menyelesaikan program magang.</p>
            </div>
        </div>
    </div>
</section>

<!-- Programs Section -->
<section id="program" class="py-12 sm:py-20 bg-pastel-bg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12 sm:mb-16">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-6">Program Magang Tersedia</h2>
            <p class="text-base sm:text-lg text-gray-600 max-w-3xl mx-auto">
                Pilih program magang yang sesuai dengan minat dan latar belakang pendidikan Anda. Setiap program dirancang untuk memberikan pengalaman praktis yang berharga.
            </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
            <!-- Program 1 -->
            <div class="bg-white rounded-xl overflow-hidden shadow-sm border border-gray-200 card-hover">
                <div class="h-24 sm:h-32 bg-cover bg-center bg-no-repeat relative"
                     style="background-image: url('https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=500&h=300&fit=crop');">
                    <div class="absolute inset-0 bg-black bg-opacity-30"></div>
                    <div class="absolute bottom-4 left-6">
                        <div class="bg-white text-riau-green px-3 py-1 rounded-lg text-sm font-medium shadow-sm">
                            3 Bulan
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Administrasi Kebudayaan</h3>
                    <p class="text-gray-600 mb-4 text-sm sm:text-base">Mempelajari sistem administrasi, tata kelola, dan manajemen program kebudayaan di instansi pemerintah.</p>
                    <div class="flex items-center justify-between">
                        <span class="text-riau-green font-medium">18 Peserta</span>
                        <button class="bg-riau-green text-white px-4 py-2 rounded-lg text-sm hover:bg-riau-green-light transition-colors duration-300">
                            Lihat Detail
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="bg-white py-12 sm:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12 sm:mb-16">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-6">Testimoni Peserta</h2>
            <p class="text-base sm:text-lg text-gray-600 max-w-3xl mx-auto">
                Dengarkan pengalaman dari para alumni program magang yang telah merasakan manfaat nyata dari SIMADAYA dalam mengembangkan karir mereka.
            </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
            <!-- Testimonial 1 -->
            <div class="bg-pastel-card border border-gray-200 p-6 rounded-xl shadow-sm">
                <p class="text-gray-600 italic mb-4">"Program magang di SIMADAYA memberikan pengalaman yang sangat berharga. Saya belajar banyak tentang pelestarian budaya Melayu dan mendapat jaringan profesional yang luas."</p>
                <div class="flex items-center space-x-4">
                    <img src="https://images.unsplash.com/photo-1494790108755-2616b9b6c00c?w=48&h=48&fit=crop&crop=face" alt="Sari Indah" class="w-12 h-12 rounded-full object-cover">
                    <div>
                        <h4 class="font-semibold text-gray-900 text-sm">Sari Indah</h4>
                        <p class="text-xs text-gray-500">Alumni Program Pelestarian Budaya</p>
                    </div>
                </div>
            </div>

            <!-- Testimonial 2 -->
            <div class="bg-pastel-card border border-gray-200 p-6 rounded-xl shadow-sm">
                <p class="text-gray-600 italic mb-4">"Sistem digital yang canggih dan pembimbing yang sangat kompeten. Program ini benar-benar mempersiapkan saya untuk berkarir di bidang kebudayaan."</p>
                <div class="flex items-center space-x-4">
                    <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=48&h=48&fit=crop&crop=face" alt="Ahmad Rizki" class="w-12 h-12 rounded-full object-cover">
                    <div>
                        <h4 class="font-semibold text-gray-900 text-sm">Ahmad Rizki</h4>
                        <p class="text-xs text-gray-500">Alumni Program Museum & Heritage</p>
                    </div>
                </div>
            </div>

            <!-- Testimonial 3 -->
            <div class="bg-pastel-card border border-gray-200 p-6 rounded-xl shadow-sm">
                <p class="text-gray-600 italic mb-4">"Pengalaman yang tidak terlupakan! Dari program ini saya mendapat kesempatan untuk terlibat langsung dalam festival budaya besar di Riau."</p>
                <div class="flex items-center space-x-4">
                    <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=48&h=48&fit=crop&crop=face" alt="Maya Putri" class="w-12 h-12 rounded-full object-cover">
                    <div>
                        <h4 class="font-semibold text-gray-900 text-sm">Maya Putri</h4>
                        <p class="text-xs text-gray-500">Alumni Program Event & Festival</p>
                    </div>
                </div>
            </div>

            <!-- Testimonial 4 -->
            <div class="bg-pastel-card border border-gray-200 p-6 rounded-xl shadow-sm">
                <p class="text-gray-600 italic mb-4">"Platform SIMADAYA sangat user-friendly dan memudahkan monitoring progres pembelajaran. Sertifikat yang diberikan juga sangat membantu karir saya."</p>
                <div class="flex items-center space-x-4">
                    <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=48&h=48&fit=crop&crop=face" alt="Budi Santoso" class="w-12 h-12 rounded-full object-cover">
                    <div>
                        <h4 class="font-semibold text-gray-900 text-sm">Budi Santoso</h4>
                        <p class="text-xs text-gray-500">Alumni Program Digital Marketing</p>
                    </div>
                </div>
            </div>

            <!-- Testimonial 5 -->
            <div class="bg-pastel-card border border-gray-200 p-6 rounded-xl shadow-sm">
                <p class="text-gray-600 italic mb-4">"Program penelitian kebudayaan memberikan saya wawasan mendalam tentang metodologi riset. Sangat recommended untuk yang ingin berkarir di akademisi."</p>
                <div class="flex items-center space-x-4">
                    <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=48&h=48&fit=crop&crop=face" alt="Lisa Maharani" class="w-12 h-12 rounded-full object-cover">
                    <div>
                        <h4 class="font-semibold text-gray-900 text-sm">Lisa Maharani</h4>
                        <p class="text-xs text-gray-500">Alumni Program Penelitian Kebudayaan</p>
                    </div>
                </div>
            </div>

            <!-- Testimonial 6 -->
            <div class="bg-pastel-card border border-gray-200 p-6 rounded-xl shadow-sm">
                <p class="text-gray-600 italic mb-4">"Komunikasi dengan pembimbing sangat lancar melalui platform terintegrasi. Program magang ini benar-benar mempersiapkan saya untuk dunia kerja profesional."</p>
                <div class="flex items-center space-x-4">
                    <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=48&h=48&fit=crop&crop=face" alt="Deni Pratama" class="w-12 h-12 rounded-full object-cover">
                    <div>
                        <h4 class="font-semibold text-gray-900 text-sm">Deni Pratama</h4>
                        <p class="text-xs text-gray-500">Alumni Program Administrasi Kebudayaan</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="bg-gradient-to-r from-[#16BC5C] to-riau-green-light py-12 sm:py-16">
    <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
        <div class="text-white">
            <h2 class="text-2xl sm:text-3xl font-bold mb-6">Siap Memulai Perjalanan Magang Anda?</h2>
            <p class="text-base sm:text-lg mb-6 sm:mb-8 opacity-90">Bergabunglah dengan ratusan peserta lainnya yang telah merasakan manfaat program magang kebudayaan. Daftarkan diri Anda sekarang dan mulai kembangkan potensi di bidang kebudayaan.</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <button onclick="document.getElementById('program').scrollIntoView({ behavior: 'smooth' });" class="bg-white text-[#16BC5C] px-6 sm:px-8 py-3 rounded-lg font-medium hover:bg-gray-100 transition-all duration-300">
                    Daftar Sekarang
                </button>
                <button class="border-2 border-white text-white px-6 sm:px-8 py-3 rounded-lg font-medium hover:bg-white hover:text-[#16BC5C] transition-all duration-300">
                    Hubungi Kami
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer id="kontak" class="bg-gray-900 text-gray-300 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="col-span-1 md:col-span-2">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="flex items-center space-x-2">
                        <img src="https://disbud.riau.go.id/assets/guest/img/image/logo-riau.png" alt="Logo Dinas Kebudayaan Provinsi Riau" class="h-12 w-auto object-contain" onerror="this.src='/images/fallback-logo-riau.png';">
                        <img src="https://disbud.riau.go.id/assets/guest/img/image/logo-disbud.png" alt="Logo SIMADAYA" class="h-12 w-auto object-contain" onerror="this.src='/images/fallback-logo-disbud.png';">
                    </div>
                    <div>
                        <h3 class="font-bold text-xl text-white">SIMADAYA</h3>
                        <p class="text-gray-400 text-sm">Dinas Kebudayaan Provinsi Riau</p>
                    </div>
                </div>
                <p class="text-gray-400 mb-6 leading-relaxed text-sm sm:text-base">
                    Sistem Magang Dinas Kebudayaan yang dikelola oleh Dinas Kebudayaan Provinsi Riau untuk mendukung pengembangan sumber daya manusia di bidang kebudayaan.
                </p>
            </div>
            <div>
                <h4 class="font-semibold text-lg mb-4 text-white">Menu</h4>
                <ul class="space-y-2">
                    <li><a href="#beranda" class="text-gray-400 hover:text-white transition-colors duration-300 text-sm sm:text-base">Beranda</a></li>
                    <li><a href="#tentang" class="text-gray-400 hover:text-white transition-colors duration-300 text-sm sm:text-base">Tentang</a></li>
                    <li><a href="#program" class="text-gray-400 hover:text-white transition-colors duration-300 text-sm sm:text-base">Program</a></li>
                    <li><a href="#kontak" class="text-gray-400 hover:text-white transition-colors duration-300 text-sm sm:text-base">Kontak</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold text-lg mb-4 text-white">Kontak</h4>
                <div class="space-y-3">
                    <div class="flex items-start space-x-3">
                        <span class="material-icons-outlined text-yellow-400 text-base mt-1">location_on</span>
                        <span class="text-gray-400 text-sm">Jl. Jenderal Sudirman No. 275, Pekanbaru, Riau 28116</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <span class="material-icons-outlined text-yellow-400 text-base">phone</span>
                        <span class="text-gray-400 text-sm">(0761) 21562</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <span class="material-icons-outlined text-yellow-400 text-base">mail</span>
                        <span class="text-gray-400 text-sm">info@disbud.riau.go.id</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-10">
            <h4 class="text-white text-lg mb-4 font-semibold">Peta Lokasi Kami</h4>
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3989.6692087890397!2d101.43578531475397!3d0.5076953997432825!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31d5ab80690ee7b9%3A0x95d8a9417e5b9d0!2sDinas%20Kebudayaan%20Provinsi%20Riau!5e0!3m2!1sen!2sid!4v1643875432810!5m2!1sen!2sid" width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" class="rounded-lg shadow-lg"></iframe>
        </div>
        <div class="border-t border-gray-700 mt-8 pt-8 text-center">
            <p class="text-gray-500 text-sm">© 2025 SIMADAYA - Dinas Kebudayaan Provinsi Riau. Hak Cipta Dilindungi.</p>
        </div>
    </div>
</footer>

<script>
    // Smooth scrolling
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
            const mobileMenu = document.getElementById('mobile-menu');
            if (mobileMenu.classList.contains('open')) {
                mobileMenu.classList.add('hidden');
                mobileMenu.classList.remove('open');
                document.getElementById('mobile-menu-toggle').innerHTML = '☰';
            }
        });
    });

    // Fade in animation on scroll
    const observerOptions = { threshold: 0.1, rootMargin: '0px 0px -50px 0px' };
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('slide-in');
            }
        });
    }, observerOptions);
    document.querySelectorAll('section').forEach(section => {
        observer.observe(section);
    });

    // Mobile menu toggle
    const mobileMenuButton = document.getElementById('mobile-menu-toggle');
    const mobileMenu = document.getElementById('mobile-menu');
    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', () => {
            const isOpen = mobileMenu.classList.contains('open');
            if (isOpen) {
                mobileMenu.classList.add('hidden');
                mobileMenu.classList.remove('open');
                mobileMenuButton.innerHTML = '☰';
            } else {
                mobileMenu.classList.remove('hidden');
                mobileMenu.classList.add('open');
                mobileMenuButton.innerHTML = '✕';
            }
        });
    }

    // Counter animation
    function animateCounter(element, target, suffix = '') {
        let current = 0;
        const increment = target / 80;
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            element.textContent = Math.floor(current) + suffix;
        }, 25);
    }

    const heroSection = document.querySelector('#beranda');
    const counters = document.querySelectorAll('.text-xl.sm\\:text-2xl.font-bold');
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                counters.forEach(counter => {
                    const target = parseInt(counter.textContent.replace(/[^0-9]/g, '')) || 0;
                    const suffix = counter.nextElementSibling.textContent.includes('%') ? '%' : '+';
                    animateCounter(counter, target, suffix);
                });
                counterObserver.disconnect();
            }
        });
    }, { threshold: 0.5 });
    counterObserver.observe(heroSection);
</script>
</body>
