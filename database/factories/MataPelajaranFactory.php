<?php

namespace Database\Factories;

use App\Models\MataPelajaran;
use Illuminate\Database\Eloquent\Factories\Factory;

class MataPelajaranFactory extends Factory
{
    protected $model = MataPelajaran::class;

    public function definition(): array
    {
        $code = fake()->unique()->word();
        return [
            'nama' => fake()->words(2, true),
            'kode' => strtoupper($code),
            'tingkat' => fake()->randomElement(['X', 'XI', 'XII']),
            'jurusan' => 'IPA',
            'jam_per_minggu' => 2,
        ];
    }
}
