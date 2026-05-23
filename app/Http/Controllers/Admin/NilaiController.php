<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MataPelajaran;
use App\Models\PesertaUjian;
use App\Models\Rombel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\NilaiExport;

class NilaiController extends Controller
{
    protected function getData($rombelId)
    {
        $mapels = MataPelajaran::whereHas('paketUjian.sesiUjian', function ($q) {
                $q->where('status', 'selesai');
            })
            ->orderBy('nama')
            ->get(['id', 'nama']);

        $rows = collect();

        if ($rombelId) {
            $pesertaBySiswa = PesertaUjian::with([
                    'siswa:id,nama,nisn,rombel_id',
                    'sesiUjian.paketUjian.mataPelajaran:id,nama',
                ])
                ->whereHas('siswa', fn($q) => $q->where('rombel_id', $rombelId))
                ->whereHas('sesiUjian', fn($q) => $q->where('status', 'selesai'))
                ->select('id', 'siswa_id', 'sesi_ujian_id', 'nilai_pg', 'nilai_bs', 'nilai_menjodohkan', 'nilai_essay', 'nilai_akhir')
                ->get()
                ->groupBy('siswa_id');

            $rows = $pesertaBySiswa->map(function ($items) use ($mapels) {
                $siswa = $items->first()->siswa;

                $perMapel = $items->groupBy(fn($p) => $p->sesiUjian->paketUjian->mataPelajaran->nama)
                    ->map(fn($group) => $group->sortByDesc('id')->first());

                $nilai = $mapels->mapWithKeys(fn($m) => [
                    $m->id => $perMapel->get($m->nama)?->nilai_akhir,
                ]);

                $total = $nilai->filter(fn($v) => $v !== null)->sum();
                $count = $nilai->filter(fn($v) => $v !== null)->count();
                $rata = $count > 0 ? round($total / $count, 2) : null;

                return [
                    'siswa_id' => $siswa->id,
                    'nama'     => $siswa->nama,
                    'nisn'     => $siswa->nisn,
                    'nilai'    => $nilai,
                    'rata_rata' => $rata,
                ];
            })->values();
        }

        return [$mapels, $rows];
    }

    public function index(Request $request)
    {
        $rombelId = $request->input('rombel_id');

        $rombels = Rombel::orderBy('tingkat')->orderBy('nama')->get(['id', 'nama', 'tingkat']);

        [$mapels, $rows] = $this->getData($rombelId);

        return Inertia::render('Admin/Ujian/Nilai/Index', [
            'rombels' => $rombels,
            'mapels'  => $mapels,
            'rows'    => $rows,
            'filters' => ['rombel_id' => $rombelId],
        ]);
    }

    public function export(Request $request)
    {
        $rombelId = $request->input('rombel_id');
        $rombel = Rombel::findOrFail($rombelId);

        [$mapels, $rows] = $this->getData($rombelId);

        $filename = 'Nilai_' . str_replace(' ', '_', $rombel->tingkat . '_' . $rombel->nama) . '.xlsx';

        return Excel::download(new NilaiExport($rombel, $mapels, $rows), $filename);
    }
}
