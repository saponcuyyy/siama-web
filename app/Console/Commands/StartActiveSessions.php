<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SesiUjian;
use App\Models\PesertaUjian;
use App\Models\Siswa;
use Illuminate\Support\Facades\DB;

class StartActiveSessions extends Command
{
    protected $signature = 'sesi:start-active';

    protected $description = 'Auto-start exam sessions when waktu_mulai is reached';

    public function handle()
    {
        $now = now();

        $sessions = SesiUjian::where('status', 'menunggu')
            ->where('waktu_mulai', '<=', $now)
            ->get();

        $startedCount = 0;

        foreach ($sessions as $session) {
            DB::transaction(function () use ($session) {
                $session->update(['status' => 'berlangsung']);

                $rombelIds = $this->getRombelIds($session);
                if (!empty($rombelIds)) {
                    $siswaList = Siswa::whereIn('rombel_id', $rombelIds)->get();
                    foreach ($siswaList as $siswa) {
                        PesertaUjian::firstOrCreate(
                            ['sesi_ujian_id' => $session->id, 'siswa_id' => $siswa->id],
                            ['status' => 'belum_mulai']
                        );
                    }
                }
            });

            $startedCount++;
        }

        if ($startedCount > 0) {
            $this->info("Auto-started {$startedCount} session(s).");
        }

        return Command::SUCCESS;
    }

    private function getRombelIds(SesiUjian $sesi): array
    {
        if ($sesi->rombels()->count() > 0) {
            return $sesi->rombels()->pluck('rombel.id')->toArray();
        }

        if ($sesi->rombel_id) {
            return [$sesi->rombel_id];
        }

        return [];
    }
}
