<?php

namespace App\Console\Commands;

use App\Models\Album;
use App\Models\Berita;
use App\Models\Fasilitas;
use App\Models\Galeri;
use App\Models\Slider;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateStorageToMinio extends Command
{
    protected $signature = 'storage:migrate-minio';

    protected $description = 'Migrate local storage files to MinIO';

    public function handle()
    {
        $this->info('Starting migration to MinIO...');

        $this->migrateSliders();
        $this->migrateBerita();
        $this->migrateFasilitas();
        $this->migrateAlbums();
        $this->migrateGaleri();

        $this->info('Migration completed!');
    }

    private function migrateSliders()
    {
        $this->info('Migrating Sliders...');
        Slider::chunk(100, function ($sliders) {
            foreach ($sliders as $slider) {
                $this->copyToMinio($slider->file_path);
            }
        });
    }

    private function migrateBerita()
    {
        $this->info('Migrating Berita...');
        Berita::chunk(100, function ($beritaList) {
            foreach ($beritaList as $berita) {
                $this->copyToMinio($berita->thumbnail);
            }
        });
    }

    private function migrateFasilitas()
    {
        $this->info('Migrating Fasilitas...');
        Fasilitas::chunk(100, function ($fasilitasList) {
            foreach ($fasilitasList as $fasilitas) {
                $this->copyToMinio($fasilitas->foto);
            }
        });
    }

    private function migrateAlbums()
    {
        $this->info('Migrating Albums...');
        Album::chunk(100, function ($albums) {
            foreach ($albums as $album) {
                $this->copyToMinio($album->cover);
            }
        });
    }

    private function migrateGaleri()
    {
        $this->info('Migrating Galeri...');
        Galeri::chunk(100, function ($galeriList) {
            foreach ($galeriList as $galeri) {
                $this->copyToMinio($galeri->file_path);
            }
        });
    }

    private function copyToMinio($path)
    {
        if (! $path || str_starts_with($path, 'http')) {
            return;
        }

        if (Storage::disk('public')->exists($path)) {
            $content = Storage::disk('public')->get($path);
            Storage::disk('minio')->put($path, $content, 'public');
            $this->line("✅ Copied: $path");
        } else {
            $this->warn("⚠️ File not found: $path");
        }
    }
}
