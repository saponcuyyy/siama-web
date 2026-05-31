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
            $query->where(fn ($q) => $q->where('nama', 'like', '%'.$request->search.'%')
                ->orWhere('kode', 'like', '%'.$request->search.'%')
            );
        }

        if ($request->tingkat) {
            $query->where('tingkat', $request->tingkat);
        }

        if ($request->jurusan) {
            $query->where('jurusan', $request->jurusan);
        }

        return Inertia::render('Admin/Ujian/MataPelajaran/Index', [
            'mapelList' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only('search', 'tingkat', 'jurusan'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'kode' => 'required|string|max:20',
            'tingkat' => 'required|in:X,XI,XII',
            'jurusan' => 'nullable|in:IPA,IPS',
        ], [
            'nama.required' => 'Nama mata pelajaran wajib diisi.',
            'kode.required' => 'Kode mata pelajaran wajib diisi.',
            'tingkat.required' => 'Tingkat kelas wajib dipilih.',
            'tingkat.in' => 'Tingkat kelas tidak valid.',
            'jurusan.in' => 'Jurusan tidak valid.',
        ]);

        MataPelajaran::create($validated);

        return back()->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function update(Request $request, MataPelajaran $mataPelajaran)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'kode' => 'required|string|max:20|unique:mata_pelajaran,kode,'.$mataPelajaran->id,
            'tingkat' => 'required|in:X,XI,XII',
            'jurusan' => 'nullable|in:IPA,IPS',
        ], [
            'nama.required' => 'Nama mata pelajaran wajib diisi.',
            'kode.required' => 'Kode mata pelajaran wajib diisi.',
            'kode.unique' => 'Kode mata pelajaran sudah digunakan.',
            'tingkat.required' => 'Tingkat kelas wajib dipilih.',
            'tingkat.in' => 'Tingkat kelas tidak valid.',
            'jurusan.in' => 'Jurusan tidak valid.',
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
