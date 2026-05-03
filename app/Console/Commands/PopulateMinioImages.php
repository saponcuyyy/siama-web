<?php

namespace App\Console\Commands;

use App\Models\Album;
use App\Models\Fasilitas;
use App\Models\Slider;
use App\Models\Setting;
use App\Services\Website\FileUploadService;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PopulateMinioImages extends Command
{
    protected $signature = 'minio:populate-images';
    protected $description = 'Download theme images and upload to MinIO';

    public function handle(FileUploadService $uploader)
    {
        $this->info('Starting image population to MinIO...');

        try {
            if (DB::getDriverName() === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = OFF;');
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            }

            // 1. Sliders
            $this->info('Populating Sliders...');
            Slider::query()->forceDelete(); 
            $sliders = [
                [
                    'judul' => 'Selamat Datang di SMAN 2 Perbaungan',
                    'subjudul' => 'Mencetak Generasi Unggul dan Berkarakter.',
                    'url' => 'https://images.unsplash.com/photo-1546410531-bb4caa6b424d?auto=format&fit=crop&w=1920&q=80',
                ],
                [
                    'judul' => 'Fasilitas Belajar Modern',
                    'subjudul' => 'Lingkungan yang mendukung kreativitas siswa.',
                    'url' => 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=1920&q=80',
                ],
            ];
            foreach ($sliders as $index => $s) {
                $path = $this->uploadFromUrl($s['url'], 'sliders', $uploader);
                if ($path) {
                    Slider::create([
                        'judul' => $s['judul'],
                        'subjudul' => $s['subjudul'],
                        'file_path' => $path,
                        'status' => 'aktif',
                        'urutan' => $index + 1,
                    ]);
                }
            }

            // 2. Fasilitas
            $this->info('Populating Fasilitas...');
            Fasilitas::query()->forceDelete();
            $fasilitas = [
                ['nama' => 'Lab Komputer', 'url' => 'https://images.unsplash.com/photo-1562774053-701939374585?auto=format&fit=crop&w=800&q=80'],
                ['nama' => 'Perpustakaan', 'url' => 'https://images.unsplash.com/photo-1521587760476-6c120c24443b?auto=format&fit=crop&w=800&q=80'],
                ['nama' => 'Aula Serbaguna', 'url' => 'https://images.unsplash.com/photo-1517486808906-6ca8b3f04846?auto=format&fit=crop&w=800&q=80'],
            ];
            foreach ($fasilitas as $index => $f) {
                $path = $this->uploadFromUrl($f['url'], 'fasilitas', $uploader);
                if ($path) {
                    Fasilitas::create([
                        'nama' => $f['nama'],
                        'deskripsi' => 'Fasilitas unggulan untuk mendukung kegiatan belajar mengajar.',
                        'foto' => $path,
                        'urutan' => $index + 1,
                        'status' => 'aktif',
                    ]);
                }
            }

            // 3. Album
            $this->info('Populating Album...');
            Album::query()->forceDelete();
            $albumPath = $this->uploadFromUrl('https://images.unsplash.com/photo-1523240735140-2236b797996a?auto=format&fit=crop&w=800&q=80', 'albums', $uploader);
            if ($albumPath) {
                Album::create([
                    'nama' => 'Wisuda Angkatan 2025',
                    'deskripsi' => 'Momen kebahagiaan para lulusan.',
                    'cover' => $albumPath,
                    'status' => 'aktif',
                ]);
            }

            if (DB::getDriverName() === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = ON;');
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }

            $this->info('All images populated successfully!');
        } catch (\Exception $e) {
            $this->error("Fatal Error: " . $e->getMessage());
        }
    }

    private function uploadFromUrl(string $url, string $folder, FileUploadService $uploader)
    {
        try {
            $this->line("Downloading: $url");
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0'
            ])->timeout(60)->get($url);

            if ($response->failed()) {
                $this->error("Failed: " . $response->status());
                return null;
            }

            $tempName = Str::random(10) . '.jpg';
            $tempPath = storage_path('app/' . $tempName);
            file_put_contents($tempPath, $response->body());

            $file = new UploadedFile($tempPath, $tempName, 'image/jpeg', null, true);
            $result = $uploader->uploadImage($file, $folder);
            @unlink($tempPath);

            return $result['path'];
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return null;
        }
    }
}
