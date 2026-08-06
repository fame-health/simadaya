<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 0;
            size: A4 landscape;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 0;
            padding: 0;
            background-color: #fff;
        }
        .outer-border {
            width: 100%;
            height: 100%;
            padding: 20px;
            box-sizing: border-box;
        }
        .inner-border {
            border: 5px double #d4a017;
            height: 100%;
            padding: 35px;
            box-sizing: border-box;
            background-color: #fffef7;
        }
        .header-table {
            width: 100%;
            border-bottom: 3px solid #15803d;
            margin-bottom: 15px;
        }
        .header-table td {
            vertical-align: middle;
            padding-bottom: 10px;
        }
        .header-text {
            text-align: center;
        }
        .header-text h1 {
            font-size: 18pt;
            color: #1e3a8a;
            margin: 0;
            text-transform: uppercase;
        }
        .header-text h2 {
            font-size: 16pt;
            color: #1e3a8a;
            margin: 5px 0;
            text-transform: uppercase;
        }
        .header-text p {
            font-size: 10pt;
            margin: 0;
            color: #333;
        }
        .cert-body {
            text-align: center;
        }
        .cert-number {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .cert-title {
            font-size: 38pt;
            font-weight: bold;
            color: #15803d;
            margin-bottom: 15px;
            letter-spacing: 2px;
        }
        .given-to {
            font-size: 14pt;
            margin-bottom: 10px;
        }
        .student-name {
            font-size: 28pt;
            font-weight: bold;
            color: #1e3a8a;
            margin: 15px 0;
            text-decoration: underline;
            text-transform: uppercase;
        }
        .student-nim {
            font-size: 13pt;
            margin-bottom: 20px;
        }
        .desc-text {
            font-size: 13pt;
            line-height: 1.6;
            margin-bottom: 25px;
        }
        .signature-table {
            width: 100%;
            margin-top: 20px;
        }
        .signature-table td {
            text-align: center;
            vertical-align: top;
        }
        .qr-code {
            width: 100px;
            height: 100px;
            margin: 10px 0;
            border: 0.5px solid #ccc;
        }
        .sign-name {
            font-size: 12pt;
            font-weight: bold;
            text-decoration: underline;
        }
        .footer-note {
            font-size: 8pt;
            font-style: italic;
            color: #666;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="outer-border">
        <div class="inner-border">
            <table class="header-table">
                <tr>
                    <td width="90">
                        <img src="{{ public_path('images/logo-provinsi.png') }}" width="80">
                    </td>
                    <td class="header-text">
                        <h1>PEMERINTAH PROVINSI RIAU</h1>
                        <h2>DINAS KEBUDAYAAN</h2>
                        <p>Jl. Jend. Sudirman No. 123, Pekanbaru, Riau</p>
                        <p>Telp: (0761) 123456 | Email: dinas.kebudayaan@riau.go.id</p>
                    </td>
                    <td width="90">
                        <img src="{{ public_path('images/logo-disbud.png') }}" width="80">
                    </td>
                </tr>
            </table>

            <div class="cert-body">
                <div class="cert-number">
                    Nomor: {{ $nomer_surat ?? '070/DISBUD/'.date('Y').'/---' }}
                </div>

                <div class="cert-title">SERTIFIKAT MAGANG</div>

                <div class="given-to">Diberikan kepada:</div>

                <div class="student-name">{{ $mahasiswa_name }}</div>

                <div class="student-nim">NIM: {{ $nim }}</div>

                <div class="desc-text">
                    Telah menyelesaikan program magang di Dinas Kebudayaan Provinsi Riau pada bidang<br>
                    <strong>{{ $bidang_diminati }}</strong> selama periode <strong>{{ $tanggal_mulai }}</strong> s.d <strong>{{ $tanggal_selesai }}</strong><br>
                    dengan hasil yang sangat memuaskan.
                </div>

                <table class="signature-table">
                    <tr>
                        <td width="50%">
                            <div style="font-size: 10pt;">
                                Sertifikat ini diterbitkan secara elektronik<br>
                                dan dapat divalidasi melalui QR Code berikut:
                            </div>
                            <img src="{{ $qr_code_path }}" class="qr-code">
                        </td>
                        <td width="50%">
                            <div style="font-size: 11pt;">
                                Pekanbaru, {{ $tanggal_verifikasi }}<br>
                                Kepala Dinas Kebudayaan,<br>
                                <br><br><br><br>
                                <div class="sign-name">{{ $verified_by }}</div>
                            </div>
                        </td>
                    </tr>
                </table>

                <div class="footer-note">
                    * Dokumen ini sah dan telah ditandatangani secara elektronik.
                </div>
            </div>
        </div>
    </div>
</body>
</html>
