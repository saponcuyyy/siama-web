<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    /**
     * Proxy gambar soal dari MinIO ke browser.
     * URL relatif terhadap app, tidak bergantung pada hostname MinIO.
     */
    public function soalImage(Request $request, string $path)
    {
        $fullPath = 'soal-images/'.$path;

        if (! Storage::disk('minio')->exists($fullPath)) {
            abort(404, 'Gambar tidak ditemukan.');
        }

        $file = Storage::disk('minio')->get($fullPath);
        $mime = Storage::disk('minio')->mimeType($fullPath) ?: 'image/jpeg';

        return response($file, 200)
            ->header('Content-Type', $mime)
            ->header('Cache-Control', 'public, max-age=86400'); // cache 1 hari
    }
}
