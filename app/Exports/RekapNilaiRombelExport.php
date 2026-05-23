<?php

namespace App\Exports;

use App\Models\Rombel;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\PesertaUjian;
use App\Models\SesiUjian;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapNilaiRombelExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $rombel;
    protected $semester;

    public function __construct(Rombel $rombel, Semester $semester)
    {
        $this->rombel = $rombel;
        $this->semester = $semester;
    }

    public function view(): View
    {
        // 1. Get all students in the rombel
        $siswas = Siswa::where('rombel_id', $this->rombel->id)
            ->orderBy('nama')
            ->get();

        // 2. Get all SesiUjian for this rombel and semester
        $sesiUjians = SesiUjian::with('paketUjian.mataPelajaran')
            ->whereHas('paketUjian', function ($q) {
                $q->where('semester_id', $this->semester->id);
            })
            ->where(function ($q) {
                $q->where('rombel_id', $this->rombel->id)
                  ->orWhereHas('rombels', function ($q2) {
                      $q2->where('rombel_id', $this->rombel->id);
                  });
            })
            ->get();

        // 3. Extract unique MataPelajaran from those SesiUjian
        $mataPelajarans = [];
        $sesiIds = [];
        foreach ($sesiUjians as $sesi) {
            $sesiIds[] = $sesi->id;
            $mapel = $sesi->paketUjian->mataPelajaran;
            if ($mapel && !isset($mataPelajarans[$mapel->id])) {
                $mataPelajarans[$mapel->id] = $mapel;
            }
        }
        
        // Sort mapel by name
        usort($mataPelajarans, function($a, $b) {
            return strcmp($a->nama, $b->nama);
        });

        // 4. Get all relevant PesertaUjian
        // Build lookup: $nilaiSiswa[$siswa_id][$mapel_id] = nilai
        // Only include peserta that have been corrected (sudah_dikoreksi = true) and have a nilai
        $pesertas = PesertaUjian::with(['sesiUjian.paketUjian.mataPelajaran'])
            ->whereIn('sesi_ujian_id', $sesiIds)
            ->whereIn('siswa_id', $siswas->pluck('id'))
            ->where('sudah_dikoreksi', true)
            ->whereNotNull('nilai_akhir')
            ->get();

        // Log for debugging
        \Log::info('RekapNilaiRombelExport: Processing rombel ' . $this->rombel->id . 
            ', semester ' . $this->semester->id . 
            ', Found ' . $siswas->count() . ' siswa, ' . 
            $sesiUjians->count() . ' sesi, ' . 
            $pesertas->count() . ' corrected peserta with nilai');

        $nilaiSiswa = [];
        foreach ($pesertas as $p) {
            // Double-check we have all necessary data
            if (
                $p->sesiUjian && 
                $p->sesiUjian->paketUjian && 
                $p->sesiUjian->paketUjian->mataPelajaran && 
                !is_null($p->nilai_akhir)
            ) {
                $mapel = $p->sesiUjian->paketUjian->mataPelajaran;
                $mapelId = $mapel->id;
                $nilai = (float) $p->nilai_akhir; // Ensure it's a float
                
                // Log the data we're processing (first few only to avoid log spam)
                if (count($nilaiSiswa) < 5) {
                    \Log::debug('Processing nilai: siswa_id=' . $p->siswa_id . 
                        ', mapel_id=' . $mapelId . 
                        ', nilai=' . $nilai . 
                        ', sudah_dikoreksi=' . $p->sudah_dikoreksi);
                }
                
                // If there are multiple sessions for the same subject, we take the highest score
                if (!isset($nilaiSiswa[$p->siswa_id][$mapelId]) || $nilai > $nilaiSiswa[$p->siswa_id][$mapelId]) {
                    $nilaiSiswa[$p->siswa_id][$mapelId] = $nilai;
                    if (count($nilaiSiswa) < 5) {
                        \Log::debug('Set nilai: siswa ' . $p->siswa_id . ', mapel ' . $mapelId . ' = ' . $nilai);
                    }
                }
            } else {
                // Log what's missing for debugging (only first few)
                if (count($nilaiSiswa) < 5) {
                    \Log::warning('Skipping peserta ID ' . $p->id . ': ' .
                        ( ! $p->sesiUjian ? 'missing sesiUjian' : '' ) .
                        ( ! $p->sesiUjian->paketUjian ? ' missing paketUjian' : '' ) .
                        ( ! $p->sesiUjian->paketUjian->mataPelajaran ? ' missing mataPelajaran' : '' ) .
                        ( is_null($p->nilai_akhir) ? ' null nilai_akhir' : '' ) .
                        ', sudah_dikoreksi=' . ($p->sudah_dikoreksi ?? 'null'));
                }
            }
        }

        \Log::info('RekapNilaiRombelExport: Final nilaiSiswa has data for ' . count($nilaiSiswa) . ' students');
        if (count($nilaiSiswa) > 0) {
            // Log sample data structure
            $firstSiswaId = array_key_first($nilaiSiswa);
            $firstMapelId = array_key_first($nilaiSiswa[$firstSiswaId] ?? []);
            \Log::debug('Sample nilaiSiswa structure: [' . $firstSiswaId . '][' . $firstMapelId . '] = ' . 
                ($nilaiSiswa[$firstSiswaId][$firstMapelId] ?? 'not set'));
        }

        return view('exports.rekap-rombel', [
            'rombel' => $this->rombel,
            'semester' => $this->semester,
            'siswas' => $siswas,
            'mataPelajarans' => $mataPelajarans,
            'nilaiSiswa' => $nilaiSiswa,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true, 'size' => 14]],
            2    => ['font' => ['bold' => true]],
            3    => ['font' => ['bold' => true]],
            4    => ['font' => ['bold' => true]],
        ];
    }
}