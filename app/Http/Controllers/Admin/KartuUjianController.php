<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Rombel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KartuUjianController extends Controller
{
    public function index(Request $request)
    {
        $rombels = Rombel::with('tahunAjaran')
            ->withCount('siswa')
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->paginate(12);

        return Inertia::render('Admin/Akademik/KartuUjian/Index', [
            'rombelList' => $rombels,
        ]);
    }

    public function perRombel(Rombel $rombel)
    {
        $rombel->load('tahunAjaran');

        $siswa = $rombel->siswa()
            ->orderBy('nama')
            ->get();

        $kepalaSekolah = Guru::where('jabatan', 'Kepala Sekolah')->first();

        $pdf = Pdf::loadView('exports.kartu-rombel', compact('rombel', 'siswa', 'kepalaSekolah'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('kartu-ujian-rombel-' . $rombel->hashid . '.pdf');
    }
}
