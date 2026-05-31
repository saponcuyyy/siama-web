<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $isDev = app()->environment('local');

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'no-referrer-when-downgrade');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        $externalStorageUrl = config('filesystems.disks.public.url');

        if ($externalStorageUrl && ! str_starts_with($externalStorageUrl, config('app.url')) && ! str_contains($externalStorageUrl, '127.0.0.1') && ! str_contains($externalStorageUrl, 'localhost')) {
            $externalStorageUrl = rtrim($externalStorageUrl, '/');
        } else {
            $externalStorageUrl = null;
        }

        $imgSrc = 'img-src \'self\' data: https://ui-avatars.com https://*.tile.openstreetmap.org https://cdnjs.cloudflare.com https://images.unsplash.com https://unpkg.com';

        if ($externalStorageUrl) {
            $imgSrc .= ' '.$externalStorageUrl;
        }
        $imgSrc .= '; ';

        $connectSrc = 'connect-src \'self\'';
        if ($externalStorageUrl) {
            $connectSrc .= ' '.$externalStorageUrl;
        }
        $connectSrc .= '; ';

        if ($isDev) {
            $connectDev = 'connect-src \'self\''.
                ($externalStorageUrl ? ' '.$externalStorageUrl : '').
                ' ws://localhost:5173 ws://127.0.0.1:5173 http://localhost:5173 http://127.0.0.1:5173;';

            $response->headers->set('Content-Security-Policy',
                "default-src 'self'; ".
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net http://localhost:5173 http://127.0.0.1:5173; ".
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com http://localhost:5173 http://127.0.0.1:5173; ".
                "font-src 'self' data: https://fonts.gstatic.com; ".
                $imgSrc.
                "media-src 'self'".($externalStorageUrl ? ' '.$externalStorageUrl : '').'; '.
                $connectDev
            );
        } else {
            $response->headers->set('Content-Security-Policy',
                "default-src 'self'; ".
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net; ".
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; ".
                "font-src 'self' data: https://fonts.gstatic.com; ".
                $imgSrc.
                "media-src 'self'".($externalStorageUrl ? ' '.$externalStorageUrl : '').'; '.
                $connectSrc
            );
        }

        return $response;
    }
}
