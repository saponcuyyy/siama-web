<?php

namespace Database\Factories;

use App\Models\PesertaUjian;
use App\Models\SesiUjian;
use App\Models\Siswa;
use Illuminate\Database\Eloquent\Factories\Factory;

class PesertaUjianFactory extends Factory
{
    protected $model = PesertaUjian::class;

    public function definition(): array
    {
        return [
            'sesi_ujian_id' => SesiUjian::factory(),
            'siswa_id' => Siswa::factory(),
            'status' => 'belum_mulai',
            'mulai_at' => null,
            'selesai_at' => null,
            'sisa_detik' => 5400,
            'jumlah_pelanggaran' => 0,
            'sudah_dikoreksi' => false,
            'essay_sudah_dinilai' => false,
        ];
    }
}
