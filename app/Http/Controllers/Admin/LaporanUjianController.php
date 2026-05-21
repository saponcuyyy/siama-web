<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{SesiUjian, PesertaUjian};
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanUjianController extends Controller
{
    public function index(Request $request)
    {
        $query = SesiUjian::with(['paketUjian.mataPelajaran', 'rombel'])
            ->withCount('peserta')
            ->whereIn('status', ['selesai', 'berlangsung'])
            ->latest('waktu_selesai');

        if ($request->search) {
            $query->where('nama_sesi', 'like', '%' . $request->search . '%');
        }

        return Inertia::render('Admin/Ujian/Laporan/Index', [
            'sesiList' => $query->paginate(15)->withQueryString(),
            'filters'  => $request->only('search'),
        ]);
    }

    public function perSesi(SesiUjian $sesi)
    {
        $sesi->load(['paketUjian.mataPelajaran', 'rombel']);
        
        $peserta = PesertaUjian::with('siswa')
            ->where('sesi_ujian_id', $sesi->id)
            ->orderByDesc('nilai_akhir')
            ->get();

        return Inertia::render('Admin/Ujian/Laporan/Sesi', [
            'sesi' => $sesi,
            'peserta' => $peserta,
        ]);
    }

    public function export(SesiUjian $sesi, $format)
    {
        $sesi->load(['paketUjian.mataPelajaran', 'rombel']);
        $peserta = PesertaUjian::with('siswa')
            ->where('sesi_ujian_id', $sesi->id)
            ->orderByDesc('nilai_akhir')
            ->get();

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('exports.laporan-ujian', compact('sesi', 'peserta'));
            return $pdf->download('Laporan-Ujian-'.$sesi->token.'.pdf');
        }

        // Jika excel, idealnya pakai Maatwebsite/Laravel-Excel
        // Untuk saat ini fallback back() atau implementasi manual csv
        abort(404, 'Format not supported yet');
    }
}
