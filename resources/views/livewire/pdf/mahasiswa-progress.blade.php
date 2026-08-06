<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Progres Magang</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 2.54cm;
            /* Diubah dari 1cm ke 2.54cm */
            line-height: 1.5;
            color: #333;
        }

        @media print {
            body {
                margin: 2.54cm !important;
                /* Pastikan margin tetap 2.54cm saat dicetak */
            }

            .no-print {
                display: none;
            }

            .page-break {
                page-break-before: always;
            }
        }

        /* ... (kode CSS lainnya tetap sama) ... */
        @page {
            size: A4;
            margin: 2.54cm;
            /* Diubah dari 0.5cm ke 2.54cm */
        }

        h1 {
            text-align: center;
            color: #1e3a8a;
            font-size: 24pt;
            margin-bottom: 10px;
        }

        h2 {
            color: #15803d;
            font-size: 18pt;
            margin-top: 30px;
            border-bottom: 2px solid #15803d;
            padding-bottom: 5px;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .header img {
            max-width: 80px;
            margin-bottom: 10px;
        }

        .header p {
            margin: 5px 0;
            font-size: 12pt;
        }

        .header hr {
            border: 1px solid #000;
            margin: 15px 0;
        }

        .info-section {
            margin-bottom: 20px;
            font-size: 12pt;
        }

        .info-section p {
            margin: 5px 0;
        }

        .progress-bar-container {
            margin: 20px 0;
        }

        .progress-bar {
            background-color: #e5e7eb;
            height: 12px;
            border-radius: 6px;
            overflow: hidden;
            border: 1px solid #ccc;
        }

        .progress-fill {
            background-color: #2563eb;
            height: 100%;
            transition: width 0.3s ease;
        }

        .progress-text {
            font-size: 10pt;
            text-align: right;
            color: #6b7280;
            margin-top: 5px;
        }

        .progress-container,
        .catatan-container,
        .penilaian-container {
            margin-top: 20px;
        }

        .step,
        .catatan,
        .penilaian {
            margin-bottom: 15px;
            padding: 15px;
            border: 1px solid #d1d5db;
            border-radius: 5px;
            background-color: #f9fafb;
        }

        .step-title,
        .catatan-title {
            font-size: 14pt;
            font-weight: bold;
            color: #15803d;
            margin-bottom: 10px;
        }

        .step-description,
        .catatan-description,
        .penilaian-description {
            font-size: 12pt;
            color: #374151;
            margin: 5px 0;
        }

        .step-status,
        .catatan-status {
            font-size: 10pt;
            color: #6b7280;
            margin-top: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 12pt;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .signature {
            margin-top: 40px;
            text-align: right;
            font-size: 12pt;
        }

        .signature p {
            margin: 8px 0;
        }

        .signature-space {
            height: 80px;
        }

        @page {
            size: A4;
            margin: 0.5cm;
        }
    </style>
</head>

<body>
    <!-- Wrapper Flex -->
    <table width="100%" style="border-bottom: 3px double #000; padding-bottom: 10px;">
        <tr>
            <!-- Logo -->
            <td style="width: 100px; text-align: center; vertical-align: middle;">
                <img src="<?php echo htmlspecialchars(public_path('images/logo-disbud.png')); ?>" alt="Logo Dinas" style="width: 90px;">
            </td>

            <!-- Teks Instansi -->
            <td style="text-align: center;">
                <p style="margin: 0; font-size: 14px; font-weight: bold;">PEMERINTAH PROVINSI RIAU</p>
                <p style="margin: 0; font-size: 16px; font-weight: bold;">DINAS KEBUDAYAAN</p>
                <p style="margin: 0; font-size: 12px;">Jl. Jend. Sudirman No. 123, Pekanbaru, Riau</p>
                <p style="margin: 0; font-size: 12px;">Telp: (0761) 123456 | Email: dinas.kebudayaan@riau.go.id</p>
            </td>
        </tr>
    </table>

    <!-- Garis Ganda di Bawah -->
    <hr style="border: 2px solid black; margin: 0;">
    <hr style="border: 1px solid black; margin-top: 2px; margin-bottom: 20px;">

<!-- Tabel Mahasiswa -->
<h3 style="margin-top: 30px; font-weight: bold;">Data Mahasiswa PKL</h3>
<table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
    <tbody>
        <tr>
            <td style="padding: 8px; font-weight: bold; width: 30%;">Nama Mahasiswa</td>
            <td style="padding: 8px;"><?php echo isset($mahasiswa->user->name) ? htmlspecialchars($mahasiswa->user->name) : 'N/A'; ?></td>
        </tr>
        <tr>
            <td style="padding: 8px; font-weight: bold;">NIM/NIP</td>
            <td style="padding: 8px;"><?php echo isset($mahasiswa->nim) ? htmlspecialchars($mahasiswa->nim) : 'N/A'; ?></td>
        </tr>
        <tr>
            <td style="padding: 8px; font-weight: bold;">Universitas/Sekolah</td>
            <td style="padding: 8px;"><?php echo isset($mahasiswa->universitas) ? htmlspecialchars($mahasiswa->universitas) : 'N/A'; ?></td>
        </tr>
        <tr>
            <td style="padding: 8px; font-weight: bold;">Jurusan</td>
            <td style="padding: 8px;"><?php echo isset($mahasiswa->jurusan) ? htmlspecialchars($mahasiswa->jurusan) : 'N/A'; ?></td>
        </tr>

        <?php if (isset($pengajuanMagang)): ?>
        <tr>
            <td style="padding: 8px; font-weight: bold;">Bidang Magang</td>
            <td style="padding: 8px;"><?php echo isset($pengajuanMagang->bidang_diminati) ? htmlspecialchars($pengajuanMagang->bidang_diminati) : 'N/A'; ?></td>
        </tr>
        <tr>
            <td style="padding: 8px; font-weight: bold;">Durasi Magang</td>
            <td style="padding: 8px;"><?php echo isset($pengajuanMagang->durasi_magang) ? htmlspecialchars($pengajuanMagang->durasi_magang) . ' minggu' : 'N/A'; ?></td>
        </tr>
        <tr>
            <td style="padding: 8px; font-weight: bold;">Tanggal Mulai</td>
            <td style="padding: 8px;"><?php echo isset($pengajuanMagang->tanggal_mulai) ? htmlspecialchars($pengajuanMagang->tanggal_mulai->format('d M Y')) : 'N/A'; ?></td>
        </tr>
        <tr>
            <td style="padding: 8px; font-weight: bold;">Tanggal Selesai</td>
            <td style="padding: 8px;"><?php echo isset($pengajuanMagang->tanggal_selesai) ? htmlspecialchars($pengajuanMagang->tanggal_selesai->format('d M Y')) : 'N/A'; ?></td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- Tabel Pembimbing -->
<h3 style="margin-top: 30px; font-weight: bold;">Data Pembimbing Lapanggan</h3>
<table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
    <tbody>
        <tr>
            <td style="padding: 8px; font-weight: bold; width: 30%;">Nama Pembimbing</td>
            <td style="padding: 8px;"><?php echo isset($pengajuanMagang->pembimbing->user->name) ? htmlspecialchars($pengajuanMagang->pembimbing->user->name) : 'N/A'; ?></td>
        </tr>
        <tr>
            <td style="padding: 8px; font-weight: bold;">NIP Pembimbing</td>
            <td style="padding: 8px;"><?php echo isset($pengajuanMagang->pembimbing->nip) ? htmlspecialchars($pengajuanMagang->pembimbing->nip) : 'N/A'; ?></td>
        </tr>
    </tbody>
</table>


 

    <div class="page-break"></div>

    <h2>Penilaian</h2>
    <div class="penilaian-container">
        <?php if (!empty($penilaian)): ?>
        <table>
            <thead>
                <tr>
                    <th>Aspek Penilaian</th>
                    <th>Nilai</th>
                    <th>Bobot</th>
                    <th>Nilai Akhir</th>
                    <th>Grade</th>
                    <th>Keterangan</th>
                    <th>Tanggal Penilaian</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($penilaian as $item): ?>
                <tr>
                    <td><?php echo isset($item->aspek_penilaian) ? htmlspecialchars($item->aspek_penilaian) : 'N/A'; ?></td>
                    <td><?php echo isset($item->nilai) ? htmlspecialchars($item->nilai) : 'N/A'; ?></td>
                    <td><?php echo isset($item->bobot) ? htmlspecialchars($item->bobot) : 'N/A'; ?></td>
                    <td><?php echo isset($item->nilai_akhir) ? htmlspecialchars($item->nilai_akhir) : 'N/A'; ?></td>
                    <td><?php echo isset($item->grade) ? htmlspecialchars($item->grade) : 'N/A'; ?></td>
                    <td><?php echo isset($item->keterangan) ? htmlspecialchars($item->keterangan) : 'N/A'; ?></td>
                    <td><?php echo isset($item->tanggal_penilaian) ? htmlspecialchars($item->tanggal_penilaian->format('d M Y')) : 'N/A'; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p>Tidak ada data penilaian tersedia.</p>
        <?php endif; ?>
    </div>
<!-- Struktur utama -->
<table style="width: 100%; font-size: 12pt; border-collapse: collapse; margin-top: 60px;">
    <tr>
        <td style="width: 50%; text-align: left; vertical-align: top;">
            <p style="margin: 0;">Pembimbing Lapangan,</p>
        </td>
        <td style="width: 50%; text-align: right; vertical-align: top;">
            <p style="margin: 0;">Pekanbaru, <?php echo date('d M Y'); ?></p>
            <p style="margin: 0;">Kepala Dinas Kebudayaan Provinsi Riau</p>
        </td>
    </tr>

    <!-- Baris tanda tangan -->
    <tr>
        <td style="padding-top: 80px; text-align: left; vertical-align: top;">
            <p style="margin: 0;">
                (<?php echo isset($pengajuanMagang->pembimbing->user->name) ? htmlspecialchars($pengajuanMagang->pembimbing->user->name) : '________________'; ?>)
            </p>
            <p style="margin: 0;">
                NIP: <?php echo isset($pengajuanMagang->pembimbing->nip) ? htmlspecialchars($pengajuanMagang->pembimbing->nip) : '________________'; ?>
            </p>
        </td>
        <td style="padding-top: 80px; text-align: right; vertical-align: top;">
            <p style="margin: 0;">(__________________________)</p>
            <p style="margin: 0;"></p>
            <p style="margin: 0;"></p>
        </td>
    </tr>
</table>






</body>

</html>
