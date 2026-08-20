<?php

namespace Database\Factories;

use App\Models\PaketUjian;
use App\Models\Rombel;
use App\Models\SesiUjian;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SesiUjianFactory extends Factory
{
    protected $model = SesiUjian::class;

    public function definition(): array
    {
        return [
            'paket_ujian_id' => PaketUjian::factory(),
            'rombel_id' => Rombel::factory(),
            'nama_sesi' => fake()->word(),
            'token' => strtoupper(fake()->unique()->bothify('??????')),
            'waktu_mulai' => now(),
            'waktu_selesai' => now()->addHours(2),
            'toleransi_menit' => 15,
            'status' => 'berlangsung',
            'max_pelanggaran' => 3,
            'wajib_fullscreen' => true,
            'dibuat_oleh' => User::factory(),
        ];
    }
}
