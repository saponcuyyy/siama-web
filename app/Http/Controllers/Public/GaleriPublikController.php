<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\Setting;
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
            'settings' => Setting::pluck('value', 'key'),
        ]);
    }

    public function show(Album $album)
    {
        abort_if($album->status !== 'aktif', 404);

        return Inertia::render('Public/Galeri/Show', [
            'album'  => $album,
            'photos' => $album->galeri()->where('status', 'aktif')->latest()->get(),
            'settings' => Setting::pluck('value', 'key'),
        ]);
    }
}
