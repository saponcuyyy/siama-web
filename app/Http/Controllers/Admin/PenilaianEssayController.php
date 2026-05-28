<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{JawabanSiswa, SesiUjian, Soal};
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class PenilaianEssayController extends Controller
{
    public function index(Request $request)
    {
        // Cari jawaban essay yang belum dinilai
        $query = JawabanSiswa::with(['pesertaUjian.siswa', 'pesertaUjian.sesiUjian.paketUjian', 'soal.bankSoal'])
            ->whereHas('soal', function($q) {
                $q->where('tipe', 'essay');
            })
            ->whereNull('skor')
            ->latest();

        if ($request->search) {
            $query->whereHas('pesertaUjian.siswa', function($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%');
            });
        }

        return Inertia::render('Admin/Ujian/Penilaian/Index', [
            'jawabanList' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only('search')
        ]);
    }

    public function nilai(Request $request, JawabanSiswa $jawaban)
    {
        $jawaban->load('soal');
        
        $request->validate([
            'skor' => 'required|numeric|min:0|max:' . $jawaban->soal->bobot,
        ]);

        DB::transaction(function () use ($jawaban, $request) {
            $jawaban->update(['skor' => $request->skor]);

            $peserta = $jawaban->pesertaUjian;

            $totalSkor = JawabanSiswa::where('peserta_ujian_id', $peserta->id)
                ->whereNotNull('skor')
                ->sum('skor');
            $peserta->update(['nilai_akhir' => $totalSkor]);
        });

        return back()->with('success', 'Nilai essay berhasil disimpan.');
    }
}
