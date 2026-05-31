<?php

namespace Database\Seeders;

use App\Models\BankSoal;
use App\Models\Guru;
use App\Models\MataPelajaran;
use App\Models\Rombel;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@siama.sch.id',
            'password' => bcrypt('Passw0rd#@!'),
        ]);
        $admin->assignRole('super_admin');

        // Seed Academic Master Data
        $tahun = TahunAjaran::create(['nama' => '2025/2026', 'is_active' => true]);
        $semester = Semester::create(['nama' => 'Ganjil', 'is_active' => true]);

        $this->call(MataPelajaranSeeder::class);
        $mapel = MataPelajaran::where('kode', 'MTK')->where('tingkat', 'XII')->first();

        $userGuru = User::create([
            'name' => 'Budi Guru',
            'email' => 'guru@siama.sch.id',
            'password' => bcrypt('password'),
        ]);
        $userGuru->assignRole('guru');

        $guru = Guru::create([
            'user_id' => $userGuru->id,
            'nip' => '198001012005011001',
            'nama' => 'Budi Guru, S.Pd',
        ]);

        $rombel = Rombel::create([
            'nama' => 'XII IPA 1',
            'tingkat' => 'XII',
            'tahun_ajaran_id' => $tahun->id,
            'guru_id' => $guru->id,
        ]);

        $userSiswa = User::create([
            'name' => 'Andi Siswa',
            'email' => '0012345678',
            'password' => bcrypt('15082005*'),
        ]);
        $userSiswa->assignRole('siswa');

        Siswa::create([
            'user_id' => $userSiswa->id,
            'rombel_id' => $rombel->id,
            'nisn' => '0012345678',
            'nama' => 'Andi Siswa',
            'tanggal_lahir' => '2005-08-15',
            'status_lulus' => 'lulus',
        ]);

        $this->call(GuruSeeder::class);
        $this->call(DummyDataSeeder::class);

        // Seed a default Bank Soal for testing
        BankSoal::create([
            'mata_pelajaran_id' => $mapel->id,
            'kode' => 'BANK-MTK-01',
            'nama' => 'Bank Soal Matematika Wajib Kelas XII',
            'dibuat_oleh' => $admin->id,
            'deskripsi' => 'Kumpulan soal latihan matematika wajib kelas XII.',
        ]);
    }
}
