<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Berita;
use App\Models\Pengumuman;
use App\Models\Pesan;
use App\Models\Galeri;
use Inertia\Inertia;

class DashboardWebController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Web/Dashboard', [
            'stats' => [
                'berita'      => Berita::count(),
                'pengumuman'  => Pengumuman::where('status','aktif')->count(),
                'pesan_baru'  => Pesan::where('status','belum_dibaca')->count(),
                'galeri'      => Galeri::count(),
            ],
            'berita_terbaru'     => Berita::latest()->take(5)->get(['id','judul','status','created_at']),
            'pengumuman_aktif'   => Pengumuman::where('status','aktif')->latest()->take(5)->get(['id','judul','prioritas','created_at']),
            'pesan_terbaru'      => Pesan::where('status','belum_dibaca')->latest()->take(5)->get(['id','nama','subjek','created_at']),
        ]);
    }
}
