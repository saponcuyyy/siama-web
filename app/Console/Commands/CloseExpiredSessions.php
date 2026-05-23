<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\{SesiUjian, PesertaUjian};
use Illuminate\Support\Carbon;
use App\Services\Ujian\UjianService;

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
     * @param \App\Services\Ujian\UjianService $ujianService
     * @return int
     */
    public function handle(UjianService $ujianService)
    {
        $now = Carbon::now();

        // Find expired sessions that are still active (menunggu or berlangsung)
        $expiredSessions = SesiUjian::whereIn('status', ['menunggu', 'berlangsung'])
            ->where('waktu_selesai', '<', $now)
            ->get();

        $closedCount = 0;

        foreach ($expiredSessions as $session) {
            // Update session status to selesai
            $session->update(['status' => 'selesai']);

            // Akhiri ujian untuk peserta yang masih mengerjakan
            $pesertas = PesertaUjian::where('sesi_ujian_id', $session->id)
                ->where('status', 'mengerjakan')
                ->get();

            foreach ($pesertas as $peserta) {
                $ujianService->akhiriUjian($peserta, '127.0.0.1', 'System/Cron');
            }

            $closedCount++;
        }

        $this->info("Closed {$closedCount} expired session(s).");

        return Command::SUCCESS;
    }
}