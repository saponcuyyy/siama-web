<table>
    <thead>
        <tr>
            <th colspan="{{ 3 + count($mapels) }}" style="text-align: center; font-size: 14px; font-weight: bold;">
                DAFTAR NILAI SISWA
            </th>
        </tr>
        <tr>
            <th colspan="{{ 3 + count($mapels) }}" style="text-align: center; font-weight: bold;">
                ROMBEL: {{ $rombel->tingkat }} {{ $rombel->nama }}
            </th>
        </tr>
        <tr>
            <th colspan="{{ 3 + count($mapels) }}"></th>
        </tr>
        <tr>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000; width: 40px;">No</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000; width: 120px;">NISN</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000; width: 200px;">Nama Siswa</th>
            @foreach($mapels as $mapel)
                <th style="font-weight: bold; text-align: center; border: 1px solid #000;">{{ $mapel['nama'] ?? $mapel->nama }}</th>
            @endforeach
            <th style="font-weight: bold; text-align: center; border: 1px solid #000; width: 80px;">Rata-rata</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $index => $row)
            <tr>
                <td style="text-align: center; border: 1px solid #000;">{{ $index + 1 }}</td>
                <td style="text-align: center; border: 1px solid #000;">{{ $row['nisn'] }}</td>
                <td style="border: 1px solid #000;">{{ $row['nama'] }}</td>
                @foreach($mapels as $mapel)
                    @php
                        $mapelId = $mapel['id'] ?? $mapel->id;
                        $nilai = $row['nilai'][$mapelId] ?? '-';
                    @endphp
                    <td style="text-align: center; border: 1px solid #000;{{ $nilai !== '-' && $nilai < 75 ? ' color: #dc2626;' : '' }}">{{ $nilai }}</td>
                @endforeach
                <td style="text-align: center; border: 1px solid #000; font-weight: bold;">{{ $row['rata_rata'] ?? '-' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
