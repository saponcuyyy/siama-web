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

        if (! in_array($detectedMime, $allowedMimes)) {
            throw new \InvalidArgumentException(
                "Tipe file tidak diizinkan: {$detectedMime}"
            );
        }

        // 2. Tentukan ekstensi dari MIME yang sudah divalidasi, bukan dari client
        $extension = match ($detectedMime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            default => 'jpg',
        };
        $filename = Str::uuid().'.'.$extension;
        $path = $folder.'/'.$filename;

        // 3. Resize gambar jika lebar melebihi maxWidth
        $image = Image::read($file->getRealPath())
            ->scaleDown(width: $maxWidth);

        // 4. Pilih encoder sesuai format asli untuk menjaga transparansi
        $encoded = match ($detectedMime) {
            'image/png' => $image->toPng(),
            'image/webp' => $image->toWebp(quality: 85),
            'image/gif' => $image->toGif(),
            default => $image->toJpeg(quality: 85),
        };

        Storage::disk($this->disk)->put(
            $path,
            $encoded,
            'public'
        );

        $result = ['path' => $path, 'url' => $this->getUrl($path)];

        // 5. Generate thumbnail jika diminta
        if ($thumbWidth) {
            $thumbFilename = Str::uuid().'_thumb.'.$extension;
            $thumbPath = $folder.'/thumbnails/'.$thumbFilename;

            $thumb = Image::read($file->getRealPath())
                ->scaleDown(width: $thumbWidth);

            Storage::disk($this->disk)->put(
                $thumbPath,
                $thumb->toJpeg(quality: 75),
                'public'
            );

            $result['thumbnail_path'] = $thumbPath;
            $result['thumbnail_url'] = $this->getUrl($thumbPath);
        }

        return $result;
    }

    /**
     * Upload file dokumen (PDF) dengan validasi berlapis (secure coding)
     *
     * Layer 1: Validasi ekstensi (dilakukan di controller via Laravel Validator)
     * Layer 2: Validasi MIME type dari konten file (OS-level, bukan header client)
     * Layer 3: Validasi magic bytes (%PDF) — mencegah content spoofing
     */
    public function uploadDocument(
        UploadedFile $file,
        string $folder = 'cms/documents',
        int $maxSizeBytes = 10 * 1024 * 1024 // 10 MB default
    ): array {
        // Layer 1: Ukuran file
        if ($file->getSize() > $maxSizeBytes) {
            throw new \InvalidArgumentException(
                'Ukuran file melebihi batas maksimum '.($maxSizeBytes / 1024 / 1024).' MB.'
            );
        }

        // Layer 2: Validasi MIME type dari sistem operasi (bukan dari request header)
        $detectedMime = $file->getMimeType();
        if ($detectedMime !== 'application/pdf') {
            throw new \InvalidArgumentException(
                "Tipe file tidak valid: {$detectedMime}. Hanya PDF yang diizinkan."
            );
        }

        // Layer 3: Validasi magic bytes — baca 4 byte pertama dan cek signature %PDF
        $handle = fopen($file->getRealPath(), 'rb');
        if ($handle === false) {
            throw new \RuntimeException('Tidak dapat membaca file yang diupload.');
        }
        $magicBytes = fread($handle, 4);
        fclose($handle);

        if ($magicBytes !== '%PDF') {
            throw new \InvalidArgumentException(
                'File bukan PDF yang valid (signature tidak cocok).'
            );
        }

        // Simpan dengan nama UUID — tidak menggunakan nama file dari client
        $filename = Str::uuid().'.pdf';
        $path = $folder.'/'.$filename;

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
     * Mengunggah file PDF, memvalidasinya, mengonversinya menjadi satu file HTML self-contained,
     * lalu menghapus file PDF aslinya.
     */
    public function uploadDocumentAndConvertToHtml(
        UploadedFile $file,
        string $folder = 'cms/documents/pengumuman',
        int $maxSizeBytes = 10 * 1024 * 1024
    ): array {
        // Validasi input PDF
        if ($file->getSize() > $maxSizeBytes) {
            throw new \InvalidArgumentException(
                'Ukuran file melebihi batas maksimum '.($maxSizeBytes / 1024 / 1024).' MB.'
            );
        }

        $detectedMime = $file->getMimeType();
        if ($detectedMime !== 'application/pdf') {
            throw new \InvalidArgumentException(
                "Tipe file tidak valid: {$detectedMime}. Hanya PDF yang diizinkan."
            );
        }

        $handle = fopen($file->getRealPath(), 'rb');
        if ($handle === false) {
            throw new \RuntimeException('Tidak dapat membaca file yang diupload.');
        }
        $magicBytes = fread($handle, 4);
        fclose($handle);

        if ($magicBytes !== '%PDF') {
            throw new \InvalidArgumentException(
                'File bukan PDF yang valid (signature tidak cocok).'
            );
        }

        $uuid = Str::uuid();
        $pdfName = $uuid.'.pdf';
        $pdfPath = $folder.'/'.$pdfName;

        // Simpan PDF asli untuk viewer interaktif
        Storage::disk($this->disk)->putFileAs(
            $folder,
            $file,
            $pdfName,
            'public'
        );

        $htmlName = $uuid.'.html';
        $htmlPath = $folder.'/'.$htmlName;
        $tablesName = $uuid.'.json';
        $tablesPath = $folder.'/'.$tablesName;

        try {
            // Konversi ke HTML (fallback/embed)
            $this->convertPdfToHtml($pdfPath, $htmlPath);

            // Ekstrak tabel dari PDF
            try {
                $extractor = app(PdfTableExtractor::class);
                $tables = $extractor->extract($pdfPath);
                if (!empty($tables)) {
                    // Merge consecutive tables with identical columns
                    $merged = [];
                    foreach ($tables as $t) {
                        if (
                            !empty($merged)
                            && end($merged)['columns'] === $t['columns']
                        ) {
                            $idx = key($merged);
                            $merged[$idx]['rows'] = array_merge(
                                $merged[$idx]['rows'], $t['rows']
                            );
                        } else {
                            $merged[] = $t;
                        }
                    }
                    Storage::disk($this->disk)->put(
                        $tablesPath,
                        json_encode($merged, JSON_UNESCAPED_UNICODE),
                        'public'
                    );
                }
            } catch (\Exception $e) {
                // Gagal ekstrak tabel bukan error fatal
                \Illuminate\Support\Facades\Log::warning('PDF table extraction failed: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            $this->delete($pdfPath);
            throw $e;
        }

        return [
            'path'        => $htmlPath,
            'url'         => $this->getUrl($htmlPath),
            'pdf_url'     => $this->getUrl($pdfPath),
            'tables_path' => $tablesPath,
        ];
    }

    /**
     * Konversi file PDF ke HTML menggunakan pdftohtml CLI tool.
     * Menggunakan Symfony Process agar aman dan sinkron.
     */
    public function convertPdfToHtml(string $pdfPath, string $htmlPath): bool
    {
        $disk = Storage::disk($this->disk);

        if (!$disk->exists($pdfPath)) {
            throw new \InvalidArgumentException("File PDF input tidak ditemukan.");
        }

        $pdfRealPath = $disk->path($pdfPath);
        $htmlRealPath = $disk->path($htmlPath);

        // Pastikan folder tujuan ada
        $dirPath = dirname($htmlRealPath);
        if (!is_dir($dirPath)) {
            mkdir($dirPath, 0755, true);
        }

        // Jalankan pdftohtml
        // -s: single HTML file
        // -noframes: no HTML frames
        // -dataurls: embed images as base64 data URIs
        $process = \Illuminate\Support\Facades\Process::run([
            'pdftohtml',
            '-s',
            '-noframes',
            '-dataurls',
            $pdfRealPath,
            $htmlRealPath
        ]);

        if (!$process->successful()) {
            throw new \RuntimeException(
                "Gagal mengonversi PDF ke HTML: " . $process->errorOutput()
            );
        }

        return true;
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
