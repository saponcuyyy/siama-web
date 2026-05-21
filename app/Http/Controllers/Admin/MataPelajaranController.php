<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MataPelajaranController extends Controller
{
    public function index(Request $request)
    {
        $query = MataPelajaran::withCount(['bankSoal', 'paketUjian'])
            ->latest();

        if ($request->search) {
            $query->where('nama', 'like', '%' . $request->search . '%')
                  ->orWhere('kode', 'like', '%' . $request->search . '%');
        }

        return Inertia::render('Admin/Ujian/MataPelajaran/Index', [
            'mapelList' => $query->paginate(15)->withQueryString(),
            'filters'   => $request->only('search'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'kode' => 'required|string|max:20|unique:mata_pelajaran,kode',
        ], [
            'nama.required' => 'Nama mata pelajaran wajib diisi.',
            'kode.required' => 'Kode mata pelajaran wajib diisi.',
            'kode.unique'   => 'Kode ini sudah digunakan oleh mata pelajaran lain.',
        ]);

        MataPelajaran::create($validated);

        return back()->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function update(Request $request, MataPelajaran $mataPelajaran)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'kode' => 'required|string|max:20|unique:mata_pelajaran,kode,' . $mataPelajaran->id,
        ], [
            'nama.required' => 'Nama mata pelajaran wajib diisi.',
            'kode.required' => 'Kode mata pelajaran wajib diisi.',
            'kode.unique'   => 'Kode ini sudah digunakan oleh mata pelajaran lain.',
        ]);

        $mataPelajaran->update($validated);

        return back()->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(MataPelajaran $mataPelajaran)
    {
        // Soft delete — data tetap ada tapi tersembunyi
        $mataPelajaran->delete();

        return back()->with('success', 'Mata pelajaran berhasil dihapus.');
    }
}
