<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengumuman;
use App\Services\Website\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class PengumumanController extends Controller
{
    public function __construct(protected FileUploadService $fileUploadService) {}

    public function index()
    {
        return Inertia::render('Admin/Web/Pengumuman/Index', [
            'pengumuman' => Pengumuman::with('author:id,name')->latest()->paginate(10),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Web/Pengumuman/Form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'judul'           => 'required|string|max:255',
            'konten'          => 'required|string',
            'prioritas'       => 'required|in:rendah,normal,tinggi',
            'tanggal_mulai'   => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'status'          => 'required|in:aktif,nonaktif',
            // Lampiran: opsional, hanya PDF, maks 10 MB
            'lampiran'        => 'nullable|file|extensions:pdf|max:10240',
        ]);

        // Upload PDF dan konversi ke HTML jika ada
        if ($request->hasFile('lampiran')) {
            try {
                $uploaded = $this->fileUploadService->uploadDocumentAndConvertToHtml(
                    $request->file('lampiran'),
                    'cms/documents/pengumuman'
                );
                $data['lampiran'] = $uploaded['path'];
            } catch (\InvalidArgumentException $e) {
                return back()->withErrors(['lampiran' => $e->getMessage()])->withInput();
            }
        } else {
            unset($data['lampiran']);
        }

        $data['created_by'] = auth()->id();
        Pengumuman::create($data);

        return redirect()->route('admin.web.pengumuman.index')
            ->with('success', 'Pengumuman berhasil disimpan.');
    }

    public function edit(Pengumuman $pengumuman)
    {
        return Inertia::render('Admin/Web/Pengumuman/Form', [
            'pengumuman'   => $pengumuman,
            'lampiran_url' => $pengumuman->lampiran
                ? route('admin.web.pengumuman.lampiran', $pengumuman->hashid)
                : null,
        ]);
    }

    public function update(Request $request, Pengumuman $pengumuman)
    {
        $data = $request->validate([
            'judul'           => 'required|string|max:255',
            'konten'          => 'required|string',
            'prioritas'       => 'required|in:rendah,normal,tinggi',
            'tanggal_mulai'   => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'status'          => 'required|in:aktif,nonaktif',
            'lampiran'        => 'nullable|file|extensions:pdf|max:10240',
            // Flag dari frontend untuk menghapus lampiran yang ada
            'hapus_lampiran'  => 'nullable|boolean',
        ]);

        // Hapus lampiran yang ada jika diminta
        if ($request->boolean('hapus_lampiran') && $pengumuman->lampiran) {
            $this->fileUploadService->delete($pengumuman->lampiran);
            $data['lampiran'] = null;
        }

        // Upload file baru dan konversi ke HTML jika ada
        if ($request->hasFile('lampiran')) {
            try {
                // Hapus file lama jika ada
                if ($pengumuman->lampiran) {
                    $this->fileUploadService->delete($pengumuman->lampiran);
                }
                $uploaded = $this->fileUploadService->uploadDocumentAndConvertToHtml(
                    $request->file('lampiran'),
                    'cms/documents/pengumuman'
                );
                $data['lampiran'] = $uploaded['path'];
            } catch (\InvalidArgumentException $e) {
                return back()->withErrors(['lampiran' => $e->getMessage()])->withInput();
            }
        } else {
            unset($data['lampiran']);
        }

        unset($data['hapus_lampiran']);
        $pengumuman->update($data);

        return redirect()->route('admin.web.pengumuman.index')
            ->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Pengumuman $pengumuman)
    {
        // Hapus file lampiran dari storage sebelum hapus record
        if ($pengumuman->lampiran) {
            $this->fileUploadService->delete($pengumuman->lampiran);
        }

        $pengumuman->delete();

        return back()->with('success', 'Pengumuman berhasil dihapus.');
    }

    /**
     * Serve HTML hasil konversi PDF lampiran di area admin (preview).
     * Hanya bisa diakses oleh user yang sudah login.
     */
    public function serveAdminLampiran(Pengumuman $pengumuman)
    {
        abort_unless($pengumuman->lampiran, 404, 'Halaman pengumuman tidak ditemukan.');

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
