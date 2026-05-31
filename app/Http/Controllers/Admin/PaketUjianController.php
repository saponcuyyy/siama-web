<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Guru, PaketUjian, MataPelajaran, Soal, TahunAjaran, Semester};
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaketUjianController extends Controller
{
    public function index(Request $request)
    {
        $query = PaketUjian::with(['mataPelajaran', 'dibuatOleh'])
            ->withCount('soal')
            ->latest();

        if ($request->search) {
            $query->where('nama', 'like', '%' . $request->search . '%')
                  ->orWhere('kode', 'like', '%' . $request->search . '%');
        }

        return Inertia::render('Admin/Ujian/Paket/Index', [
            'paketList' => $query->paginate(15)->withQueryString(),
            'filters'  => $request->only('search'),
            'mapelList' => MataPelajaran::select('id', 'nama', 'kode', 'tingkat', 'jurusan')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'kode' => 'required|string|max:50|unique:paket_ujian,kode',
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'durasi_menit' => 'required|integer|min:1',
            'jenis' => 'required|in:uh,uts,uas,pas,try_out,lainnya',
            'tingkat' => 'required|in:X,XI,XII',
            'acak_soal' => 'boolean',
            'acak_jawaban' => 'boolean',
        ]);

        $guru = Guru::where('user_id', Auth::id())->first();
        $tahunAjaran = TahunAjaran::where('is_active', true)->first();
        $semester = Semester::where('is_active', true)->first();

        $validated['guru_id'] = $guru?->id;
        $validated['tahun_ajaran_id'] = $tahunAjaran?->id;
        $validated['semester_id'] = $semester?->id;
        $validated['dibuat_oleh'] = Auth::id();
        $validated['status'] = 'draft';

        PaketUjian::create($validated);

        return back()->with('success', 'Paket ujian berhasil ditambahkan.');
    }

    public function show(PaketUjian $paket)
    {
        $paket->load(['mataPelajaran', 'soal.pilihanJawaban', 'soal.bankSoal']);
        
        // Soal yang tersedia dari mapel yang sama, yang belum ada di paket ini
        $existingSoalIds = $paket->soal->pluck('id')->toArray();
        $availableSoal = Soal::with('bankSoal')
            ->whereHas('bankSoal', function($q) use ($paket) {
                $q->where('mata_pelajaran_id', $paket->mata_pelajaran_id);
            })
            ->whereNotIn('id', $existingSoalIds)
            ->get();

        return Inertia::render('Admin/Ujian/Paket/Show', [
            'paket' => $paket,
            'availableSoal' => $availableSoal
        ]);
    }

    public function tambahSoal(Request $request, PaketUjian $paket)
    {
        $request->validate([
            'soal_ids' => 'nullable|array',
            'soal_ids.*' => 'exists:soal,id',
            'bank_soal_id' => 'nullable|exists:bank_soal,id',
        ]);

        if ($request->bank_soal_id) {
            $existingSoalIds = $paket->soal()->pluck('soal.id')->toArray();
            $soalIds = Soal::where('bank_soal_id', $request->bank_soal_id)
                ->whereNotIn('id', $existingSoalIds)
                ->pluck('id')
                ->toArray();
        } else {
            $soalIds = $request->soal_ids ?? [];
        }

        if (empty($soalIds)) {
            return back()->with('error', 'Tidak ada soal yang dapat ditambahkan.');
        }

        $currentMaxUrutan = DB::table('paket_soal')
            ->where('paket_ujian_id', $paket->id)
            ->max('urutan') ?? 0;

        $attachData = [];
        foreach ($soalIds as $index => $soalId) {
            $attachData[$soalId] = ['urutan' => $currentMaxUrutan + $index + 1];
        }

        $paket->soal()->syncWithoutDetaching($attachData);

        return back()->with('success', count($soalIds) . ' soal berhasil ditambahkan ke paket.');
    }

    public function hapusSoal(PaketUjian $paket, Soal $soal)
    {
        $paket->soal()->detach($soal->id);
        return back()->with('success', 'Soal berhasil dihapus dari paket.');
    }
}
