<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Pengumuman;
use App\Models\Setting;
use Inertia\Inertia;

class PengumumanPublikController extends Controller
{
    public function index()
    {
        return Inertia::render('Public/Pengumuman/Index', [
            'pengumuman' => Pengumuman::with('author:id,name')
                ->where('status', 'aktif')
                ->where(function ($q) {
                    $q->whereNull('tanggal_selesai')
                      ->orWhere('tanggal_selesai', '>=', now());
                })
                ->latest()
                ->paginate(10),
            'settings' => Setting::pluck('value', 'key'),
        ]);
    }
}
