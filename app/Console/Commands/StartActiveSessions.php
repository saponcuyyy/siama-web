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

        $sessions = SesiUjian::with('rombels')
            ->where('status', 'menunggu')
            ->where('waktu_mulai', '<=', $now)
            ->get();

        $startedCount = 0;

        foreach ($sessions as $session) {
            DB::transaction(function () use ($session) {
                $session->update(['status' => 'berlangsung']);

                $rombelIds = $this->getRombelIds($session);
                if (!empty($rombelIds)) {
                    $siswaIds = Siswa::whereIn('rombel_id', $rombelIds)->pluck('id');
                    $existing = PesertaUjian::where('sesi_ujian_id', $session->id)
                        ->whereIn('siswa_id', $siswaIds)
                        ->pluck('siswa_id');

                    $newIds = $siswaIds->diff($existing);
                    $now = now();

                    $newPeserta = $newIds->map(fn($id) => [
                        'sesi_ujian_id' => $session->id,
                        'siswa_id'      => $id,
                        'status'        => 'belum_mulai',
                        'created_at'    => $now,
                        'updated_at'    => $now,
                    ])->toArray();

                    if (!empty($newPeserta)) {
                        PesertaUjian::insert($newPeserta);
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
        $rombels = $sesi->rombels;

        if ($rombels->isNotEmpty()) {
            return $rombels->pluck('id')->toArray();
        }

        if ($sesi->rombel_id) {
            return [$sesi->rombel_id];
        }

        return [];
    }
}
