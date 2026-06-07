<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Pengumuman;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
            'settings' => Cache::remember('settings', 3600, fn () => Setting::pluck('value', 'key')),
        ]);
    }

    public function baca(Pengumuman $pengumuman)
    {
        abort_unless($pengumuman->status === 'aktif', 404);
        if ($pengumuman->tanggal_selesai && $pengumuman->tanggal_selesai->isPast()) {
            abort(404);
        }

        abort_unless($pengumuman->lampiran, 404, 'Halaman pengumuman tidak ditemukan.');

        $tables = [];
        if ($pengumuman->lampiran) {
            $tablesPath = (string) Str::of($pengumuman->lampiran)->replace('.html', '.json');
            if (Storage::disk('public')->exists($tablesPath)) {
                $content = Storage::disk('public')->get($tablesPath);
                $decoded = json_decode($content, true);
                if (is_array($decoded)) {
                    $tables = $decoded;
                }
            }
        }

        return Inertia::render('Public/Pengumuman/Baca', [
            'pengumuman' => $pengumuman->load('author:id,name'),
            'settings'   => Cache::remember('settings', 3600, fn () => Setting::pluck('value', 'key')),
            'tables'     => $tables,
        ]);
    }

    public function pdf(Pengumuman $pengumuman)
    {
        abort_unless($pengumuman->status === 'aktif', 404);
        if ($pengumuman->tanggal_selesai && $pengumuman->tanggal_selesai->isPast()) {
            abort(404);
        }

        abort_unless($pengumuman->lampiran, 404, 'File pengumuman tidak ditemukan.');

        $pdfPath = (string) Str::of($pengumuman->lampiran)->replace('.html', '.pdf');
        $disk = 'public';
        abort_unless(Storage::disk($disk)->exists($pdfPath), 404, 'File tidak ditemukan.');

        return Storage::disk($disk)->response($pdfPath, null, [
            'Content-Type' => 'application/pdf',
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    public function embed(Pengumuman $pengumuman)
    {
        abort_unless($pengumuman->status === 'aktif', 404);
        if ($pengumuman->tanggal_selesai && $pengumuman->tanggal_selesai->isPast()) {
            abort(404);
        }

        abort_unless($pengumuman->lampiran, 404, 'File pengumuman tidak ditemukan.');

        $disk = 'public';
        abort_unless(Storage::disk($disk)->exists($pengumuman->lampiran), 404, 'File tidak ditemukan.');

        $content = Storage::disk($disk)->get($pengumuman->lampiran);

        return response($content, 200)
            ->header('Content-Type', 'text/html')
            ->header('X-Content-Type-Options', 'nosniff')
            ->header('X-Frame-Options', 'SAMEORIGIN')
            ->header('Content-Security-Policy', "default-src 'self' 'unsafe-inline' data:; script-src 'none';")
            ->header('Cache-Control', 'private, max-age=3600');
    }
}
