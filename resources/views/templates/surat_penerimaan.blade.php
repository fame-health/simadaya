<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 0.5cm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt; /* Slightly smaller font to fit everything */
            line-height: 1.3;
            color: #000;
            margin: 0;
            padding: 1cm 1.5cm;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 3px double #000;
            margin-bottom: 15px;
        }
        .header-table td {
            vertical-align: middle;
            padding-bottom: 8px;
        }
        .logo-left {
            width: 85px; /* Increased size */
            text-align: left;
        }
        .logo-right {
            width: 85px; /* Increased size */
            text-align: right;
        }
        .header-text {
            text-align: center;
        }
        .header-text p {
            margin: 0;
            padding: 0;
        }
        .title-1 {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .title-2 {
            font-size: 15pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 2px !important;
        }
        .subtitle {
            font-size: 9pt;
            font-style: italic;
        }
        .content {
            text-align: justify;
        }
        .date-section {
            text-align: right;
            margin-bottom: 10px;
        }
        .ref-section {
            margin-bottom: 10px;
        }
        .ref-section table {
            border: none;
            width: 100%;
        }
        .ref-section td {
            vertical-align: top;
            padding: 1px 0;
        }
        .recipient {
            margin-bottom: 15px;
        }
        .opening {
            margin-bottom: 10px;
        }
        .details-table {
            width: 100%;
            margin: 10px 0;
            border-collapse: collapse;
        }
        .details-table th, .details-table td {
            border: 1px solid #000; /* Added borders */
            padding: 6px 10px;
            vertical-align: middle;
        }
        .details-table th {
            background-color: #f2f2f2;
            text-align: left;
            width: 30%;
        }
        .signature-table {
            width: 100%;
            margin-top: 20px;
        }
        .signature-table td {
            width: 50%;
            text-align: center;
        }
        .signature-box {
            display: inline-block;
            text-align: left;
        }
        .qr-signature {
            width: 80px;
            height: 80px;
            margin: 5px 0;
        }
        p { margin: 5px 0; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td class="logo-left">
                <img src="{{ public_path('images/logo-provinsi.png') }}" width="80">
            </td>
            <td class="header-text">
                <p class="title-1">PEMERINTAH PROVINSI RIAU</p>
                <p class="title-2">DINAS KEBUDAYAAN</p>
                <p class="subtitle">Jl. Jend. Sudirman No. 123, Pekanbaru, Riau</p>
                <p class="subtitle">Telp: (0761) 123456 | Email: dinas.kebudayaan@riau.go.id</p>
            </td>
            <td class="logo-right">
                <img src="{{ public_path('images/logo-disbud.png') }}" width="80">
            </td>
        </tr>
    </table>

    <div class="date-section">
        Pekanbaru, {{ \Carbon\Carbon::parse($template->created_at)->translatedFormat('d F Y') }}
    </div>

    <div class="ref-section">
        <table>
            <tr>
                <td style="width: 80px;">Nomor</td>
                <td style="width: 10px;">:</td>
                <td>{{ $nomer_surat ?? '-' }}</td>
            </tr>
            <tr>
                <td>Lampiran</td>
                <td>:</td>
                <td>-</td>
            </tr>
            <tr>
                <td>Perihal</td>
                <td>:</td>
                <td><strong>Penerimaan Magang</strong></td>
            </tr>
        </table>
    </div>

    <div class="recipient">
        Kepada Yth:<br>
        <strong>{{ $mahasiswa_name }}</strong><br>
        NIM: {{ $nim }}<br>
        Di Tempat
    </div>

    <div class="content">
        <p class="opening">Dengan hormat,</p>
        <p>Bersama ini kami sampaikan bahwa pengajuan magang Anda telah <strong>DITERIMA</strong> untuk mengikuti program magang di Dinas Kebudayaan Provinsi Riau dengan rincian sebagai berikut:</p>

        <table class="details-table">
            <tr>
                <th>Bidang / Divisi</th>
                <td>{{ $bidang_diminati }}</td>
            </tr>
            <tr>
                <th>Periode Magang</th>
                <td>{{ $tanggal_mulai }} s.d {{ $tanggal_selesai }}</td>
            </tr>
            <tr>
                <th>Pembimbing Lapangan</th>
                <td>{{ $pembimbing_name }}</td>
            </tr>
        </table>

        <p>Demikian surat ini disampaikan, atas perhatian dan kerja samanya kami ucapkan terima kasih.</p>
    </div>

    <table class="signature-table">
        <tr>
            <td width="55%"></td>
            <td width="45%">
                <div class="signature-box">
                    Ditetapkan di: Pekanbaru<br>
                    Pada tanggal: {{ \Carbon\Carbon::parse($template->created_at)->translatedFormat('d F Y') }}<br>
                    <br>
                    Kepala Dinas Kebudayaan,<br>
                    <img src="{{ $qr_code_path }}" class="qr-signature"><br>
                    <strong><u>{{ $verified_by }}</u></strong>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
