<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Berita;
use App\Models\KategoriBerita;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class BeritaPublikController extends Controller
{
    public function index()
    {
        return Inertia::render('Public/Berita/Index', [
            'berita' => Berita::with(['kategori', 'author:id,name'])
                ->where('status', 'published')
                ->latest('published_at')
                ->paginate(9),
            'kategori' => KategoriBerita::withCount(['berita' => fn ($q) => $q->where('status', 'published')])->get(),
            'settings' => Cache::remember('settings', 3600, fn () => Setting::pluck('value', 'key')),
        ]);
    }

    public function show($slug)
    {
        $berita = Berita::with(['kategori', 'author:id,name'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $related = Berita::with('kategori')
            ->where('status', 'published')
            ->where('kategori_id', $berita->kategori_id)
            ->where('id', '!=', $berita->id)
            ->latest('published_at')
            ->take(3)
            ->get();

        return Inertia::render('Public/Berita/Show', [
            'berita' => $berita,
            'related' => $related,
            'settings' => Cache::remember('settings', 3600, fn () => Setting::pluck('value', 'key')),
        ]);
    }
}
