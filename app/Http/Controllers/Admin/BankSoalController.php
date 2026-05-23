<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{BankSoal, MataPelajaran, Guru, TahunAjaran};
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class BankSoalController extends Controller
{
    public function index(Request $request)
    {
        $query = BankSoal::with(['mataPelajaran', 'guru'])
            ->withCount('soal')
            ->latest();

        if ($request->search) {
            $query->where('judul', 'like', '%' . $request->search . '%')
                  ->orWhereHas('mataPelajaran', function($q) use ($request) {
                      $q->where('nama', 'like', '%' . $request->search . '%');
                  });
        }

        return Inertia::render('Admin/Ujian/BankSoal/Index', [
            'bankSoalList' => $query->paginate(15)->withQueryString(),
            'filters'      => $request->only('search'),
            'mapelList'    => MataPelajaran::select('id', 'nama', 'kode', 'tingkat', 'jurusan')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'judul' => 'required|string|max:255',
            'tingkat' => 'required|in:X,XI,XII',
            'deskripsi' => 'nullable|string',
        ]);

        $validated['tahun_ajaran_id'] = TahunAjaran::where('is_active', true)->first()?->id;
        $validated['guru_id'] = Guru::where('user_id', Auth::id())->first()?->id ?? Guru::first()?->id;
        $validated['is_active'] = true;

        if (!$validated['tahun_ajaran_id']) {
            return back()->with('error', 'Tidak ada tahun ajaran aktif. Harap hubungi administrator.');
        }

        if (!$validated['guru_id']) {
            return back()->with('error', 'Data Guru tidak ditemukan. Silakan tambahkan data guru di menu Master Data terlebih dahulu.');
        }

        BankSoal::create($validated);

        return back()->with('success', 'Bank soal berhasil ditambahkan.');
    }

    public function show(BankSoal $bankSoal)
    {
        $bankSoal->load(['mataPelajaran', 'soal.pilihanJawaban', 'soal.pasanganMenjodohkan', 'guru']);
        
        return Inertia::render('Admin/Ujian/BankSoal/Show', [
            'bankSoal' => $bankSoal
        ]);
    }

    public function destroy(BankSoal $bankSoal)
    {
        $bankSoal->delete();

        return back()->with('success', 'Bank soal berhasil dihapus.');
    }
}
