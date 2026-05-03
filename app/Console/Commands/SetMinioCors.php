<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Aws\S3\S3Client;

class SetMinioCors extends Command
{
    protected $signature = 'minio:set-cors';
    protected $description = 'Set CORS policy for MinIO bucket';

    public function handle()
    {
        $this->info('Setting MinIO CORS policy...');

        $s3 = new S3Client([
            'version' => 'latest',
            'region'  => env('MINIO_REGION', 'us-east-1'),
            'endpoint' => env('MINIO_ENDPOINT', 'http://127.0.0.1:9000'),
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key'    => env('MINIO_KEY'),
                'secret' => env('MINIO_SECRET'),
            ],
        ]);

        $bucket = env('MINIO_BUCKET', 'siama-web');

        try {
            $s3->putBucketCors([
                'Bucket' => $bucket,
                'CORSConfiguration' => [
                    'CORSRules' => [
                        [
                            'AllowedHeaders' => ['*'],
                            'AllowedMethods' => ['GET', 'HEAD'],
                            'AllowedOrigins' => ['*'], // For development
                            'ExposeHeaders'  => [],
                            'MaxAgeSeconds'  => 3000,
                        ],
                    ],
                ],
            ]);
            $this->info("CORS policy set successfully for bucket: $bucket");
        } catch (\Exception $e) {
            $this->error("Failed to set CORS: " . $e->getMessage());
        }
    }
}
