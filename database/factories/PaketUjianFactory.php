<?php

namespace Database\Factories;

use App\Models\Guru;
use App\Models\MataPelajaran;
use App\Models\PaketUjian;
use App\Models\Semester;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaketUjianFactory extends Factory
{
    protected $model = PaketUjian::class;

    public function definition(): array
    {
        return [
            'mata_pelajaran_id' => MataPelajaran::factory(),
            'guru_id' => Guru::factory(),
            'tahun_ajaran_id' => TahunAjaran::factory(),
            'semester_id' => Semester::factory(),
            'dibuat_oleh' => User::factory(),
            'nama' => fake()->words(3, true),
            'kode' => strtoupper(fake()->unique()->bothify('PKT-###')),
            'jenis' => 'uh',
            'tingkat' => 'XII',
            'durasi_menit' => 90,
            'status' => 'published',
            'acak_soal' => true,
            'acak_jawaban' => true,
        ];
    }
}
