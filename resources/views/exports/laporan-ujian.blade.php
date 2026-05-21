<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Ujian - {{ $sesi->nama_sesi }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 4px 0; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .data-table th, .data-table td { border: 1px solid #000; padding: 8px; text-align: left; }
        .data-table th { background-color: #f3f4f6; }
        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }
    </style>
</head>
<body>

    <div class="header">
        <h2>Laporan Hasil Ujian (CBT)</h2>
        <p>Sistem Akademik SIAMA</p>
    </div>

    <table class="info-table">
        <tr>
            <td width="20%"><strong>Sesi Ujian</strong></td>
            <td width="30%">: {{ $sesi->nama_sesi }}</td>
            <td width="20%"><strong>Mata Pelajaran</strong></td>
            <td width="30%">: {{ $sesi->paketUjian->mataPelajaran->nama ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Paket Ujian</strong></td>
            <td>: {{ $sesi->paketUjian->nama ?? '-' }}</td>
            <td><strong>Rombel</strong></td>
            <td>: {{ $sesi->rombel->nama ?? 'Semua' }}</td>
        </tr>
        <tr>
            <td><strong>Selesai</strong></td>
            <td>: {{ \Carbon\Carbon::parse($sesi->waktu_selesai)->format('d M Y H:i') }}</td>
            <td><strong>Jml Peserta</strong></td>
            <td>: {{ count($peserta) }} Siswa</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th width="35%">Nama Siswa</th>
                <th width="20%">NISN</th>
                <th width="20%">Rombel</th>
                <th width="20%" class="text-right">Nilai Akhir</th>
            </tr>
        </thead>
        <tbody>
            @forelse($peserta as $index => $p)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $p->siswa->nama ?? '-' }}</td>
                <td>{{ $p->siswa->nisn ?? '-' }}</td>
                <td>{{ $p->siswa->rombel->nama ?? '-' }}</td>
                <td class="text-right"><strong>{{ $p->nilai_akhir ?? 0 }}</strong></td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">Belum ada peserta yang dinilai.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
