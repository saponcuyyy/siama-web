<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fasilitas;
use App\Services\Website\FileUploadService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FasilitasController extends Controller
{
    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    public function index()
    {
        return Inertia::render('Admin/Web/Fasilitas/Index', [
            'fasilitas' => Fasilitas::orderBy('urutan')->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'urutan' => 'nullable|integer',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        if ($request->hasFile('foto')) {
            $uploaded = $this->fileUploadService->uploadImage($request->file('foto'), 'fasilitas');
            $validated['foto'] = $uploaded['path'];
        }

        Fasilitas::create($validated);

        return back()->with('success', 'Fasilitas berhasil ditambahkan.');
    }

    public function update(Request $request, Fasilitas $fasilita)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'urutan' => 'nullable|integer',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        if ($request->hasFile('foto')) {
            $this->fileUploadService->delete($fasilita->foto);
            $uploaded = $this->fileUploadService->uploadImage($request->file('foto'), 'fasilitas');
            $validated['foto'] = $uploaded['path'];
        }

        $fasilita->update($validated);

        return back()->with('success', 'Fasilitas berhasil diperbarui.');
    }

    public function destroy(Fasilitas $fasilita)
    {
        // Hapus file dari MinIO sebelum hapus record
        if ($fasilita->foto) {
            $this->fileUploadService->delete($fasilita->foto);
        }
        $fasilita->delete();
        return back()->with('success', 'Fasilitas berhasil dihapus.');
    }

    public function updateOrder(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:fasilitas,id',
        ]);

        foreach ($request->ids as $index => $id) {
            Fasilitas::where('id', $id)->update(['urutan' => $index]);
        }

        return back()->with('success', 'Urutan fasilitas diperbarui.');
    }
}
