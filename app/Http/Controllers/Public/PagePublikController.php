<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class PagePublikController extends Controller
{
    public function show($slug)
    {
        $page = Page::where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        return Inertia::render('Public/Page', [
            'page' => $page,
            'settings' => Cache::remember('settings', 3600, fn () => Setting::pluck('value', 'key')),
        ]);
    }
}
