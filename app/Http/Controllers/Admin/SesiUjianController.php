<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{SesiUjian, PaketUjian, Rombel, PesertaUjian};
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class SesiUjianController extends Controller
{
    public function index(Request $request)
    {
        $query = SesiUjian::with(['paketUjian.mataPelajaran', 'rombel', 'rombels', 'dibuatOleh'])
            ->latest();

        if ($request->search) {
            $query->where('nama_sesi', 'like', '%' . $request->search . '%');
        }

        return Inertia::render('Admin/Ujian/Sesi/Index', [
            'sesiList' => $query->paginate(15)->withQueryString(),
            'filters'  => $request->only('search'),
            'paketList' => PaketUjian::select('id', 'nama', 'kode')->get(),
            'rombelList' => Rombel::select('id', 'nama')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'paket_ujian_id' => 'required|exists:paket_ujian,id',
            'rombel_ids' => 'nullable|array',
            'rombel_ids.*' => 'exists:rombel,id',
            'nama_sesi' => 'required|string|max:255',
            'waktu_mulai' => 'required|date',
            'waktu_selesai' => 'required|date|after:waktu_mulai',
            'toleransi_menit' => 'required|integer|min:0',
            'max_pelanggaran' => 'required|integer|min:1',
            'wajib_fullscreen' => 'boolean',
            'catatan' => 'nullable|string',
        ]);

        $validated['token'] = strtoupper(\Illuminate\Support\Str::random(8));
        $validated['dibuat_oleh'] = Auth::id();
        $validated['status'] = 'menunggu';

        $sesi = SesiUjian::create($validated);

        if ($request->rombel_ids) {
            $sesi->rombels()->sync($request->rombel_ids);
        }

        return back()->with('success', 'Sesi ujian berhasil ditambahkan.');
    }

    public function toggleStatus(Request $request, SesiUjian $sesi)
    {
        $request->validate(['status' => 'required|in:menunggu,berlangsung,selesai,dibatalkan']);
        $newStatus = $request->status;
        $sesi->update(['status' => $newStatus]);

        // Auto-generate peserta dari rombel saat sesi mulai berlangsung
        if ($newStatus === 'berlangsung') {
            $this->generatePesertaFromRombel($sesi);
        }

        return back()->with('success', "Status sesi berhasil diubah menjadi {$newStatus}.");
    }

    /**
     * Endpoint manual untuk generate/sync peserta dari rombel.
     */
    public function generatePeserta(SesiUjian $sesi)
    {
        $jumlah = $this->generatePesertaFromRombel($sesi);

        if ($jumlah === 0 && !$this->getRombelIds($sesi)) {
            return back()->with('error', 'Sesi ini tidak memiliki rombel yang ditugaskan.');
        }

        return back()->with('success', "{$jumlah} siswa berhasil didaftarkan sebagai peserta.");
    }

    /**
     * Generate peserta_ujian untuk semua siswa di rombel sesi ini.
     * Sudah ada tidak akan di-duplicate (firstOrCreate).
     */
    private function generatePesertaFromRombel(SesiUjian $sesi): int
    {
        $rombelIds = $this->getRombelIds($sesi);
        if (empty($rombelIds)) return 0;

        $count = 0;

        \App\Models\Siswa::whereIn('rombel_id', $rombelIds)->chunk(100, function ($siswaList) use ($sesi, &$count) {
            foreach ($siswaList as $siswa) {
                $created = PesertaUjian::firstOrCreate(
                    ['sesi_ujian_id' => $sesi->id, 'siswa_id' => $siswa->id],
                    ['status' => 'belum_mulai']
                );
                if ($created->wasRecentlyCreated) $count++;
            }
        });

        return $count;
    }

    private function getRombelIds(SesiUjian $sesi): array
    {
        $rombelIds = [];

        $sesi->loadMissing('rombels');
        $rombels = $sesi->rombels;

        if ($rombels->isNotEmpty()) {
            $rombelIds = $rombels->pluck('id')->toArray();
        } elseif ($sesi->rombel_id) {
            $rombelIds = [$sesi->rombel_id];
        }

        return $rombelIds;
    }

    public function monitor(Request $request, SesiUjian $sesi)
    {
        $sesi->load(['paketUjian.mataPelajaran', 'rombel', 'rombels']);

        $perPage = (int) $request->input('per_page', 10);

        $peserta = PesertaUjian::with('siswa')
            ->withCount(['jawabanSiswa as jawaban_terisi' => function($q) {
                $q->whereNotNull('jawaban')->orWhereNotNull('jawaban_menjodohkan');
            }])
            ->where('sesi_ujian_id', $sesi->id)
            ->paginate($perPage)
            ->through(function ($p) {
                $total = count($p->urutan_soal ?? []);
                $p->progress = $total > 0 ? round(($p->jawaban_terisi / $total) * 100) : 0;
                $p->jawaban_tersimpan = $p->jawaban_terisi;
                $p->total_soal = $total;
                return $p;
            });

        $maxScore = Cache::remember('max_score_paket_' . $sesi->paket_ujian_id, 60, function () use ($sesi) {
            return $sesi->paketUjian->soal()->sum('bobot') ?: 100;
        });

        $stats = PesertaUjian::where('sesi_ujian_id', $sesi->id)
            ->selectRaw("COUNT(*) as total")
            ->selectRaw("SUM(CAST(status = 'mengerjakan' AS UNSIGNED)) as mengerjakan")
            ->selectRaw("SUM(CAST(status = 'selesai' AS UNSIGNED)) as selesai")
            ->selectRaw("SUM(CAST(status = 'didiskualifikasi' AS UNSIGNED)) as didiskualifikasi")
            ->selectRaw("ROUND(AVG(CASE WHEN status = 'selesai' AND nilai_akhir IS NOT NULL THEN nilai_akhir END), 2) as rata_nilai")
            ->first();

        return Inertia::render('Admin/Ujian/Sesi/Monitor', [
            'sesi'      => $sesi,
            'peserta'   => $peserta,
            'stats'     => $stats,
            'maxScore'  => $maxScore,
        ]);
    }

    public function kartuUjian(SesiUjian $sesi)
    {
        $sesi->load(['paketUjian.mataPelajaran', 'rombel', 'rombels']);

        $rombelIds = $this->getRombelIds($sesi);

        $peserta = PesertaUjian::with('siswa.rombel')
            ->where('sesi_ujian_id', $sesi->id)
            ->whereHas('siswa', fn($q) => $q->whereIn('rombel_id', $rombelIds))
            ->orderBy(
                PesertaUjian::select('nama')
                    ->from('siswa')
                    ->whereColumn('siswa.id', 'peserta_ujian.siswa_id')
                    ->limit(1)
            )
            ->get();

        $pdf = Pdf::loadView('exports.kartu-ujian', compact('sesi', 'peserta'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('kartu-ujian-' . $sesi->token . '.pdf');
    }
}
