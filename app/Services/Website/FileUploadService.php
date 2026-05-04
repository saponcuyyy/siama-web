<?php

namespace App\Services\Website;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class FileUploadService
{
    // Disk yang digunakan — ambil dari config, default: minio
    protected string $disk;

    public function __construct()
    {
        $this->disk = 'public';
    }

    /**
     * Upload gambar ke MinIO dengan validasi ketat
     * Return: URL publik file yang bisa langsung dipakai di <img src="">
     */
    public function uploadImage(
        UploadedFile $file,
        string $folder = 'cms/images',
        int $maxWidth = 1920,
        ?int $thumbWidth = 400
    ): array {
        // 1. Validasi MIME dari konten file (bukan hanya ekstensi)
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $detectedMime = $file->getMimeType();

        if (!in_array($detectedMime, $allowedMimes)) {
            throw new \InvalidArgumentException(
                "Tipe file tidak diizinkan: {$detectedMime}"
            );
        }

        // 2. Generate nama file UUID
        $extension = $file->getClientOriginalExtension();
        $filename  = Str::uuid() . '.' . strtolower($extension);
        $path      = $folder . '/' . $filename;

        // 3. Resize gambar jika lebar melebihi maxWidth
        $image = Image::read($file->getRealPath())
                      ->scaleDown(width: $maxWidth);

        // 4. Upload ke MinIO
        Storage::disk($this->disk)->put(
            $path,
            $image->toJpeg(quality: 85),
            'public'
        );

        $result = ['path' => $path, 'url' => $this->getUrl($path)];

        // 5. Generate thumbnail jika diminta
        if ($thumbWidth) {
            $thumbFilename = Str::uuid() . '_thumb.' . strtolower($extension);
            $thumbPath     = $folder . '/thumbnails/' . $thumbFilename;

            $thumb = Image::read($file->getRealPath())
                          ->scaleDown(width: $thumbWidth);

            Storage::disk($this->disk)->put(
                $thumbPath,
                $thumb->toJpeg(quality: 75),
                'public'
            );

            $result['thumbnail_path'] = $thumbPath;
            $result['thumbnail_url']  = $this->getUrl($thumbPath);
        }

        return $result;
    }

    /**
     * Upload file dokumen (PDF) ke MinIO
     */
    public function uploadDocument(
        UploadedFile $file,
        string $folder = 'cms/documents'
    ): array {
        // Validasi MIME PDF
        $allowedMimes = ['application/pdf'];
        $detectedMime = $file->getMimeType();

        if (!in_array($detectedMime, $allowedMimes)) {
            throw new \InvalidArgumentException(
                "Hanya file PDF yang diizinkan."
            );
        }

        $filename = Str::uuid() . '.pdf';
        $path     = $folder . '/' . $filename;

        Storage::disk($this->disk)->putFileAs(
            $folder,
            $file,
            $filename,
            'public'
        );

        return [
            'path' => $path,
            'url'  => $this->getUrl($path),
        ];
    }

    /**
     * Hapus file dari MinIO
     */
    public function delete(?string $path): void
    {
        if ($path && Storage::disk($this->disk)->exists($path)) {
            Storage::disk($this->disk)->delete($path);
        }
    }

    /**
     * Hapus file lama saat update (gambar + thumbnail)
     */
    public function deleteOld(?string $path, ?string $thumbnailPath = null): void
    {
        $this->delete($path);
        $this->delete($thumbnailPath);
    }

    /**
     * Generate URL publik file dari MinIO
     * Menggabungkan MINIO_URL + path
     */
    public function getUrl(string $path): string
    {
        return Storage::disk($this->disk)->url($path);
    }
}
