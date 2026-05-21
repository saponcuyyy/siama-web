<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Guru;
use App\Models\Rombel;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Get or Create active Tahun Ajaran
        $tahunAjaran = TahunAjaran::where('is_active', true)->first();
        if (!$tahunAjaran) {
            $tahunAjaran = TahunAjaran::create([
                'nama' => '2025/2026',
                'is_active' => true
            ]);
        }

        // 2. Data Guru Dummy
        $dataGuru = [
            ['nama' => 'Drs. H. Ahmad Fauzi, M.Pd', 'nip' => '197508122000031001', 'email' => 'ahmad.fauzi@siama.sch.id'],
            ['nama' => 'Rina Wijayanti, S.Pd', 'nip' => '198504232009042002', 'email' => 'rina.wijayanti@siama.sch.id'],
            ['nama' => 'Eko Prasetyo, S.Kom', 'nip' => '199011052015021003', 'email' => 'eko.prasetyo@siama.sch.id'],
            ['nama' => 'Siti Rahmawati, S.Si', 'nip' => '198802152010122001', 'email' => 'siti.rahmawati@siama.sch.id'],
            ['nama' => 'Dedi Iskandar, M.Si', 'nip' => '198207192008011002', 'email' => 'dedi.iskandar@siama.sch.id'],
        ];

        $gurus = [];
        foreach ($dataGuru as $g) {
            $user = User::where('email', $g['email'])->first();
            if (!$user) {
                $user = User::create([
                    'name' => $g['nama'],
                    'email' => $g['email'],
                    'password' => Hash::make('password'),
                ]);
                $user->assignRole('guru');
            }

            $guru = Guru::where('nip', $g['nip'])->first();
            if (!$guru) {
                $guru = Guru::create([
                    'user_id' => $user->id,
                    'nip' => $g['nip'],
                    'nama' => $g['nama'],
                ]);
            }
            $gurus[] = $guru;
        }

        // 3. Data Rombel Dummy (assigned to Wali Kelas from Gurus above)
        $dataRombel = [
            ['nama' => 'X IPA 1', 'tingkat' => 'X', 'guru_idx' => 0],
            ['nama' => 'X IPS 1', 'tingkat' => 'X', 'guru_idx' => 1],
            ['nama' => 'XI IPA 1', 'tingkat' => 'XI', 'guru_idx' => 2],
            ['nama' => 'XI IPS 1', 'tingkat' => 'XI', 'guru_idx' => 3],
            ['nama' => 'XII IPA 2', 'tingkat' => 'XII', 'guru_idx' => 4],
        ];

        foreach ($dataRombel as $r) {
            $wali = $gurus[$r['guru_idx']];
            $rombel = Rombel::where('nama', $r['nama'])
                ->where('tahun_ajaran_id', $tahunAjaran->id)
                ->first();

            if (!$rombel) {
                Rombel::create([
                    'nama' => $r['nama'],
                    'tingkat' => $r['tingkat'],
                    'tahun_ajaran_id' => $tahunAjaran->id,
                    'guru_id' => $wali->id,
                ]);
            }
        }

        // 4. Clean up existing Siswa data and their Users (to prevent duplication)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        // Delete users of role 'siswa'
        $siswaUserIds = User::role('siswa')->pluck('id');
        User::whereIn('id', $siswaUserIds)->delete();
        // Truncate siswa table
        Siswa::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 5. Generate exactly 36 students per Rombel using Faker
        $faker = Faker::create('id_ID');
        $rombels = Rombel::all();
        $passwordHash = Hash::make('password');

        foreach ($rombels as $rombel) {
            // Determine birth year based on grade levels
            $birthYear = 2010; // Default for class X
            if ($rombel->tingkat === 'XI') {
                $birthYear = 2009;
            } elseif ($rombel->tingkat === 'XII') {
                $birthYear = 2008;
            }

            for ($i = 1; $i <= 36; $i++) {
                // Unique NISN pattern: 00 + birthYear_last_two_digits + rombel_id_padded + index_padded
                // E.g. Class X (ID 1), index 5 -> 0010001005 (10 digits)
                $nisn = sprintf("00%02d%03d%03d", $birthYear % 100, $rombel->id, $i);
                
                $gender = $faker->randomElement(['male', 'female']);
                $name = $faker->name($gender);
                $birthDate = sprintf("%d-%02d-%02d", $birthYear, $faker->numberBetween(1, 12), $faker->numberBetween(1, 28));

                // Create user
                $formattedPassword = date('dmY', strtotime($birthDate)) . '*';
                $user = User::create([
                    'name' => $name,
                    'email' => $nisn,
                    'password' => Hash::make($formattedPassword),
                ]);
                $user->assignRole('siswa');

                // Create siswa record
                Siswa::create([
                    'user_id' => $user->id,
                    'rombel_id' => $rombel->id,
                    'nisn' => $nisn,
                    'nama' => $name,
                    'tanggal_lahir' => $birthDate,
                    'status_lulus' => 'ditunda',
                ]);
            }
        }
    }
}
