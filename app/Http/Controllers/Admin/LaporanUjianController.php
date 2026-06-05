<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SesiStatus;
use App\Exports\RekapNilaiRombelExport;
use App\Http\Controllers\Controller;
use App\Models\PesertaUjian;
use App\Models\Rombel;
use App\Models\Semester;
use App\Models\SesiUjian;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class LaporanUjianController extends Controller
{
    private function guruMapelIds(): array
    {
        $user = Auth::user();
        if (! $user->hasRole('guru')) {
            return [];
        }

        return $user->guru?->mataPelajarans()->pluck('mata_pelajaran.id')->toArray() ?? [];
    }

    public function index(Request $request)
    {
        $query = SesiUjian::with(['paketUjian.mataPelajaran', 'rombel'])
            ->withCount('pesertaUjian')
            ->whereIn('status', [SesiStatus::SELESAI->value, SesiStatus::BERLANGSUNG->value])
            ->latest('waktu_selesai');

        $guruMapelIds = $this->guruMapelIds();
        if ($guruMapelIds) {
            $query->whereHas('paketUjian', fn ($q) => $q->whereIn('mata_pelajaran_id', $guruMapelIds));
        }

        if ($request->search) {
            $query->where('nama_sesi', 'like', '%'.$request->search.'%');
        }

        $rombelList = Rombel::orderBy('tingkat')->orderBy('nama')->get(['id', 'nama', 'tingkat']);
        $semesterList = Semester::orderBy('nama')->get(['id', 'nama', 'is_active']);

        return Inertia::render('Admin/Ujian/Laporan/Index', [
            'sesiList' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only('search'),
            'rombelList' => $rombelList,
            'semesterList' => $semesterList,
        ]);
    }

    public function perSesi(SesiUjian $sesi)
    {
        $sesi->load(['paketUjian.mataPelajaran', 'rombel']);

        $peserta = PesertaUjian::with('siswa.rombel')
            ->where('sesi_ujian_id', $sesi->id)
            ->orderByDesc('nilai_akhir')
            ->get();

        $perRombel = $peserta
            ->groupBy(fn ($p) => $p->siswa?->rombel?->nama ?? 'Tanpa Rombel')
            ->map(function ($group, $rombelName) {
                $scores = $group->pluck('nilai_akhir');

                return [
                    'rombel' => $rombelName,
                    'jumlah' => $group->count(),
                    'rata_rata' => round($scores->average() ?: 0, 2),
                    'tertinggi' => $scores->max() ?: 0,
                    'terendah' => $scores->min() ?: 0,
                    'lulus' => $scores->filter(fn ($v) => $v >= 75)->count(),
                    'tidak_lulus' => $scores->filter(fn ($v) => $v < 75)->count(),
                ];
            })
            ->values();

        return Inertia::render('Admin/Ujian/Laporan/Sesi', [
            'sesi' => $sesi,
            'peserta' => $peserta,
            'perRombel' => $perRombel,
        ]);
    }

    public function export(SesiUjian $sesi, $format)
    {
        $sesi->load(['paketUjian.mataPelajaran', 'rombel']);
        $peserta = PesertaUjian::with('siswa.rombel')
            ->where('sesi_ujian_id', $sesi->id)
            ->orderByDesc('nilai_akhir')
            ->get();

        $perRombel = $peserta
            ->groupBy(fn ($p) => $p->siswa?->rombel?->nama ?? 'Tanpa Rombel')
            ->map(function ($group, $rombelName) {
                $scores = $group->pluck('nilai_akhir');

                return [
                    'rombel' => $rombelName,
                    'jumlah' => $group->count(),
                    'rata_rata' => round($scores->average() ?: 0, 2),
                    'tertinggi' => $scores->max() ?: 0,
                    'terendah' => $scores->min() ?: 0,
                    'lulus' => $scores->filter(fn ($v) => $v >= 75)->count(),
                    'tidak_lulus' => $scores->filter(fn ($v) => $v < 75)->count(),
                ];
            })
            ->values();

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('exports.laporan-ujian', compact('sesi', 'peserta', 'perRombel'));

            return $pdf->download('Laporan-Ujian-'.$sesi->token.'.pdf');
        }

        // Jika excel, idealnya pakai Maatwebsite/Laravel-Excel
        // Untuk saat ini fallback back() atau implementasi manual csv
        abort(404, 'Format not supported yet');
    }

    public function rekapRombel(Request $request)
    {
        $request->validate([
            'rombel_id' => 'required|exists:rombel,id',
            'semester_id' => 'required|exists:semester,id',
        ]);

        $rombel = Rombel::findOrFail($request->rombel_id);
        $semester = Semester::findOrFail($request->semester_id);

        $filename = 'Rekap-Nilai-'.str_replace(' ', '-', $rombel->nama).'-'.str_replace(' ', '-', $semester->nama).'.xlsx';

        return Excel::download(new RekapNilaiRombelExport($rombel, $semester), $filename);
    }
}
