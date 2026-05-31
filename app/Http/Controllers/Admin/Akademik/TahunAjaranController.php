<?php

namespace App\Http\Controllers\Admin\Akademik;

use App\Http\Controllers\Controller;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TahunAjaranController extends Controller
{
    public function index(Request $request)
    {
        $query = TahunAjaran::latest();

        if ($request->search) {
            $query->where('nama', 'like', '%'.$request->search.'%');
        }

        return Inertia::render('Admin/Akademik/TahunAjaran/Index', [
            'tahunAjaranList' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only('search'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:50|unique:tahun_ajaran,nama',
            'is_active' => 'boolean',
        ]);

        if ($validated['is_active'] ?? false) {
            TahunAjaran::where('is_active', true)->update(['is_active' => false]);
        }

        TahunAjaran::create($validated);

        return back()->with('success', 'Tahun ajaran berhasil ditambahkan.');
    }

    public function update(Request $request, TahunAjaran $tahunAjaran)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:50|unique:tahun_ajaran,nama,'.$tahunAjaran->id,
            'is_active' => 'boolean',
        ]);

        if ($validated['is_active'] ?? false) {
            TahunAjaran::where('id', '!=', $tahunAjaran->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

        $tahunAjaran->update($validated);

        return back()->with('success', 'Tahun ajaran berhasil diperbarui.');
    }

    public function destroy(TahunAjaran $tahunAjaran)
    {
        if ($tahunAjaran->rombel()->count() > 0) {
            return back()->with('error', 'Tahun ajaran tidak dapat dihapus karena masih memiliki rombel terdaftar.');
        }

        $tahunAjaran->delete();

        return back()->with('success', 'Tahun ajaran berhasil dihapus.');
    }
}
