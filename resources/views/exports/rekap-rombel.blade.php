<table>
    <thead>
        <tr>
            <th colspan="{{ 3 + count($mataPelajarans) }}" style="text-align: center; font-size: 14px; font-weight: bold;">
                REKAPITULASI NILAI UJIAN
            </th>
        </tr>
        <tr>
            <th colspan="{{ 3 + count($mataPelajarans) }}" style="text-align: center; font-weight: bold;">
                ROMBEL: {{ $rombel->nama }} | SEMESTER: {{ $semester->nama }}
            </th>
        </tr>
        <tr>
            <th colspan="{{ 3 + count($mataPelajarans) }}"></th>
        </tr>
        <tr>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000;">No</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000;">NIS/NISN</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000;">Nama Siswa</th>
            @foreach($mataPelajarans as $mapel)
                <th style="font-weight: bold; text-align: center; border: 1px solid #000;">{{ $mapel->nama }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($siswas as $index => $siswa)
            <tr>
                <td style="text-align: center; border: 1px solid #000;">{{ $index + 1 }}</td>
                <td style="text-align: center; border: 1px solid #000;">{{ $siswa->nisn ?? $siswa->nis }}</td>
                <td style="border: 1px solid #000;">{{ $siswa->nama }}</td>
                @foreach($mataPelajarans as $mapel)
                    @php
                        $nilai = $nilaiSiswa[$siswa->id][$mapel->id] ?? '-';
                    @endphp
                    <td style="text-align: center; border: 1px solid #000;">{{ $nilai }}</td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
