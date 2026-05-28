<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class SyncUjianSessions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Metode JIT (Just-In-Time) Sinkronisasi Sesi
        // Akan tereksekusi maksimal 1 kali setiap 60 detik agar tidak membebani server
        if (Cache::add('sync_ujian_sessions_lock', true, 60)) {
            try {
                // Jalankan command start & close secara sinkronus di background request
                Artisan::call('sesi:start-active');
                Artisan::call('sesi:close-expired');
            } catch (\Throwable $e) {
                // Abaikan error agar request pengguna tidak terganggu
            }
        }

        return $next($request);
    }
}
