<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validasi Sertifikat Magang</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0fdf4; /* hijau muda */
            margin: 0;
            padding: 15px;
        }
        .container {
            max-width: 800px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
            border-top: 6px solid #16a34a; /* hijau */
        }
        .header {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #d1fae5; /* hijau muda */
            padding-bottom: 10px;
        }
        .header img {
            max-width: 90px; /* diperbesar */
            margin: 5px;
        }
        .header .title {
            text-align: center;
            flex: 1 1 100%;
        }
        .header .title h2 {
            margin: 5px 0;
            color: #16a34a; /* hijau */
            font-size: 18px;
        }
        .header .title p {
            margin: 2px 0;
            font-size: 12px;
            color: #166534; /* hijau gelap */
        }
        .info p {
            font-size: 14px;
            margin: 6px 0;
        }
        .status {
            margin-top: 20px;
            padding: 12px;
            border-radius: 8px;
            font-weight: bold;
            text-align: center;
            font-size: 14px;
        }
        .status.valid {
            background: #d1fae5; /* hijau muda */
            color: #065f46; /* hijau gelap */
            border: 2px solid #16a34a; /* hijau */
        }
        .status.invalid {
            background: #fee2e2; /* merah */
            color: #991b1b;
            border: 2px solid #ef4444;
        }

        /* Responsif untuk HP */
        @media screen and (max-width: 480px) {
            .container {
                padding: 15px;
            }
            .header img {
                max-width: 70px;
            }
            .header .title h2 {
                font-size: 16px;
            }
            .header .title p {
                font-size: 11px;
            }
            .info p {
                font-size: 13px;
            }
            .status {
                font-size: 13px;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <!-- Logo Dinas Kebudayaan -->
            <img src="{{ asset('images/logo-riau.png') }}" alt="Logo Dinas Kebudayaan">

            <!-- Judul Tengah -->
            <div class="title">
                <h2>Validasi Sertifikat Magang</h2>
                <p>Dinas Kebudayaan Provinsi Riau</p>
            </div>
        </div>

        <div class="info">
            <p><strong>Nama:</strong> {{ $pengajuan->mahasiswa->user->name }}</p>
            <p><strong>NIM:</strong> {{ $pengajuan->mahasiswa->nim }}</p>
            <p><strong>Bidang:</strong> {{ $pengajuan->bidang_diminati }}</p>
            <p><strong>Periode:</strong>
                {{ \Carbon\Carbon::parse($pengajuan->tanggal_mulai)->format('d F Y') }} -
                {{ \Carbon\Carbon::parse($pengajuan->tanggal_selesai)->format('d F Y') }}
            </p>
        </div>

        <div class="status {{ $pengajuan->status === 'selesai' ? 'valid' : 'invalid' }}">
            {{ $pengajuan->status === 'selesai' ? '✅ Sertifikat ini VALID' : '❌ Sertifikat ini TIDAK VALID' }}
        </div>
    </div>
</body>
</html>
