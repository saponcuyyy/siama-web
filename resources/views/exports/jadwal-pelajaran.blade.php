<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Jadwal Pelajaran - {{ $namaSekolah }}</title>
    <style>
        @page {
            margin: 12mm 15mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10.5px;
            color: #0f172a;
            line-height: 1.35;
        }
        .header {
            text-align: center;
            margin-bottom: 16px;
            border-bottom: 3px double #1e293b;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 19px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #0f172a;
        }
        .header h2 {
            margin: 3px 0 0 0;
            font-size: 13px;
            font-weight: 700;
            color: #334155;
        }
        .header p {
            margin: 3px 0 0 0;
            font-size: 10px;
            color: #64748b;
        }
        .table-container {
            margin-bottom: 22px;
            page-break-inside: avoid;
        }
        .rombel-title {
            font-size: 13px;
            font-weight: bold;
            color: #1e1b4b;
            background-color: #e0e7ff;
            padding: 5px 12px;
            border-radius: 5px;
            margin-bottom: 8px;
            border-left: 4px solid #4338ca;
        }
        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            table-layout: fixed;
        }
        .schedule-table th, .schedule-table td {
            border: 1px solid #cbd5e1;
            padding: 5px 4px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
        }
        .schedule-table th {
            background-color: #1e293b;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.5px;
        }
        .jam-column {
            background-color: #f8fafc;
            font-weight: bold;
            color: #334155;
            width: 55px;
        }
        .mapel-box {
            font-weight: 800;
            color: #0f172a;
            font-size: 10px;
        }
        .guru-box {
            font-size: 8.5px;
            color: #475569;
            margin-top: 2px;
            font-style: italic;
        }
        .empty-cell {
            color: #cbd5e1;
            font-size: 11px;
        }
        .footer-signatures {
            margin-top: 25px;
            width: 100%;
            page-break-inside: avoid;
        }
        .footer-signatures td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            font-size: 10.5px;
        }
        .signature-space {
            height: 50px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>{{ $namaSekolah }}</h1>
        <h2>JADWAL PELAJARAN TAHUN AJARAN {{ $tahunAjaran->nama ?? 'AKTA' }}</h2>
        <p>
            @if($selectedRombel)
                Target Kelas: <strong>{{ $selectedRombel->nama }}</strong> (Tingkat {{ $selectedRombel->tingkat }})
            @else
                Target: <strong>Semua Kelas & Rombongan Belajar</strong>
            @endif
            &bull; Tanggal Cetak: {{ date('d/m/Y H:i') }}
        </p>
    </div>

    @forelse($jadwalGrouped as $group)
        <div class="table-container">
            <div class="rombel-title">
                KELAS / ROMBEL: {{ $group['rombel']->nama ?? 'Rombel' }} (Tingkat {{ $group['rombel']->tingkat ?? '-' }})
            </div>

            <table class="schedule-table">
                <thead>
                    <tr>
                        <th class="jam-column">Jam Ke</th>
                        @foreach($hariList as $hari)
                            <th>{{ $hari }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @for($j = 1; $j <= $group['max_jam']; $j++)
                        <tr>
                            <td class="jam-column">Jam Ke-{{ $j }}</td>
                            @foreach($hariList as $hari)
                                @php
                                    $item = $group['grid'][$hari][$j] ?? null;
                                @endphp
                                <td>
                                    @if($item)
                                        <div class="mapel-box">{{ $item->mataPelajaran->nama ?? '-' }}</div>
                                        <div class="guru-box">{{ $item->guru->nama ?? '-' }}</div>
                                    @else
                                        <span class="empty-cell">-</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    @empty
        <div style="text-align:center; padding: 40px; color:#64748b;">
            <p>Belum ada data jadwal pelajaran untuk kriteria yang dipilih.</p>
        </div>
    @endforelse

    <table class="footer-signatures">
        <tr>
            <td>
                <p>Mengetahui,<br>Wali Kelas / Bidang Kurikulum</p>
                <div class="signature-space"></div>
                <p>__________________________<br><strong>NIP. .....................................</strong></p>
            </td>
            <td>
                <p>Mengetahui,<br>Kepala {{ $namaSekolah }}</p>
                <div class="signature-space"></div>
                <p>__________________________<br><strong>NIP. .....................................</strong></p>
            </td>
        </tr>
    </table>

</body>
</html>
