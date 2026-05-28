<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\Galeri;
use App\Services\Website\FileUploadService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class AlbumController extends Controller
{
    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    public function index()
    {
        return Inertia::render('Admin/Web/Album/Index', [
            'albums' => Album::withCount('galeri')->latest()->paginate(12)
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'status' => 'required|in:aktif,nonaktif',
            'cover' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('cover')) {
            $uploaded = $this->fileUploadService->uploadImage($request->file('cover'), 'albums');
            $validated['cover'] = $uploaded['path'];
        }

        Album::create($validated);

        return back()->with('success', 'Album berhasil dibuat.');
    }

    public function show(Request $request, Album $album)
    {
        return Inertia::render('Admin/Web/Album/Show', [
            'album' => $album,
            'photos' => $album->galeri()->latest()->paginate(20)->withQueryString()
        ]);
    }

    public function update(Request $request, Album $album)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'status' => 'required|in:aktif,nonaktif',
            'cover' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('cover')) {
            $this->fileUploadService->delete($album->cover);
            $uploaded = $this->fileUploadService->uploadImage($request->file('cover'), 'albums');
            $validated['cover'] = $uploaded['path'];
        }

        $album->update($validated);

        return back()->with('success', 'Album berhasil diperbarui.');
    }

    public function destroy(Album $album)
    {
        $album->delete();
        return back()->with('success', 'Album berhasil dihapus.');
    }

    public function uploadPhotos(Request $request, Album $album)
    {
        $request->validate([
            'photos' => 'required|array',
            'photos.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120', // 5MB max
        ]);

        foreach ($request->file('photos') as $photo) {
            $uploaded = $this->fileUploadService->uploadImage($photo, 'galeri');
            $album->galeri()->create([
                'judul' => $photo->getClientOriginalName(),
                'file_path' => $uploaded['path'],
                'status' => 'aktif',
                'created_by' => auth()->id(),
            ]);
        }

        return back()->with('success', 'Foto-foto berhasil diupload.');
    }
}
