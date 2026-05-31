<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class GaleriPublikController extends Controller
{
    public function index()
    {
        return Inertia::render('Public/Galeri/Index', [
            'albums' => Album::withCount('galeri')
                ->where('status', 'aktif')
                ->latest()
                ->paginate(12),
            'settings' => Cache::remember('settings', 3600, fn () => Setting::pluck('value', 'key')),
        ]);
    }

    public function show(Album $album)
    {
        abort_if($album->status !== 'aktif', 404);

        return Inertia::render('Public/Galeri/Show', [
            'album' => $album,
            'photos' => $album->galeri()->where('status', 'aktif')->latest()->simplePaginate(20),
            'settings' => Cache::remember('settings', 3600, fn () => Setting::pluck('value', 'key')),
        ]);
    }
}
