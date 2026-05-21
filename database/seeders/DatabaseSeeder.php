<?php

namespace Database\Seeders;

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

        $admin = \App\Models\User::create([
            'name' => 'Super Admin',
            'email' => 'admin@siama.sch.id',
            'password' => bcrypt('Passw0rd#@!'),
        ]);
        $admin->assignRole('super_admin');

        // Seed Academic Master Data
        $tahun = \App\Models\TahunAjaran::create(['nama' => '2025/2026', 'is_active' => true]);
        $semester = \App\Models\Semester::create(['nama' => 'Ganjil', 'is_active' => true]);
        
        $mapel = \App\Models\MataPelajaran::create(['nama' => 'Matematika', 'kode' => 'MTK']);
        
        $userGuru = \App\Models\User::create([
            'name' => 'Budi Guru',
            'email' => 'guru@siama.sch.id',
            'password' => bcrypt('password'),
        ]);
        $userGuru->assignRole('guru');
        
        $guru = \App\Models\Guru::create([
            'user_id' => $userGuru->id,
            'nip' => '198001012005011001',
            'nama' => 'Budi Guru, S.Pd',
        ]);

        $rombel = \App\Models\Rombel::create([
            'nama' => 'XII IPA 1',
            'tingkat' => 'XII',
            'tahun_ajaran_id' => $tahun->id,
            'guru_id' => $guru->id,
        ]);

        $userSiswa = \App\Models\User::create([
            'name' => 'Andi Siswa',
            'email' => '0012345678',
            'password' => bcrypt('15082005*'),
        ]);
        $userSiswa->assignRole('siswa');

        \App\Models\Siswa::create([
            'user_id' => $userSiswa->id,
            'rombel_id' => $rombel->id,
            'nisn' => '0012345678',
            'nama' => 'Andi Siswa',
            'tanggal_lahir' => '2005-08-15',
            'status_lulus' => 'lulus',
        ]);

        $this->call(DummyDataSeeder::class);

        // Seed a default Bank Soal for testing
        \App\Models\BankSoal::create([
            'mata_pelajaran_id' => $mapel->id,
            'kode' => 'BANK-MTK-01',
            'nama' => 'Bank Soal Matematika Wajib Kelas XII',
            'dibuat_oleh' => $admin->id,
            'deskripsi' => 'Kumpulan soal latihan matematika wajib kelas XII.'
        ]);
    }
}
