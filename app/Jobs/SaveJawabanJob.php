<?php

namespace App\Jobs;

use App\Models\JawabanSiswa;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SaveJawabanJob implements ShouldQueue
{
    use Queueable;

    protected $pesertaId;
    protected $soalId;
    protected $jawaban;
    protected $durasi;

    public function __construct(int $pesertaId, int $soalId, $jawaban, int $durasi)
    {
        $this->pesertaId = $pesertaId;
        $this->soalId = $soalId;
        $this->jawaban = $jawaban;
        $this->durasi = $durasi;
    }

    public function handle(): void
    {
        $jawaban = JawabanSiswa::firstOrNew([
            'peserta_ujian_id' => $this->pesertaId,
            'soal_id'          => $this->soalId,
        ]);

        if (is_array($this->jawaban)) {
            $jawaban->jawaban_menjodohkan = $this->jawaban;
        } else {
            $jawaban->jawaban = $this->jawaban;
        }

        $jawaban->durasi_detik = ($jawaban->durasi_detik ?? 0) + $this->durasi;
        $jawaban->dijawab_at   = now();
        $jawaban->save();
    }
}
