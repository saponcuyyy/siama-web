<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kartu Ujian - {{ $rombel->nama }}</title>
    <style>
        @page {
            margin: 10mm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #000;
            background: #fff;
        }
        table {
            border-collapse: collapse;
        }
        .page-break {
            page-break-after: always;
        }
        .card {
            border: 1px solid #000;
            border-radius: 5px;
            padding: 5px;
            background-color: #fff;
            margin-bottom: 2px;
        }
        .kop-table {
            border-bottom: 2px double #000;
            margin-bottom: 4px;
            padding-bottom: 2px;
            width: 100%;
        }
        .kop-table td {
            vertical-align: middle;
        }
        .kop-text {
            text-align: center;
        }
        .kop-text .title {
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .kop-text .school {
            font-size: 13px;
            font-weight: bold;
            margin: 2px 0;
            text-transform: uppercase;
        }
        .kop-text .address {
            font-size: 8px;
        }
        .content-table {
            width: 100%;
        }
        .content-table td {
            padding: 2px 0;
            vertical-align: top;
            font-size: 10px;
        }
        .content-table .label {
            width: 25%;
            font-weight: bold;
        }
        .content-table .colon {
            width: 5%;
            text-align: center;
        }
        .content-table .value {
            width: 70%;
            font-weight: bold;
        }
        .footer-table {
            margin-top: 3px;
            width: 100%;
        }
        .footer-table td {
            font-size: 9px;
            text-align: center;
            vertical-align: bottom;
        }
    </style>
</head>
<body>

    @php
        // Encode gambar logo SEKALI saja di luar loop agar tidak lambat
        $tutwuriPath = public_path('images/tutwuri-small.png');
        $logoPath    = public_path('images/logo-small.png');
        $tutwuriSrc  = file_exists($tutwuriPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($tutwuriPath))
            : '';
        $logoSrc     = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : '';
        $bulan = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
                  7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
        $tgl   = date('d') . ' ' . $bulan[(int)date('m')] . ' ' . date('Y');
    @endphp

    @foreach($siswa->chunk(8) as $chunk)
        <table width="100%" style="margin-bottom: 0;">
            @foreach($chunk->chunk(2) as $row)
                <tr>
                    @foreach($row as $s)
                        <td width="48%" valign="top">
                            <div class="card">
                                <!-- KOP -->
                                <table class="kop-table">
                                    <tr>
                                        <td width="15%" style="text-align: left;">
                                            @if($tutwuriSrc)
                                                <img src="{{ $tutwuriSrc }}" width="35" alt="Tut Wuri">
                                            @endif
                                        </td>
                                        <td width="70%" class="kop-text">
                                            <div class="title">KARTU PESERTA UJIAN</div>
                                            <div class="school">SMA Negeri 2 Perbaungan</div>
                                            <div class="address">Jln. Cempaka No. 25 Perbaungan Kab. Serdang Bedagai Provinsi Sumatera Utara</div>
                                        </td>
                                        <td width="15%" style="text-align: right;">
                                            @if($logoSrc)
                                                <img src="{{ $logoSrc }}" width="35" alt="Logo">
                                            @endif
                                        </td>
                                    </tr>
                                </table>

                                <!-- CONTENT -->
                                <table width="100%">
                                    <tr>
                                        <td width="25%" style="padding-top: 3px; vertical-align: top;">
                                            <div style="width: 1.8cm; height: 2.4cm; border: 1px dashed #666; text-align: center; font-size: 9px; color: #666; line-height: 2.4cm;">
                                                Pas Foto
                                            </div>
                                        </td>
                                        <td width="75%" style="padding-left: 8px; vertical-align: top;">
                                            <table class="content-table">
                                                <tr>
                                                    <td class="label">Nama</td>
                                                    <td class="colon">:</td>
                                                    <td class="value">{{ $s->nama ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="label">NISN</td>
                                                    <td class="colon">:</td>
                                                    <td class="value">{{ $s->nisn ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="label">Rombel</td>
                                                    <td class="colon">:</td>
                                                    <td class="value">{{ $rombel->nama ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="label">User</td>
                                                    <td class="colon">:</td>
                                                    <td class="value" style="font-family: monospace; font-size: 11px;">{{ $s->nisn ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="label">Pass</td>
                                                    <td class="colon">:</td>
                                                    <td class="value" style="font-family: monospace; font-size: 11px; color: #dc2626;">
                                                        {{ $s->tanggal_lahir ? date('dmY', strtotime($s->tanggal_lahir)) . '*' : '********' }}
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>

                                <!-- FOOTER -->
                                <table class="footer-table">
                                    <tr>
                                        <td width="50%" style="text-align: left; vertical-align: bottom;">
                                            <div style="font-weight: bold; font-size: 9px; background: #1e293b; color: #fff; padding: 3px 5px; display: inline-block; border-radius: 2px;">
                                                sman2perbaungan.sch.id/ujian
                                            </div>
                                        </td>
                                        <td width="50%">
                                            Perbaungan, {{ $tgl }}<br>
                                            Kepala Sekolah<br>
                                            <br><br>
                                            <strong>{{ $kepalaSekolah?->nama ?? '.............................................' }}</strong><br>
                                            NIP. {{ $kepalaSekolah?->nip ?? '' }}
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                        @if($loop->iteration == 1 && count($row) == 1)
                            <td width="4%"></td>
                            <td width="48%"></td>
                        @elseif($loop->iteration == 1)
                            <td width="4%"></td>
                        @endif
                    @endforeach
                </tr>
                <tr><td colspan="3" height="3"></td></tr>
            @endforeach
        </table>

        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach

</body>
</html>
