<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Pengumuman;
use Illuminate\Support\Facades\Storage;

class PengumumanLampiranController extends Controller
{
    /**
     * Serve file PDF lampiran pengumuman secara aman ke publik.
     *
     * Security checklist:
     * ✅ Hanya pengumuman dengan status 'aktif' yang bisa diakses
     * ✅ File distream via proxy — URL storage tidak pernah diekspos ke client
     * ✅ Content-Type dikunci ke application/pdf
     * ✅ X-Content-Type-Options: nosniff mencegah MIME sniffing browser
     * ✅ Content-Disposition: inline (ditampilkan di browser, bukan didownload)
     * ✅ Tidak ada informasi path internal di response header
     */
    public function __invoke(Pengumuman $pengumuman)
    {
        // Hanya pengumuman aktif yang bisa diakses publik
        abort_unless($pengumuman->status === 'aktif', 404);
        abort_unless($pengumuman->lampiran, 404, 'Pengumuman ini tidak memiliki lampiran.');

        $disk = 'public';
        abort_unless(Storage::disk($disk)->exists($pengumuman->lampiran), 404, 'File lampiran tidak ditemukan.');

        $content  = Storage::disk($disk)->get($pengumuman->lampiran);
        $filename = 'Lampiran-'.str($pengumuman->judul)->slug().'.pdf';

        return response($content, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="'.$filename.'"')
            ->header('X-Content-Type-Options', 'nosniff')
            ->header('X-Frame-Options', 'SAMEORIGIN')
            ->header('Cache-Control', 'public, max-age=3600');
    }
}
