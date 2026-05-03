<?php

namespace App\Http\Controllers;

use App\Models\Berita;
use App\Models\Pengumuman;
use App\Models\Slider;
use App\Models\Fasilitas;
use App\Models\Album;
use App\Models\Setting;
use App\Models\Menu;
use App\Models\Page;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LandingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key');
        
        return Inertia::render('Landing', [
            'sliders' => Slider::where('status', 'aktif')->orderBy('urutan')->get(),
            'berita' => Berita::with('kategori')->where('status', 'published')->latest()->take(6)->get(),
            'pengumuman' => Pengumuman::where('status', 'aktif')
                ->where(function($q) {
                    $q->whereNull('tanggal_selesai')
                      ->orWhere('tanggal_selesai', '>=', now());
                })
                ->latest()->take(5)->get(),
            'fasilitas' => Fasilitas::where('status', 'aktif')->orderBy('urutan')->get(),
            'albums' => Album::withCount('galeri')->where('status', 'aktif')->latest()->take(8)->get(),
            'about' => Page::where('slug', 'tentang-sekolah')->first(),
            'visiMisi' => Page::where('slug', 'visi-misi')->first(),
            'menus' => Menu::with('children')->whereNull('parent_id')->where('status', 'aktif')->orderBy('urutan')->get(),
            'settings' => $settings
        ]);
    }
}
