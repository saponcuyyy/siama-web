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
            \Illuminate\Support\Facades\Log::info('Running JIT Session Sync (Direct DB)...');
            try {
                $now = now();

                // 1. Start Active Sessions
                $sessionsToStart = \App\Models\SesiUjian::with('rombels')
                    ->where('status', 'menunggu')
                    ->where('waktu_mulai', '<=', $now)
                    ->get();

                foreach ($sessionsToStart as $session) {
                    \Illuminate\Support\Facades\DB::transaction(function () use ($session) {
                        $session->update(['status' => 'berlangsung']);
                        $rombelIds = [];
                        if ($session->rombels->isNotEmpty()) {
                            $rombelIds = $session->rombels->pluck('id')->toArray();
                        } elseif ($session->rombel_id) {
                            $rombelIds = [$session->rombel_id];
                        }
                        
                        if (!empty($rombelIds)) {
                            \App\Models\Siswa::whereIn('rombel_id', $rombelIds)->chunk(200, function ($siswaList) use ($session) {
                                foreach ($siswaList as $siswa) {
                                    \App\Models\PesertaUjian::firstOrCreate(
                                        ['sesi_ujian_id' => $session->id, 'siswa_id' => $siswa->id],
                                        ['status' => 'belum_mulai']
                                    );
                                }
                            });
                        }
                    });
                }

                // 2. Close Expired Sessions
                $expiredSessions = \App\Models\SesiUjian::whereIn('status', ['menunggu', 'berlangsung'])
                    ->where('waktu_selesai', '<', $now)
                    ->get();

                foreach ($expiredSessions as $session) {
                    $session->update(['status' => 'selesai']);
                    // Optionally end exams for pesertas, but updating session status prevents login anyway.
                }

                \Illuminate\Support\Facades\Log::info('JIT Sync completed.');
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('JIT Sync failed: ' . $e->getMessage());
            }
        }

        return $next($request);
    }
}
