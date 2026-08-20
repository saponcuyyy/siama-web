<?php

namespace Database\Factories;

use App\Models\BankSoal;
use App\Models\Guru;
use App\Models\MataPelajaran;
use App\Models\TahunAjaran;
use Illuminate\Database\Eloquent\Factories\Factory;

class BankSoalFactory extends Factory
{
    protected $model = BankSoal::class;

    public function definition(): array
    {
        return [
            'mata_pelajaran_id' => MataPelajaran::factory(),
            'guru_id' => Guru::factory(),
            'tahun_ajaran_id' => TahunAjaran::factory(),
            'judul' => fake()->sentence(3),
            'deskripsi' => fake()->paragraph(),
            'tingkat' => 'XII',
            'is_active' => true,
        ];
    }
}
