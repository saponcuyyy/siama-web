<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kartu Ujian - {{ $sesi->nama_sesi }}</title>
    <style>
        @page {
            margin: 15mm 10mm;
        }
        body {
            font-family: 'Courier New', monospace;
            font-size: 11px;
        }
        .page-header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 3px double #1e293b;
        }
        .page-header .school-name {
            font-size: 16px;
            font-weight: bold;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .page-header .school-ident {
            font-size: 9px;
            color: #64748b;
            margin-top: 2px;
        }
        .page-header .title {
            font-size: 14px;
            font-weight: bold;
            margin-top: 8px;
            color: #1e293b;
            border-top: 1px solid #cbd5e1;
            padding-top: 8px;
        }
        .card-container {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: center;
        }
        .card {
            width: 48%;
            border: 1.5px solid #1e293b;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 8px;
            page-break-inside: avoid;
            box-sizing: border-box;
        }
        .card-header {
            text-align: center;
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
            border-bottom: 1px dashed #94a3b8;
            padding-bottom: 5px;
            margin-bottom: 8px;
            color: #1e293b;
        }
        .card-body {
            display: flex;
            gap: 10px;
        }
        .card-info {
            flex: 1;
        }
        .card-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .card-info td {
            padding: 2px 0;
            font-size: 10px;
        }
        .card-info .label {
            font-weight: bold;
            color: #475569;
            width: 35%;
            vertical-align: top;
        }
        .card-info .value {
            color: #1e293b;
        }
        .card-info .highlight {
            font-weight: bold;
            color: #dc2626;
            font-size: 11px;
        }
        .card-info .exam-link {
            background: #1e293b;
            color: #fff;
            padding: 2px 6px;
            border-radius: 2px;
            font-size: 9px;
            display: inline-block;
            margin-top: 2px;
        }
        .photo-placeholder {
            width: 80px;
            height: 100px;
            border: 1.5px dashed #94a3b8;
            border-radius: 4px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 7px;
            color: #94a3b8;
            text-align: center;
            background: #f8fafc;
        }
        .card-footer {
            margin-top: 6px;
            padding-top: 5px;
            border-top: 1px dashed #cbd5e1;
            font-size: 7px;
            color: #94a3b8;
            text-align: center;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>

    <div class="page-header">
        <div class="school-name">SMA Negeri 2 Perbaungan</div>
        <div class="school-ident">Jl. Pendidikan No. 123, Perbaungan, Sumatera Utara</div>
        <div class="title">KARTU UJIAN</div>
        <div style="font-size:10px;margin-top:3px;color:#475569;">
            {{ $sesi->paketUjian->mataPelajaran->nama ?? '-' }}
            &bull; {{ $sesi->nama_sesi }}
        </div>
    </div>

    @foreach($peserta as $index => $p)
    <div class="card">
        <div class="card-header">
            {{ $p->siswa->rombel->nama ?? 'Umum' }}
        </div>
        <div class="card-body">
            <div class="card-info">
                <table>
                    <tr>
                        <td class="label">Nama</td>
                        <td class="value">: <strong>{{ $p->siswa->nama ?? '-' }}</strong></td>
                    </tr>
                    <tr>
                        <td class="label">NISN</td>
                        <td class="value">: {{ $p->siswa->nisn ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Kelas</td>
                        <td class="value">: {{ $p->siswa->rombel->nama ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Username</td>
                        <td class="value highlight">: {{ $p->siswa->nisn ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Password</td>
                        <td class="value">: <em>sesuai tanggal lahir *</em></td>
                    </tr>
                    <tr>
                        <td class="label">Link Ujian</td>
                        <td class="value">: <span class="exam-link">https://sman2perbaungan.sch.id/ujian</span></td>
                    </tr>
                </table>
            </div>
            <div class="photo-placeholder">
                FOTO<br>SISWA
            </div>
        </div>
        <div class="card-footer">
            Kartu ini berlaku untuk sesi {{ $sesi->nama_sesi }} - {{ \Carbon\Carbon::parse($sesi->waktu_mulai)->format('d M Y H:i') }}
        </div>
    </div>
    @endforeach

</body>
</html>
