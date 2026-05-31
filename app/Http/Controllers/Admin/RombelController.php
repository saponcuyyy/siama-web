<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Rombel;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RombelController extends Controller
{
    public function index(Request $request)
    {
        $query = Rombel::with(['tahunAjaran', 'waliKelas'])
            ->withCount('siswa')
            ->latest();

        if ($request->search) {
            $query->where('nama', 'like', '%'.$request->search.'%');
        }

        if ($request->tahun_ajaran_id) {
            $query->where('tahun_ajaran_id', $request->tahun_ajaran_id);
        }

        return Inertia::render('Admin/Akademik/Rombel/Index', [
            'rombelList' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only(['search', 'tahun_ajaran_id']),
            'guruList' => Guru::select('id', 'nama', 'nip')->orderBy('nama')->get(),
            'tahunAjaranList' => TahunAjaran::select('id', 'nama', 'is_active')->orderByDesc('is_active')->orderByDesc('id')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'tingkat' => 'required|string|max:20',
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            'guru_id' => 'nullable|exists:guru,id',
        ]);

        Rombel::create($validated);

        return back()->with('success', 'Rombel berhasil ditambahkan.');
    }

    public function update(Request $request, Rombel $rombel)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'tingkat' => 'required|string|max:20',
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            'guru_id' => 'nullable|exists:guru,id',
        ]);

        $rombel->update($validated);

        return back()->with('success', 'Data rombel berhasil diperbarui.');
    }

    public function destroy(Rombel $rombel)
    {
        if ($rombel->siswa()->count() > 0) {
            return back()->with('error', 'Rombel tidak dapat dihapus karena masih memiliki siswa terdaftar.');
        }

        $rombel->delete();

        return back()->with('success', 'Rombel berhasil dihapus.');
    }
}
