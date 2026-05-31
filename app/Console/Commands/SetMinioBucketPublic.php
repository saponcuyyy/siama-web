<?php

namespace App\Console\Commands;

use Aws\S3\S3Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SetMinioBucketPublic extends Command
{
    protected $signature = 'minio:set-public';

    protected $description = 'Set MinIO bucket policy to public read-only';

    public function handle()
    {
        $bucket = config('filesystems.disks.minio.bucket');
        $this->info("Setting bucket '$bucket' to public...");

        try {
            // Get S3 client from the disk
            $adapter = Storage::disk('minio')->getAdapter();

            // We need the raw S3 client
            // If using league/flysystem-aws-s3-v3, the client is accessible
            $client = new S3Client([
                'version' => 'latest',
                'region' => config('filesystems.disks.minio.region'),
                'endpoint' => config('filesystems.disks.minio.endpoint'),
                'use_path_style_endpoint' => config('filesystems.disks.minio.use_path_style_endpoint'),
                'credentials' => [
                    'key' => config('filesystems.disks.minio.key'),
                    'secret' => config('filesystems.disks.minio.secret'),
                ],
            ]);

            $policy = [
                'Version' => '2012-10-17',
                'Statement' => [
                    [
                        'Effect' => 'Allow',
                        'Principal' => ['AWS' => ['*']],
                        'Action' => ['s3:GetBucketLocation', 's3:ListBucket'],
                        'Resource' => ["arn:aws:s3:::$bucket"],
                    ],
                    [
                        'Effect' => 'Allow',
                        'Principal' => ['AWS' => ['*']],
                        'Action' => ['s3:GetObject'],
                        'Resource' => ["arn:aws:s3:::$bucket/*"],
                    ],
                ],
            ];

            $client->putBucketPolicy([
                'Bucket' => $bucket,
                'Policy' => json_encode($policy),
            ]);

            $this->info("✅ Bucket '$bucket' is now PUBLIC (read-only).");
        } catch (\Exception $e) {
            $this->error('❌ Failed to set bucket policy: '.$e->getMessage());
            $this->line("Alternative: Run this in your terminal if you have 'mc' (MinIO Client):");
            $this->line("mc anonymous set download local/$bucket");
        }
    }
}
