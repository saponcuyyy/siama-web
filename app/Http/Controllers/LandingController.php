<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Berita;
use App\Models\Fasilitas;
use App\Models\Guru;
use App\Models\Menu;
use App\Models\Page;
use App\Models\Pengumuman;
use App\Models\Setting;
use App\Models\Siswa;
use App\Models\Slider;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class LandingController extends Controller
{
    public function index()
    {
        return Inertia::render('Landing', Cache::remember('landing_page', 300, function () {
            return [
                'sliders' => Slider::where('status', 'aktif')->orderBy('urutan')->limit(10)->get(),
                'berita' => Berita::with('kategori')->where('status', 'published')->latest()->take(6)->get(),
                'pengumuman' => Pengumuman::with('author')
                    ->where('status', 'aktif')
                    ->where(function ($q) {
                        $q->whereNull('tanggal_selesai')
                            ->orWhere('tanggal_selesai', '>=', now());
                    })
                    ->latest()->take(5)->get(),
                'fasilitas' => Fasilitas::where('status', 'aktif')->orderBy('urutan')->limit(10)->get(),
                'albums' => Album::withCount('galeri')->where('status', 'aktif')->latest()->take(8)->get(),
                'about' => Page::where('slug', 'tentang-sekolah')->first(),
                'visiMisi' => Page::where('slug', 'visi-misi')->first(),
                'menus' => Menu::with('children')->whereNull('parent_id')->where('status', 'aktif')->orderBy('urutan')->get(),
                'settings' => Setting::pluck('value', 'key'),
                'total_siswa' => Siswa::count(),
                'total_guru' => Guru::count(),
                'total_fasilitas' => Fasilitas::where('status', 'aktif')->count(),
            ];
        }));
    }
}
