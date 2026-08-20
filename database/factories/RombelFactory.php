<?php

namespace Database\Factories;

use App\Models\Guru;
use App\Models\Rombel;
use App\Models\TahunAjaran;
use Illuminate\Database\Eloquent\Factories\Factory;

class RombelFactory extends Factory
{
    protected $model = Rombel::class;

    public function definition(): array
    {
        return [
            'nama' => fake()->unique()->word() . ' Class',
            'tingkat' => 'XII',
            'jurusan' => 'IPA',
            'tahun_ajaran_id' => TahunAjaran::factory(),
            'guru_id' => Guru::factory(),
        ];
    }
}
