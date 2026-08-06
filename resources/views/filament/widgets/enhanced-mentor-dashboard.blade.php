@php
    $data = $this->getData();
    $hour = now()->hour;
    $greeting = match(true) {
        $hour < 12 => 'Selamat Pagi',
        $hour < 15 => 'Selamat Siang',
        $hour < 18 => 'Selamat Sore',
        default => 'Selamat Malam',
    };
    $isAdmin = $data['is_admin'];
@endphp

<x-filament-widgets::widget>
    <div class="fi-wi-enhanced-mentor-dashboard antialiased" style="display: block; width: 100%;">

        <style>
            /* Sembunyikan judul "Dashboard" dan hilangkan semua space di atasnya */
            .fi-header-heading,
            header.fi-header > h1,
            .fi-header {
                display: none !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            /* Paksa kontainer utama naik ke paling atas */
            main.fi-main,
            .fi-main-ctn {
                padding-top: 0 !important;
            }

            /* Tarik widget kustom ke atas agar menempel ke navbar */
            .fi-wi-widget.fi-wi-enhanced-mentor-dashboard {
                margin-top: -2.5rem !important;
            }

            .dashboard-wrapper { display: flex; flex-direction: column; gap: 1rem; font-family: inherit; }

            /* Compact Header */
            .header-banner {
                position: relative; overflow: hidden; border-radius: 1rem; padding: 1.25rem;
                background: {{ $isAdmin ? 'linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%)' : 'linear-gradient(135deg, #059669 0%, #10b981 100%)' }};
                color: white; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            }
            .header-banner .decor-circle {
                position: absolute; top: -1rem; right: -1rem; width: 6rem; height: 6rem;
                background: rgba(255, 255, 255, 0.1); border-radius: 50%; filter: blur(30px);
            }

            /* Responsive Grid Stats */
            .grid-stats { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.75rem; }
            @media (min-width: 1024px) {
                .grid-stats { grid-template-columns: repeat({{ $isAdmin ? '3' : '4' }}, minmax(0, 1fr)); }
            }

            .card-stat {
                padding: 1rem; border-radius: 0.75rem; display: flex; flex-direction: column; gap: 0.25rem;
                transition: transform 0.2s; border: 1px solid rgba(0,0,0,0.05); position: relative; overflow: hidden;
            }
            .card-stat:active { transform: scale(0.98); }

            .card-amber { background: #fffbeb; border-left: 4px solid #f59e0b; }
            .card-blue { background: #eff6ff; border-left: 4px solid #3b82f6; }
            .card-emerald { background: #ecfdf5; border-left: 4px solid #10b981; }
            .card-indigo { background: #eef2ff; border-left: 4px solid #6366f1; }
            .card-rose { background: #fff1f2; border-left: 4px solid #f43f5e; }
            .card-cyan { background: #ecfeff; border-left: 4px solid #06b6d4; }

            .stat-value { font-size: 1.5rem; font-weight: 800; line-height: 1; margin: 0.25rem 0; }
            .stat-label { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; tracking: 0.05em; color: #4b5563; }
            .stat-desc { font-size: 0.55rem; font-weight: 500; color: #6b7280; line-height: 1.2; }

            /* Main Content Grid */
            .main-content-grid { display: grid; grid-template-columns: 1fr; gap: 1rem; }
            @media (min-width: 1024px) { .main-content-grid { grid-template-columns: 2fr 1fr; } }

            .presence-panel { background: white; border-radius: 1rem; padding: 1.25rem; border: 1px solid #e5e7eb; }
            .dark .presence-panel { background: #111827; border-color: #374151; }

            .presence-row { display: grid; grid-template-columns: repeat(3, 1fr); margin: 1.25rem 0; text-align: center; gap: 0.5rem; }
            .presence-item p:first-child { font-size: 0.6rem; font-weight: 700; text-transform: uppercase; color: #6b7280; }
            .presence-item p:last-child { font-size: 1.75rem; font-weight: 800; }

            .security-card {
                background: #111827; color: white; border-radius: 1rem; padding: 1.25rem;
                display: flex; flex-direction: column; justify-content: space-between; min-height: 180px;
            }

            .btn-group { display: flex; gap: 0.5rem; flex-wrap: wrap; }
        </style>

        <div class="dashboard-wrapper">
            {{-- Compact Header --}}
            <div class="header-banner">
                <div class="decor-circle"></div>
                <div style="display: flex; align-items: center; justify-content: space-between; position: relative; z-index: 10;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <div style="background: rgba(255,255,255,0.2); padding: 0.5rem; border-radius: 0.75rem;">
                            <x-heroicon-o-shield-check style="width: 1.5rem; height: 1.5rem;" />
                        </div>
                        <div>
                            <p style="font-size: 0.6rem; font-weight: 700; text-transform: uppercase; opacity: 0.9;">{{ $greeting }}</p>
                            <h2 style="font-size: 1.25rem; font-weight: 800; line-height: 1;">{{ $isAdmin ? 'Administrator Console' : $data['user_name'] }}</h2>
                            <p style="font-size: 0.7rem; opacity: 0.8; margin-top: 0.1rem;">{{ $isAdmin ? 'Central Management' : strtoupper($data['role']) }} • SIMADAYA</p>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <p style="font-size: 0.5rem; font-weight: 700; text-transform: uppercase; opacity: 0.7;">System Status</p>
                        <p style="font-size: 0.875rem; font-weight: 800;">ONLINE</p>
                    </div>
                </div>
            </div>

            @if($isAdmin)
                {{-- ADMIN STATS --}}
                <div class="grid-stats">
                    <div class="card-stat card-blue">
                        <span class="stat-label">Total Mahasiswa</span>
                        <p class="stat-value" style="color: #1d4ed8;">{{ $data['total_students'] }}</p>
                        <p class="stat-desc">Seluruh mahasiswa terdaftar.</p>
                        <x-heroicon-m-users style="position: absolute; right: 0.5rem; top: 0.5rem; width: 1.2rem; height: 1.2rem; opacity: 0.1;" />
                    </div>
                    <div class="card-stat card-cyan">
                        <span class="stat-label">Total Pembimbing</span>
                        <p class="stat-value" style="color: #0891b2;">{{ $data['total_mentors'] }}</p>
                        <p class="stat-desc">Dosen/Staf pengawas aktif.</p>
                        <x-heroicon-m-user-group style="position: absolute; right: 0.5rem; top: 0.5rem; width: 1.2rem; height: 1.2rem; opacity: 0.1;" />
                    </div>
                    <div class="card-stat card-amber">
                        <span class="stat-label">Pending Approval</span>
                        <p class="stat-value" style="color: #b45309;">{{ $data['pending'] }}</p>
                        <p class="stat-desc">Pendaftaran menunggu review.</p>
                        <x-heroicon-m-clock style="position: absolute; right: 0.5rem; top: 0.5rem; width: 1.2rem; height: 1.2rem; opacity: 0.1;" />
                    </div>
                    <div class="card-stat card-emerald">
                        <span class="stat-label">Magang Aktif</span>
                        <p class="stat-value" style="color: #047857;">{{ $data['active'] }}</p>
                        <p class="stat-desc">Mahasiswa di lokasi magang.</p>
                        <x-heroicon-m-briefcase style="position: absolute; right: 0.5rem; top: 0.5rem; width: 1.2rem; height: 1.2rem; opacity: 0.1;" />
                    </div>
                    <div class="card-stat card-indigo">
                        <span class="stat-label">Selesai Magang</span>
                        <p class="stat-value" style="color: #4338ca;">{{ $data['completed'] }}</p>
                        <p class="stat-desc">Program magang yang tuntas.</p>
                        <x-heroicon-m-academic-cap style="position: absolute; right: 0.5rem; top: 0.5rem; width: 1.2rem; height: 1.2rem; opacity: 0.1;" />
                    </div>
                    <div class="card-stat card-rose">
                        <span class="stat-label">Sesi Presensi</span>
                        <p class="stat-value" style="color: #be123c;">{{ $data['admin_active_sessions'] }}</p>
                        <p class="stat-desc">Sesi absensi sistem aktif.</p>
                        <x-heroicon-m-check-badge style="position: absolute; right: 0.5rem; top: 0.5rem; width: 1.2rem; height: 1.2rem; opacity: 0.1;" />
                    </div>
                </div>
            @else
                {{-- PEMBIMBING STATS --}}
                <div class="grid-stats">
                    <div class="card-stat card-amber">
                        <span class="stat-label">Pending</span>
                        <p class="stat-value" style="color: #b45309;">{{ $data['pending'] }}</p>
                        <p class="stat-desc">Menunggu persetujuan Anda.</p>
                        <x-heroicon-m-clock style="position: absolute; right: 0.5rem; top: 0.5rem; width: 1.2rem; height: 1.2rem; opacity: 0.1;" />
                    </div>
                    <div class="card-stat card-blue">
                        <span class="stat-label">Mahasiswa Aktif</span>
                        <p class="stat-value" style="color: #1d4ed8;">{{ $data['active'] }}</p>
                        <p class="stat-desc">Mahasiswa yang Anda bimbing.</p>
                        <x-heroicon-m-users style="position: absolute; right: 0.5rem; top: 0.5rem; width: 1.2rem; height: 1.2rem; opacity: 0.1;" />
                    </div>
                    <div class="card-stat card-emerald">
                        <span class="stat-label">Selesai</span>
                        <p class="stat-value" style="color: #047857;">{{ $data['completed'] }}</p>
                        <p class="stat-desc">Telah menyelesaikan bimbingan.</p>
                        <x-heroicon-m-academic-cap style="position: absolute; right: 0.5rem; top: 0.5rem; width: 1.2rem; height: 1.2rem; opacity: 0.1;" />
                    </div>
                    <div class="card-stat card-indigo">
                        <span class="stat-label">Total Riwayat</span>
                        <p class="stat-value" style="color: #4338ca;">{{ $data['total'] }}</p>
                        <p class="stat-desc">Seluruh riwayat bimbingan Anda.</p>
                        <x-heroicon-m-briefcase style="position: absolute; right: 0.5rem; top: 0.5rem; width: 1.2rem; height: 1.2rem; opacity: 0.1;" />
                    </div>
                </div>
            @endif

            {{-- Presence & Security --}}
            <div class="main-content-grid">
                <div class="presence-panel">
                    <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 0.75rem; border-bottom: 1px solid #f3f4f6;">
                        <h3 style="font-size: 0.875rem; font-weight: 800; color: #111827;">System Presence Insight</h3>
                        <span style="color: #059669; font-size: 0.5rem; font-weight: 800;">{{ $isAdmin ? 'GLOBAL VIEW' : 'PERSONAL VIEW' }}</span>
                    </div>

                    <div class="presence-row">
                        <div class="presence-item">
                            <p>Sesi Aktif</p>
                            <p>{{ $data['active_sessions'] }}</p>
                        </div>
                        <div class="presence-item">
                            <p style="color: #059669;">Total Hadir</p>
                            <p style="color: #059669;">{{ $data['present_today'] }}</p>
                        </div>
                        <div class="presence-item">
                            <p style="color: #dc2626;">Tdk Hadir</p>
                            <p style="color: #dc2626;">{{ $data['not_present'] }}</p>
                        </div>
                    </div>

                    <div class="btn-group">
                        <x-filament::button icon="heroicon-m-plus" size="xs" tag="a" href="{{ \App\Filament\Resources\AttendanceSessionResource::getUrl('create') }}">
                            + Sesi
                        </x-filament::button>
                        <x-filament::button color="gray" icon="heroicon-m-document-text" size="xs" tag="a" href="{{ \App\Filament\Resources\AttendanceLogResource::getUrl('index') }}">
                            Laporan
                        </x-filament::button>
                    </div>
                </div>

                <div class="security-card">
                    <div>
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                            <x-heroicon-o-key style="width: 1rem; height: 1rem; color: #10b981;" />
                            <h4 style="font-size: 0.875rem; font-weight: 800;">System Shield</h4>
                        </div>
                        <p style="font-size: 0.6rem; color: #9ca3af; line-height: 1.4;">{{ $isAdmin ? 'Global encryption and token validation monitoring active.' : 'Your session is protected by 256-bit encryption.' }}</p>
                    </div>

                    <div style="background: rgba(255,255,255,0.05); padding: 0.75rem; border-radius: 0.75rem; border: 1px solid rgba(255,255,255,0.1); margin-top: 1rem;">
                        <p style="font-size: 0.5rem; font-weight: 700; color: #10b981; text-transform: uppercase;">Sync Time</p>
                        <div style="font-family: monospace; font-size: 1.25rem; font-weight: 800;">
                            {{ now()->format('H:i') }}<span style="color: #10b981;">:{{ now()->format('s') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div style="text-align: center; margin-top: 0.5rem;">
                <p style="font-size: 0.5rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3em; color: #9ca3af;">
                    SIMADAYA • {{ $isAdmin ? 'ADMIN CONTROL CENTER' : 'MENTOR PORTAL' }}
                </p>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
