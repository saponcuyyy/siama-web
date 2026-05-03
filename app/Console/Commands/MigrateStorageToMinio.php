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
        foreach (Slider::all() as $slider) {
            $this->copyToMinio($slider->file_path);
        }
    }

    private function migrateBerita()
    {
        $this->info('Migrating Berita...');
        foreach (Berita::all() as $berita) {
            $this->copyToMinio($berita->thumbnail);
        }
    }

    private function migrateFasilitas()
    {
        $this->info('Migrating Fasilitas...');
        foreach (Fasilitas::all() as $fasilitas) {
            $this->copyToMinio($fasilitas->foto);
        }
    }

    private function migrateAlbums()
    {
        $this->info('Migrating Albums...');
        foreach (Album::all() as $album) {
            $this->copyToMinio($album->cover);
        }
    }

    private function migrateGaleri()
    {
        $this->info('Migrating Galeri...');
        foreach (Galeri::all() as $galeri) {
            $this->copyToMinio($galeri->file_path);
        }
    }

    private function copyToMinio($path)
    {
        if (!$path || str_starts_with($path, 'http')) return;

        if (Storage::disk('public')->exists($path)) {
            $content = Storage::disk('public')->get($path);
            Storage::disk('minio')->put($path, $content, 'public');
            $this->line("✅ Copied: $path");
        } else {
            $this->warn("⚠️ File not found: $path");
        }
    }
}
