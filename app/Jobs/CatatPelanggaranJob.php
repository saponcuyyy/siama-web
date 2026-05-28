<?php

namespace App\Jobs;

use App\Models\{PesertaUjian, LogUjian};
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class CatatPelanggaranJob implements ShouldQueue
{
    use Queueable;

    protected int $pesertaId;
    protected string $tipe;
    protected string $ip;
    protected ?string $userAgent;

    public function __construct(int $pesertaId, string $tipe, string $ip, ?string $userAgent)
    {
        $this->pesertaId = $pesertaId;
        $this->tipe = $tipe;
        $this->ip = $ip;
        $this->userAgent = $userAgent;
    }

    public function handle(): void
    {
        DB::transaction(function () {
            $peserta = PesertaUjian::lockForUpdate()->findOrFail($this->pesertaId);

            $peserta->increment('jumlah_pelanggaran');

            LogUjian::create([
                'peserta_ujian_id' => $peserta->id,
                'tipe_event'       => $this->tipe,
                'ip_address'       => $this->ip,
                'user_agent'       => $this->userAgent,
                'terjadi_at'       => now(),
            ]);

            if ($peserta->jumlah_pelanggaran >= $peserta->sesiUjian->max_pelanggaran) {
                $peserta->update(['status' => 'didiskualifikasi', 'selesai_at' => now()]);

                LogUjian::create([
                    'peserta_ujian_id' => $peserta->id,
                    'tipe_event'       => 'diskualifikasi',
                    'keterangan'       => 'Mencapai batas maksimal pelanggaran',
                    'ip_address'       => $this->ip,
                    'user_agent'       => $this->userAgent,
                    'terjadi_at'       => now(),
                ]);
            }
        });
    }
}
