<?php

namespace Database\Factories;

use App\Models\JawabanSiswa;
use App\Models\PesertaUjian;
use App\Models\Soal;
use Illuminate\Database\Eloquent\Factories\Factory;

class JawabanSiswaFactory extends Factory
{
    protected $model = JawabanSiswa::class;

    public function definition(): array
    {
        return [
            'peserta_ujian_id' => PesertaUjian::factory(),
            'soal_id' => Soal::factory(),
            'jawaban' => 'A',
            'is_benar' => null,
            'nilai' => null,
            'is_ragu' => false,
        ];
    }
}
