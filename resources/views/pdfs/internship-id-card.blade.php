<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 0; }

        body {
            margin: 0;
            padding: 0;
            font-family: sans-serif;
            font-size: 7.5px;
        }

        .card {
            width: 100%;
            padding: 6px;
            box-sizing: border-box;
        }

        /* ===== HEADER ===== */
        .header-container {
            width: 100%;
            text-align: center;
            margin-bottom: 4px;
            position: relative;
            padding-top: 4px;
        }

        .logo {
            width: 60px;
            height: auto;
            position: absolute;
            left: 30px;
            top: 0;
        }

        .header {
            font-size: 9px;
            font-weight: bold;
        }

        .header-sub {
            font-size: 6px;
            margin-top: 2px;
            line-height: 1.2;
        }

        /* Garis pemisah */
        .line {
            width: 100%;
            height: 0.5px;
            background: #000;
            margin: 6px 0 6px 0;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table td {
            padding: 4px;
            vertical-align: top;
        }

        .photo {
            width: 40px;
            height: 52px;
            border: 1px solid #bbb;
            object-fit: cover;
        }

        .label {
            font-weight: bold;
        }

        .qr img {
            width: 40px;
            height: 40px;
            display: block;
            margin: 0 auto;
        }

        .qr-text {
            font-size: 6px;
            line-height: 1.15;
            text-align: left;
        }
    </style>
</head>
<body>

<div class="card">

    <!-- HEADER DENGAN LOGO & ALAMAT -->
    <div class="header-container">
        <img class="logo" src="{{ public_path('images/logo-riau.png') }}">

        <div class="header">KARTU IDENTITAS MAGANG</div>

        <div class="header-sub">
            Jl. Jend. Sudirman No. 123, Pekanbaru, Riau<br>
            Telp: (0761) 123456 | Email: dinas.kebudayaan@riau.go.id
        </div>
    </div>

    <!-- Garis Pemisah -->
    <div class="line"></div>

    <table class="table">

        <!-- FOTO + DATA -->
        <tr>
            <td style="width: 50px;">
                <img src="{{ $profile_photo_path ?: public_path('default-photo.png') }}" class="photo">
            </td>

            <td>
                <table style="width:100%; font-size:6.5px; border-collapse:collapse;">
                    <tr><td class="label">Nama</td><td>: {{ $mahasiswa_name }}</td></tr>
                    <tr><td class="label">NIM</td><td>: {{ $nim }}</td></tr>
                    <tr><td class="label">Jurusan</td><td>: {{ $jurusan }}</td></tr>
                    <tr><td class="label">Univ</td><td>: {{ $universitas }}</td></tr>
                    <tr><td class="label">Mulai</td><td>: {{ $tanggal_mulai }}</td></tr>
                    <tr><td class="label">Selesai</td><td>: {{ $tanggal_selesai }}</td></tr>
                </table>
            </td>
        </tr>

        <!-- QR + PENJELASAN -->
        <tr>
            <td style="text-align:center; width: 30px; padding: 2px;">
                <div class="qr">
                    <img src="{{ $qr_code_path }}" style="width:29px; height:29px; margin:0 auto;">
                </div>
                <div style="font-size:3.3px; margin-top:2px;">
                    <b>QR Identitas</b>
                </div>
            </td>

            <td style="padding: 1px;">
                <div class="qr-text" style="font-size:5px; line-height:1.05;">
                    <b>Kegunaan QR:</b><br>
                    • Verifikasi identitas.<br>
                    • Dapat discan pembimbing lapangan.<br>
                    • Menampilkan data lengkap mahasiswa.<br>
                </div>
            </td>
        </tr>

    </table>

</div>

</body>
</html>
