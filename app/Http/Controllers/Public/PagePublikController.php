<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Setting;
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
            'settings' => Setting::pluck('value', 'key'),
        ]);
    }
}
