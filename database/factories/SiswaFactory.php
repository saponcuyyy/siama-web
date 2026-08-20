<?php

namespace Database\Factories;

use App\Models\Rombel;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SiswaFactory extends Factory
{
    protected $model = Siswa::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'rombel_id' => Rombel::factory(),
            'nisn' => fake()->unique()->numerify('##########'),
            'nama' => fake()->name(),
            'tanggal_lahir' => fake()->date('Y-m-d', '2008-01-01'),
            'agama' => 'Islam',
            'status_lulus' => 'lulus',
        ];
    }
}
