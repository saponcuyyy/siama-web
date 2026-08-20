<?php

namespace App\Http\Controllers\Admin\Akademik;

use App\Http\Controllers\Controller;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class MataPelajaranController extends Controller
{
    public function index(Request $request)
    {
        $query = MataPelajaran::with(['gurus' => function ($q) {
            $q->select('guru.id', 'guru.nama');
        }])
            ->withCount(['gurus as jumlah_guru', 'jadwal as jumlah_jam'])
            ->latest();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%')
                    ->orWhere('kode', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->tingkat) {
            $query->where('tingkat', $request->tingkat);
        }

        if ($request->jurusan) {
            $query->where('jurusan', $request->jurusan);
        }

        return Inertia::render('Admin/Akademik/MataPelajaran/Index', [
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
            'jam_per_minggu' => 'required|integer|min:1|max:40',
        ], [
            'nama.required' => 'Nama mata pelajaran wajib diisi.',
            'kode.required' => 'Kode mata pelajaran wajib diisi.',
            'tingkat.required' => 'Tingkat kelas wajib dipilih.',
            'tingkat.in' => 'Tingkat kelas tidak valid.',
            'jurusan.in' => 'Jurusan tidak valid.',
            'jam_per_minggu.required' => 'Jumlah jam per minggu wajib diisi.',
            'jam_per_minggu.integer' => 'Jumlah jam per minggu harus berupa angka.',
            'jam_per_minggu.min' => 'Jumlah jam per minggu minimal 1 jam.',
            'jam_per_minggu.max' => 'Jumlah jam per minggu maksimal 40 jam.',
        ]);

        MataPelajaran::create($validated);

        return back()->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function update(Request $request, MataPelajaran $mataPelajaran)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'kode' => 'required|string|max:20|unique:mata_pelajaran,kode,' . $mataPelajaran->id,
            'tingkat' => 'required|in:X,XI,XII',
            'jurusan' => 'nullable|in:IPA,IPS',
            'jam_per_minggu' => 'required|integer|min:1|max:40',
        ], [
            'nama.required' => 'Nama mata pelajaran wajib diisi.',
            'kode.required' => 'Kode mata pelajaran wajib diisi.',
            'kode.unique' => 'Kode mata pelajaran sudah digunakan.',
            'tingkat.required' => 'Tingkat kelas wajib dipilih.',
            'tingkat.in' => 'Tingkat kelas tidak valid.',
            'jurusan.in' => 'Jurusan tidak valid.',
            'jam_per_minggu.required' => 'Jumlah jam per minggu wajib diisi.',
            'jam_per_minggu.integer' => 'Jumlah jam per minggu harus berupa angka.',
            'jam_per_minggu.min' => 'Jumlah jam per minggu minimal 1 jam.',
            'jam_per_minggu.max' => 'Jumlah jam per minggu maksimal 40 jam.',
        ]);

        $mataPelajaran->update($validated);

        return back()->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(MataPelajaran $mataPelajaran)
    {
        $mataPelajaran->delete();

        return back()->with('success', 'Mata pelajaran berhasil dihapus.');
    }
}
