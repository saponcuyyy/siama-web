<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $isDev = app()->environment('local');

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'no-referrer-when-downgrade'); // Better for OSM tiles
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // Extended CSP for MinIO, Maps, and External Assets
        $imgSrc = "img-src 'self' data: https://ui-avatars.com http://127.0.0.1:9000 https://*.tile.openstreetmap.org https://cdnjs.cloudflare.com https://images.unsplash.com https://unpkg.com; ";
        $connectSrc = "connect-src 'self' http://127.0.0.1:9000; ";

        if ($isDev) {
            // Allow Vite dev server
            $response->headers->set('Content-Security-Policy',
                "default-src 'self'; " .
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' http://localhost:5173 http://127.0.0.1:5173; " .
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com http://localhost:5173 http://127.0.0.1:5173; " .
                "font-src 'self' data: https://fonts.gstatic.com; " .
                $imgSrc .
                $connectSrc . "ws://localhost:5173 ws://127.0.0.1:5173 http://localhost:5173 http://127.0.0.1:5173;"
            );
        } else {
            $response->headers->set('Content-Security-Policy',
                "default-src 'self'; " .
                "script-src 'self' 'unsafe-inline' 'unsafe-eval'; " .
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; " .
                "font-src 'self' data: https://fonts.gstatic.com; " .
                $imgSrc .
                $connectSrc
            );
        }

        return $response;
    }
}
