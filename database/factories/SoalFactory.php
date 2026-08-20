<?php

namespace Database\Factories;

use App\Models\BankSoal;
use App\Models\Soal;
use Illuminate\Database\Eloquent\Factories\Factory;

class SoalFactory extends Factory
{
    protected $model = Soal::class;

    public function definition(): array
    {
        return [
            'bank_soal_id' => BankSoal::factory(),
            'tipe' => 'pg',
            'pertanyaan' => fake()->paragraph(),
            'bobot' => 2,
            'tingkat_kesulitan' => 'sedang',
            'kunci_jawaban' => 'A',
            'pembahasan' => fake()->paragraph(),
            'urutan' => 1,
        ];
    }
}
