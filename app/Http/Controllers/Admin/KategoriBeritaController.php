<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KategoriBerita;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class KategoriBeritaController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Web/KategoriBerita/Index', [
            'kategori' => KategoriBerita::latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255|unique:kategori_berita,nama',
        ]);

        KategoriBerita::create([
            'nama' => $request->nama,
            'slug' => Str::slug($request->nama),
        ]);

        return back()->with('success', 'Kategori berita berhasil ditambahkan.');
    }

    public function update(Request $request, KategoriBerita $kategoriBeritum)
    {
        $request->validate([
            'nama' => 'required|string|max:255|unique:kategori_berita,nama,'.$kategoriBeritum->id,
        ]);

        $kategoriBeritum->update([
            'nama' => $request->nama,
            'slug' => Str::slug($request->nama),
        ]);

        return back()->with('success', 'Kategori berita berhasil diperbarui.');
    }

    public function destroy(KategoriBerita $kategoriBeritum)
    {
        if ($kategoriBeritum->berita()->count() > 0) {
            return back()->with('error', 'Kategori tidak bisa dihapus karena masih memiliki berita.');
        }

        $kategoriBeritum->delete();

        return back()->with('success', 'Kategori berita berhasil dihapus.');
    }
}
