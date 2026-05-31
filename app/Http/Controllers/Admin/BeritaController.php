<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Berita;
use App\Models\KategoriBerita;
use App\Services\Website\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class BeritaController extends Controller
{
    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    public function index()
    {
        return Inertia::render('Admin/Web/Berita/Index', [
            'berita' => Berita::with(['kategori', 'author:id,name'])
                ->latest()
                ->paginate(10),
            'kategori' => KategoriBerita::all(['id', 'nama']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Web/Berita/Form', [
            'kategori' => KategoriBerita::all(['id', 'nama']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategori_berita,id',
            'ringkasan' => 'required|string|max:500',
            'konten' => 'required|string',
            'status' => 'required|in:published,draft,archived',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'tags' => 'nullable|array',
            'published_at' => 'nullable|date',
        ]);

        $validated['slug'] = Str::slug($validated['judul']);
        $validated['created_by'] = auth()->id();

        if ($request->hasFile('thumbnail')) {
            $uploaded = $this->fileUploadService->uploadImage($request->file('thumbnail'), 'berita');
            $validated['thumbnail'] = $uploaded['path'];
        }

        if ($validated['status'] === 'published' && ! $validated['published_at']) {
            $validated['published_at'] = now();
        }

        Berita::create($validated);

        return redirect()->route('admin.web.berita.index')->with('success', 'Berita berhasil diterbitkan.');
    }

    public function edit(Berita $beritum)
    {
        return Inertia::render('Admin/Web/Berita/Form', [
            'berita' => $beritum,
            'kategori' => KategoriBerita::all(['id', 'nama']),
        ]);
    }

    public function update(Request $request, Berita $beritum)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategori_berita,id',
            'ringkasan' => 'required|string|max:500',
            'konten' => 'required|string',
            'status' => 'required|in:published,draft,archived',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'tags' => 'nullable|array',
            'published_at' => 'nullable|date',
        ]);

        $validated['slug'] = Str::slug($validated['judul']);

        if ($request->hasFile('thumbnail')) {
            $this->fileUploadService->delete($beritum->thumbnail);
            $uploaded = $this->fileUploadService->uploadImage($request->file('thumbnail'), 'berita');
            $validated['thumbnail'] = $uploaded['path'];
        }

        if ($validated['status'] === 'published' && ! $beritum->published_at && ! $validated['published_at']) {
            $validated['published_at'] = now();
        }

        $beritum->update($validated);

        return redirect()->route('admin.web.berita.index')->with('success', 'Berita berhasil diperbarui.');
    }

    public function destroy(Berita $beritum)
    {
        // Thumbnail is kept in case of restore from soft delete
        $beritum->delete();

        return back()->with('success', 'Berita berhasil dipindahkan ke tempat sampah.');
    }
}
