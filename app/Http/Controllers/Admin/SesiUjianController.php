<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{SesiUjian, PaketUjian, Rombel, PesertaUjian};
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class SesiUjianController extends Controller
{
    public function index(Request $request)
    {
        \Illuminate\Support\Facades\Artisan::call('sesi:start-active');
        \Illuminate\Support\Facades\Artisan::call('sesi:close-expired');

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

        $siswaList = \App\Models\Siswa::whereIn('rombel_id', $rombelIds)->get();
        $count = 0;

        foreach ($siswaList as $siswa) {
            $created = PesertaUjian::firstOrCreate(
                ['sesi_ujian_id' => $sesi->id, 'siswa_id' => $siswa->id],
                ['status' => 'belum_mulai']
            );
            if ($created->wasRecentlyCreated) $count++;
        }

        return $count;
    }

    private function getRombelIds(SesiUjian $sesi): array
    {
        if ($sesi->rombels()->count() > 0) {
            return $sesi->rombels()->pluck('rombel.id')->toArray();
        }

        if ($sesi->rombel_id) {
            return [$sesi->rombel_id];
        }

        return [];
    }

    public function monitor(Request $request, SesiUjian $sesi)
    {
        $sesi->load(['paketUjian.mataPelajaran', 'rombel', 'rombels']);

        $perPage = (int) $request->input('per_page', 10);

        $peserta = PesertaUjian::with('siswa')
            ->where('sesi_ujian_id', $sesi->id)
            ->paginate($perPage)
            ->through(function ($p) {
                $total = count($p->urutan_soal ?? []);
                $jawabanTersimpan = \App\Models\JawabanSiswa::where('peserta_ujian_id', $p->id)
                    ->where(function($q) {
                        $q->whereNotNull('jawaban')->orWhereNotNull('jawaban_menjodohkan');
                    })->count();

                $p->progress = $total > 0 ? round(($jawabanTersimpan / $total) * 100) : 0;
                $p->jawaban_tersimpan = $jawabanTersimpan;
                $p->total_soal = $total;
                return $p;
            });

        $maxScore = $sesi->paketUjian->soal()->sum('bobot') ?: 100;

        $stats = [
            'total'           => PesertaUjian::where('sesi_ujian_id', $sesi->id)->count(),
            'mengerjakan'     => PesertaUjian::where('sesi_ujian_id', $sesi->id)->where('status', 'mengerjakan')->count(),
            'selesai'         => PesertaUjian::where('sesi_ujian_id', $sesi->id)->where('status', 'selesai')->count(),
            'didiskualifikasi'=> PesertaUjian::where('sesi_ujian_id', $sesi->id)->where('status', 'didiskualifikasi')->count(),
            'rata_nilai'      => round(PesertaUjian::where('sesi_ujian_id', $sesi->id)
                ->where('status', 'selesai')
                ->whereNotNull('nilai_akhir')
                ->avg('nilai_akhir') ?? 0, 2),
        ];

        return Inertia::render('Admin/Ujian/Sesi/Monitor', [
            'sesi'      => $sesi,
            'peserta'   => $peserta,
            'stats'     => $stats,
            'maxScore'  => $maxScore,
        ]);
    }
}
