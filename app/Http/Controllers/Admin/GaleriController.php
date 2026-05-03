<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Galeri;
use App\Services\Website\FileUploadService;
use Illuminate\Http\Request;

class GaleriController extends Controller
{
    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Update caption/judul foto.
     */
    public function update(Request $request, Galeri $galeri)
    {
        $validated = $request->validate([
            'judul'  => 'nullable|string|max:255',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        $galeri->update($validated);

        return back()->with('success', 'Foto berhasil diperbarui.');
    }

    /**
     * Hapus foto dari database dan MinIO.
     */
    public function destroy(Galeri $galeri)
    {
        // Hapus file dari MinIO
        $this->fileUploadService->delete($galeri->file_path);

        // Hapus dari database (soft delete)
        $galeri->delete();

        return back()->with('success', 'Foto berhasil dihapus.');
    }
}
