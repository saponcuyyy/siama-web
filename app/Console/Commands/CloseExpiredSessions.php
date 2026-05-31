<?php

namespace App\Console\Commands;

use App\Enums\PesertaStatus;
use App\Enums\SesiStatus;
use App\Models\SesiUjian;
use App\Services\Ujian\UjianService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CloseExpiredSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sesi:close-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Close exam sessions that have passed their end time';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(UjianService $ujianService)
    {
        $now = Carbon::now();

        $closedCount = 0;

        // Ambil semua sesi yang masih aktif, lalu filter berdasarkan toleransi_menit
        SesiUjian::whereIn('status', [SesiStatus::MENUNGGU->value, SesiStatus::BERLANGSUNG->value])
            ->with(['pesertaUjian' => fn ($q) => $q->where('status', PesertaStatus::MENGERJAKAN->value)])
            ->chunk(50, function ($sessions) use ($ujianService, &$closedCount, $now) {

                foreach ($sessions as $session) {
                    // Hitung batas waktu efektif dengan toleransi
                    $batasWaktu = $session->waktu_selesai->copy()
                        ->addMinutes($session->toleransi_menit ?? 0);

                    // Lewati jika belum melewati batas waktu (+ toleransi)
                    if ($batasWaktu->gt($now)) {
                        continue;
                    }

                    // Update session status to selesai
                    $session->update(['status' => 'selesai']);

                    // Akhiri ujian untuk peserta yang masih mengerjakan
                    foreach ($session->pesertaUjian as $peserta) {
                        try {
                            $ujianService->akhiriUjian(
                                $peserta,
                                '127.0.0.1',
                                'System/Cron'
                            );
                        } catch (\Throwable $e) {
                            $this->error("Failed to close peserta {$peserta->id}: {$e->getMessage()}");
                        }
                    }

                    $closedCount++;
                }
            });

        $this->info("Closed {$closedCount} expired session(s).");

        return Command::SUCCESS;
    }
}
