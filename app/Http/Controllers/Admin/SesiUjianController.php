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
        $query = SesiUjian::with(['paketUjian.mataPelajaran', 'rombel', 'dibuatOleh'])
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
            'rombel_id' => 'nullable|exists:rombel,id',
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

        SesiUjian::create($validated);

        return back()->with('success', 'Sesi ujian berhasil ditambahkan.');
    }

    public function toggleStatus(Request $request, SesiUjian $sesi)
    {
        $request->validate(['status' => 'required|in:menunggu,berlangsung,selesai,dibatalkan']);
        $newStatus = $request->status;
        $sesi->update(['status' => $newStatus]);

        // Auto-generate peserta dari rombel saat sesi mulai berlangsung
        if ($newStatus === 'berlangsung' && $sesi->rombel_id) {
            $this->generatePesertaFromRombel($sesi);
        }

        return back()->with('success', "Status sesi berhasil diubah menjadi {$newStatus}.");
    }

    /**
     * Endpoint manual untuk generate/sync peserta dari rombel.
     */
    public function generatePeserta(SesiUjian $sesi)
    {
        if (!$sesi->rombel_id) {
            return back()->with('error', 'Sesi ini tidak memiliki rombel yang ditugaskan.');
        }

        $jumlah = $this->generatePesertaFromRombel($sesi);
        return back()->with('success', "{$jumlah} siswa berhasil didaftarkan sebagai peserta.");
    }

    /**
     * Generate peserta_ujian untuk semua siswa di rombel sesi ini.
     * Sudah ada tidak akan di-duplicate (firstOrCreate).
     */
    private function generatePesertaFromRombel(SesiUjian $sesi): int
    {
        $siswaList = \App\Models\Siswa::where('rombel_id', $sesi->rombel_id)->get();
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

    public function monitor(SesiUjian $sesi)
    {
        $sesi->load(['paketUjian.mataPelajaran', 'rombel']);
        
        $peserta = PesertaUjian::with('siswa')
            ->where('sesi_ujian_id', $sesi->id)
            ->get()
            ->map(function ($p) {
                // Calculate progress
                $total = count($p->urutan_soal ?? []);
                $answered = count($p->urutan_jawaban ? array_filter($p->urutan_jawaban) : []);
                
                // Fetch answers from JawabanSiswa to get actual answered count if needed, 
                // but for simplicity we assume urutan_jawaban is populated or we count JawabanSiswa records
                $jawabanTersimpan = \App\Models\JawabanSiswa::where('peserta_ujian_id', $p->id)
                    ->where(function($q) {
                        $q->whereNotNull('jawaban')->orWhereNotNull('jawaban_menjodohkan');
                    })->count();

                $p->progress = $total > 0 ? round(($jawabanTersimpan / $total) * 100) : 0;
                $p->jawaban_tersimpan = $jawabanTersimpan;
                $p->total_soal = $total;
                return $p;
            });

        return Inertia::render('Admin/Ujian/Sesi/Monitor', [
            'sesi' => $sesi,
            'peserta' => $peserta,
        ]);
    }
}
