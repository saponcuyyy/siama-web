<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JawabanSiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PenilaianEssayController extends Controller
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
        // Cari jawaban essay yang belum dinilai
        $query = JawabanSiswa::with(['pesertaUjian.siswa', 'pesertaUjian.sesiUjian.paketUjian', 'soal.bankSoal'])
            ->whereHas('soal', function ($q) {
                $q->where('tipe', 'essay');
            })
            ->whereNull('skor')
            ->latest();

        $guruMapelIds = $this->guruMapelIds();
        if ($guruMapelIds) {
            $query->whereHas('pesertaUjian.sesiUjian.paketUjian', fn ($q) => $q->whereIn('mata_pelajaran_id', $guruMapelIds));
        }

        if ($request->search) {
            $query->whereHas('pesertaUjian.siswa', function ($q) use ($request) {
                $q->where('nama', 'like', '%'.$request->search.'%');
            });
        }

        return Inertia::render('Admin/Ujian/Penilaian/Index', [
            'jawabanList' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only('search'),
        ]);
    }

    public function nilai(Request $request, JawabanSiswa $jawaban)
    {
        $jawaban->load(['soal', 'pesertaUjian']);

        $request->validate([
            'skor' => 'required|numeric|min:0|max:'.$jawaban->soal->bobot,
        ]);

        DB::transaction(function () use ($jawaban, $request) {
            $jawaban->update(['skor' => $request->skor]);

            $peserta = $jawaban->pesertaUjian;

            $totalSkorEssay = JawabanSiswa::where('peserta_ujian_id', $peserta->id)
                ->whereNotNull('skor')
                ->sum('skor');

            $nilaiAkhir = ($peserta->nilai_pg ?? 0)
                + ($peserta->nilai_bs ?? 0)
                + ($peserta->nilai_menjodohkan ?? 0)
                + $totalSkorEssay;

            $peserta->update(['nilai_akhir' => $nilaiAkhir]);
        });

        return back()->with('success', 'Nilai essay berhasil disimpan.');
    }
}
