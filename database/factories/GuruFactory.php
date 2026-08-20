<?php

namespace Database\Factories;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GuruFactory extends Factory
{
    protected $model = Guru::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'nip' => fake()->unique()->numerify('19##########20##'),
            'nama' => fake()->name(),
            'jabatan' => 'Guru Mata Pelajaran',
            'tanggal_lahir' => fake()->date('Y-m-d', '2000-01-01'),
        ];
    }
}
