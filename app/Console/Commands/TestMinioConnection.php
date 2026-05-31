<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestMinioConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'minio:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test connection to MinIO storage';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $disk = Storage::disk('minio');
            $test = 'minio-test-'.time().'.txt';

            $disk->put($test, 'MinIO connection test OK', 'public');
            $url = $disk->url($test);
            $disk->delete($test);

            $this->info('✅ Koneksi MinIO berhasil!');
            $this->info('📦 Bucket : '.config('filesystems.disks.minio.bucket'));
            $this->info('🔗 URL    : '.$url);
        } catch (\Exception $e) {
            $this->error('❌ Koneksi MinIO gagal: '.$e->getMessage());
        }
    }
}
