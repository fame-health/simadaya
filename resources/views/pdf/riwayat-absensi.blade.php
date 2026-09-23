<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Presensi Peserta Magang</title>

    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            color: #333333;
            margin: 20px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #2e7d32;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header-table td {
            vertical-align: middle;
        }

        .title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            color: #1b5e20;
            text-transform: uppercase;
            margin-bottom: 15px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .info-table td {
            padding: 4px 8px;
            font-size: 10pt;
        }

        .info-table td.label {
            font-weight: bold;
            width: 25%;
            color: #424242;
        }

        .stats-container {
            width: 100%;
            margin-bottom: 20px;
        }

        .stats-table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
        }

        .stats-table th {
            background-color: #2e7d32;
            color: #ffffff;
            font-size: 9pt;
            padding: 8px;
            text-transform: uppercase;
        }

        .stats-table td {
            border: 1px solid #cccccc;
            padding: 8px;
            font-size: 11pt;
            font-weight: bold;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .data-table th {
            background-color: #f5f5f5;
            color: #2e7d32;
            border: 1px solid #dddddd;
            padding: 8px;
            font-size: 10pt;
            text-align: left;
        }

        .data-table td {
            border: 1px solid #dddddd;
            padding: 7px 8px;
            font-size: 9.5pt;
        }

        .data-table tr:nth-child(even) {
            background-color: #fafafa;
        }

        .badge {
            display: inline-block;
            padding: 3px 7px;
            font-size: 8.5pt;
            font-weight: bold;
            border-radius: 3px;
            color: #ffffff;
            text-align: center;
        }

        .badge-hadir {
            background-color: #2e7d32;
        }

        .badge-izin {
            background-color: #f57c00;
        }

        .badge-sakit {
            background-color: #d32f2f;
        }

        .badge-alpa {
            background-color: #c62828;
        }

        .footer-table {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
        }

        .footer-table td {
            text-align: center;
            font-size: 10pt;
        }
    </style>
</head>
<body>

    <!-- Header Kop -->
    <table class="header-table">
        <tr>
            <td style="width: 15%; text-align: left;">
                <img src="{{ public_path('images/logo-riau.png') }}" alt="Logo" style="height: 65px;" onError="this.style.display='none';">
            </td>
            <td style="text-align: center;">
                <h2 style="margin: 0; font-size: 15pt; color: #1b5e20;">SISTEM INFORMASI MAGANG (SIMADAYA)</h2>
                <p style="margin: 3px 0 0 0; font-size: 10pt; color: #666666;">Laporan Resmi Riwayat Presensi Kehadiran Peserta Magang</p>
            </td>
            <td style="width: 15%;"></td>
        </tr>
    </table>

    <div class="title">LAPORAN RIWAYAT PRESENSI</div>

    <!-- Info Peserta -->
    <table class="info-table">
        <tr>
            <td class="label">Nama Peserta</td>
            <td>: {{ $student->user->name ?? '-' }}</td>
            <td class="label">NIM</td>
            <td>: {{ $student->nim ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Universitas</td>
            <td>: {{ $student->universitas ?? '-' }}</td>
            <td class="label">Jurusan</td>
            <td>: {{ $student->jurusan ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Pembimbing Lapangan</td>
            <td>: {{ $pengajuan?->pembimbing?->user?->name ?? 'Belum Ditentukan' }}</td>
            <td class="label">Periode Magang</td>
            <td>: {{ $pengajuan?->tanggal_mulai ? $pengajuan->tanggal_mulai->format('d/m/Y') : '-' }} s/d {{ $pengajuan?->tanggal_selesai ? $pengajuan->tanggal_selesai->format('d/m/Y') : '-' }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal Cetak</td>
            <td>: {{ $generatedAt }}</td>
            <td class="label">Filter Tanggal</td>
            <td>: {{ $startDate ? $startDate : 'Awal' }} s/d {{ $endDate ? $endDate : 'Akhir' }}</td>
        </tr>
    </table>

    <!-- Ringkasan Statistik -->
    <div class="stats-container">
        <table class="stats-table">
            <thead>
                <tr>
                    <th style="background-color: #2e7d32;">Hadir</th>
                    <th style="background-color: #f57c00;">Izin</th>
                    <th style="background-color: #d32f2f;">Sakit</th>
                    <th style="background-color: #c62828;">Alpa</th>
                    <th style="background-color: #1565c0;">Total Sesi/Hari</th>
                    <th style="background-color: #4527a0;">Persentase Kehadiran</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="color: #2e7d32;">{{ $stats['total_hadir'] }}</td>
                    <td style="color: #f57c00;">{{ $stats['total_izin'] }}</td>
                    <td style="color: #d32f2f;">{{ $stats['total_sakit'] }}</td>
                    <td style="color: #c62828;">{{ $stats['total_alpa'] }}</td>
                    <td style="color: #1565c0;">{{ $stats['total_total'] }}</td>
                    <td style="color: #4527a0;">{{ $stats['persentase_kehadiran'] }}%</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Tabel Detail Log Absensi -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No</th>
                <th style="width: 25%;">Waktu / Tanggal</th>
                <th style="width: 25%;">Sesi / Keterangan</th>
                <th style="width: 15%; text-align: center;">Status</th>
                <th style="width: 30%;">Alasan / Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $index => $log)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $log->scan_time ? $log->scan_time->translatedFormat('d F Y, H:i') : '-' }}</td>
                    <td>{{ $log->session?->session_name ?? 'Pengajuan Manual' }}</td>
                    <td style="text-align: center;">
                        @if($log->status === 'present')
                            <span class="badge badge-hadir">HADIR</span>
                        @elseif($log->status === 'permit')
                            <span class="badge badge-izin">IZIN</span>
                        @elseif($log->status === 'sick')
                            <span class="badge badge-sakit">SAKIT</span>
                        @else
                            <span class="badge badge-alpa">ALPA</span>
                        @endif
                    </td>
                    <td>{{ $log->reason ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #888888; padding: 15px;">
                        Belum ada riwayat absensi terdaftar untuk periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Tanda Tangan -->
    <table class="footer-table">
        <tr>
            <td style="width: 60%;"></td>
            <td style="width: 40%;">
                <p>Pekanbaru, {{ now()->translatedFormat('d F Y') }}</p>
                <p>Mengetahui,</p>
                <br><br><br>
                <p><strong><u>{{ $pengajuan?->pembimbing?->user?->name ?? 'Pembimbing Lapangan' }}</u></strong></p>
                <p style="font-size: 8.5pt; color: #666666;">Pembimbing Lapangan Magang</p>
            </td>
        </tr>
    </table>

</body>
</html>
