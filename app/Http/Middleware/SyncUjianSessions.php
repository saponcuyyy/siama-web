<?php

namespace App\Http\Middleware;

use App\Enums\PesertaStatus;
use App\Models\PesertaUjian;
use App\Models\SesiUjian;
use App\Models\Siswa;
use App\Services\Ujian\UjianService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SyncUjianSessions
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Metode JIT (Just-In-Time) Sinkronisasi Sesi
        // Akan tereksekusi maksimal 1 kali setiap 60 detik agar tidak membebani server
        if (Cache::add('sync_ujian_sessions_lock', true, 60)) {
            Log::info('Running JIT Session Sync (Direct DB)...');
            try {
                $now = now();

                // 1. Start Active Sessions
                $sessionsToStart = SesiUjian::with('rombels')
                    ->where('status', 'menunggu')
                    ->where('waktu_mulai', '<=', $now)
                    ->get();

                foreach ($sessionsToStart as $session) {
                    DB::transaction(function () use ($session) {
                        $session->update(['status' => 'berlangsung']);
                        $rombelIds = [];
                        if ($session->rombels->isNotEmpty()) {
                            $rombelIds = $session->rombels->pluck('id')->toArray();
                        } elseif ($session->rombel_id) {
                            $rombelIds = [$session->rombel_id];
                        }

                        if (! empty($rombelIds)) {
                            $siswaIds = Siswa::whereIn('rombel_id', $rombelIds)->pluck('id');
                            $existing = PesertaUjian::where('sesi_ujian_id', $session->id)
                                ->whereIn('siswa_id', $siswaIds)
                                ->pluck('siswa_id');

                            $newIds = $siswaIds->diff($existing);
                            $now = now();

                            $newPeserta = $newIds->map(fn ($id) => [
                                'sesi_ujian_id' => $session->id,
                                'siswa_id' => $id,
                                'status' => 'belum_mulai',
                                'created_at' => $now,
                                'updated_at' => $now,
                            ])->toArray();

                            if (! empty($newPeserta)) {
                                PesertaUjian::insert($newPeserta);
                            }
                        }
                    });
                }

                // 2. Close Expired Sessions (dengan toleransi_menit)
                $ujianService = app(UjianService::class);

                $expiredSessions = SesiUjian::whereIn('status', ['menunggu', 'berlangsung'])
                    ->with(['pesertaUjian' => fn ($q) => $q->where('status', PesertaStatus::MENGERJAKAN->value)])
                    ->get()
                    ->filter(function ($session) use ($now) {
                        // Hitung batas waktu efektif dengan toleransi
                        $batasWaktu = $session->waktu_selesai->copy()
                            ->addMinutes($session->toleransi_menit ?? 0);

                        return $batasWaktu->lt($now);
                    });

                foreach ($expiredSessions as $session) {
                    $session->update(['status' => 'selesai']);

                    // Akhiri ujian dengan scoring otomatis untuk peserta yang masih mengerjakan
                    foreach ($session->pesertaUjian as $peserta) {
                        try {
                            $ujianService->akhiriUjian(
                                $peserta,
                                '127.0.0.1',
                                'System/JIT-Sync'
                            );
                        } catch (\Throwable $e) {
                            Log::error(
                                "JIT Sync: Failed to close peserta {$peserta->id}: ".$e->getMessage()
                            );
                        }
                    }
                }

                Cache::forever('cron_last_run', now()->timestamp);

                Log::info('JIT Sync completed.');
            } catch (\Throwable $e) {
                Log::error('JIT Sync failed: '.$e->getMessage());
            }
        }

        return $next($request);
    }
}
