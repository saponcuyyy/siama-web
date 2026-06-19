<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\MataPelajaran;
use App\Models\Rombel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JadwalDummySeeder extends Seeder
{
    public function run(): void
    {
        $tahunAjaranId = 1;

        // ─── 1. Update Jurusan Rombel ─────────────────────────────────────────
        $rombelMap = [
            1 => ['jurusan' => 'IPA'],  // XII IPA 1
            2 => ['jurusan' => null],   // X IPA 1 (X = umum)
            3 => ['jurusan' => null],   // X IPS 1 (X = umum)
            4 => ['jurusan' => 'IPA'],  // XI IPA 1
            5 => ['jurusan' => 'IPS'],  // XI IPS 1
            6 => ['jurusan' => 'IPA'],  // XII IPA 2
            7 => ['jurusan' => 'IPA'],  // XI-IPA 2
        ];

        foreach ($rombelMap as $id => $data) {
            Rombel::where('id', $id)->update($data);
        }

        $this->command->info('Rombel jurusan updated.');

        // ─── 2. Create Mata Pelajaran ──────────────────────────────────────────

        // Kelas X (Umum) — semua jurusan
        $kelasX = [
            ['nama' => 'Pendidikan Agama',       'kode' => 'AGM', 'tingkat' => 'X',  'jurusan' => null, 'jam' => 3],
            ['nama' => 'PPKn',                    'kode' => 'PPK', 'tingkat' => 'X',  'jurusan' => null, 'jam' => 2],
            ['nama' => 'Bahasa Indonesia',        'kode' => 'BIN', 'tingkat' => 'X',  'jurusan' => null, 'jam' => 4],
            ['nama' => 'Matematika',              'kode' => 'MTK', 'tingkat' => 'X',  'jurusan' => null, 'jam' => 4],
            ['nama' => 'Bahasa Inggris',          'kode' => 'BIG', 'tingkat' => 'X',  'jurusan' => null, 'jam' => 3],
            ['nama' => 'PJOK',                    'kode' => 'PJO', 'tingkat' => 'X',  'jurusan' => null, 'jam' => 3],
            ['nama' => 'Sejarah',                 'kode' => 'SEJ', 'tingkat' => 'X',  'jurusan' => null, 'jam' => 2],
            ['nama' => 'Seni Budaya',             'kode' => 'SBD', 'tingkat' => 'X',  'jurusan' => null, 'jam' => 2],
            ['nama' => 'Prakarya & Kewirausahaan', 'kode' => 'PKW', 'tingkat' => 'X',  'jurusan' => null, 'jam' => 2],
            ['nama' => 'Informatika',             'kode' => 'INF', 'tingkat' => 'X',  'jurusan' => null, 'jam' => 3],
        ];

        // Kelas XI IPA
        $kelasXI_IPA = [
            ['nama' => 'Pendidikan Agama',       'kode' => 'AGM', 'tingkat' => 'XI', 'jurusan' => 'IPA', 'jam' => 3],
            ['nama' => 'PPKn',                    'kode' => 'PPK', 'tingkat' => 'XI', 'jurusan' => 'IPA', 'jam' => 2],
            ['nama' => 'Bahasa Indonesia',        'kode' => 'BIN', 'tingkat' => 'XI', 'jurusan' => 'IPA', 'jam' => 4],
            ['nama' => 'Matematika',              'kode' => 'MTK', 'tingkat' => 'XI', 'jurusan' => 'IPA', 'jam' => 4],
            ['nama' => 'Bahasa Inggris',          'kode' => 'BIG', 'tingkat' => 'XI', 'jurusan' => 'IPA', 'jam' => 3],
            ['nama' => 'Fisika',                  'kode' => 'FIS', 'tingkat' => 'XI', 'jurusan' => 'IPA', 'jam' => 4],
            ['nama' => 'Kimia',                   'kode' => 'KIM', 'tingkat' => 'XI', 'jurusan' => 'IPA', 'jam' => 4],
            ['nama' => 'Biologi',                 'kode' => 'BIO', 'tingkat' => 'XI', 'jurusan' => 'IPA', 'jam' => 4],
            ['nama' => 'PJOK',                    'kode' => 'PJO', 'tingkat' => 'XI', 'jurusan' => 'IPA', 'jam' => 2],
            ['nama' => 'Sejarah',                 'kode' => 'SEJ', 'tingkat' => 'XI', 'jurusan' => 'IPA', 'jam' => 2],
            ['nama' => 'Seni Budaya',             'kode' => 'SBD', 'tingkat' => 'XI', 'jurusan' => 'IPA', 'jam' => 2],
        ];

        // Kelas XI IPS
        $kelasXI_IPS = [
            ['nama' => 'Pendidikan Agama',       'kode' => 'AGM', 'tingkat' => 'XI', 'jurusan' => 'IPS', 'jam' => 3],
            ['nama' => 'PPKn',                    'kode' => 'PPK', 'tingkat' => 'XI', 'jurusan' => 'IPS', 'jam' => 2],
            ['nama' => 'Bahasa Indonesia',        'kode' => 'BIN', 'tingkat' => 'XI', 'jurusan' => 'IPS', 'jam' => 4],
            ['nama' => 'Matematika',              'kode' => 'MTK', 'tingkat' => 'XI', 'jurusan' => 'IPS', 'jam' => 4],
            ['nama' => 'Bahasa Inggris',          'kode' => 'BIG', 'tingkat' => 'XI', 'jurusan' => 'IPS', 'jam' => 3],
            ['nama' => 'Ekonomi',                 'kode' => 'EKO', 'tingkat' => 'XI', 'jurusan' => 'IPS', 'jam' => 4],
            ['nama' => 'Sosiologi',               'kode' => 'SOS', 'tingkat' => 'XI', 'jurusan' => 'IPS', 'jam' => 4],
            ['nama' => 'Geografi',                'kode' => 'GEO', 'tingkat' => 'XI', 'jurusan' => 'IPS', 'jam' => 4],
            ['nama' => 'PJOK',                    'kode' => 'PJO', 'tingkat' => 'XI', 'jurusan' => 'IPS', 'jam' => 2],
            ['nama' => 'Sejarah',                 'kode' => 'SEJ', 'tingkat' => 'XI', 'jurusan' => 'IPS', 'jam' => 2],
            ['nama' => 'Seni Budaya',             'kode' => 'SBD', 'tingkat' => 'XI', 'jurusan' => 'IPS', 'jam' => 2],
        ];

        // Kelas XII IPA
        $kelasXII_IPA = [
            ['nama' => 'Pendidikan Agama',       'kode' => 'AGM', 'tingkat' => 'XII', 'jurusan' => 'IPA', 'jam' => 3],
            ['nama' => 'PPKn',                    'kode' => 'PPK', 'tingkat' => 'XII', 'jurusan' => 'IPA', 'jam' => 2],
            ['nama' => 'Bahasa Indonesia',        'kode' => 'BIN', 'tingkat' => 'XII', 'jurusan' => 'IPA', 'jam' => 4],
            ['nama' => 'Matematika',              'kode' => 'MTK', 'tingkat' => 'XII', 'jurusan' => 'IPA', 'jam' => 4],
            ['nama' => 'Bahasa Inggris',          'kode' => 'BIG', 'tingkat' => 'XII', 'jurusan' => 'IPA', 'jam' => 3],
            ['nama' => 'Fisika',                  'kode' => 'FIS', 'tingkat' => 'XII', 'jurusan' => 'IPA', 'jam' => 4],
            ['nama' => 'Kimia',                   'kode' => 'KIM', 'tingkat' => 'XII', 'jurusan' => 'IPA', 'jam' => 4],
            ['nama' => 'Biologi',                 'kode' => 'BIO', 'tingkat' => 'XII', 'jurusan' => 'IPA', 'jam' => 4],
            ['nama' => 'PJOK',                    'kode' => 'PJO', 'tingkat' => 'XII', 'jurusan' => 'IPA', 'jam' => 2],
            ['nama' => 'Sejarah',                 'kode' => 'SEJ', 'tingkat' => 'XII', 'jurusan' => 'IPA', 'jam' => 2],
            ['nama' => 'Seni Budaya',             'kode' => 'SBD', 'tingkat' => 'XII', 'jurusan' => 'IPA', 'jam' => 2],
        ];

        // Kelas XII IPS
        $kelasXII_IPS = [
            ['nama' => 'Pendidikan Agama',       'kode' => 'AGM', 'tingkat' => 'XII', 'jurusan' => 'IPS', 'jam' => 3],
            ['nama' => 'PPKn',                    'kode' => 'PPK', 'tingkat' => 'XII', 'jurusan' => 'IPS', 'jam' => 2],
            ['nama' => 'Bahasa Indonesia',        'kode' => 'BIN', 'tingkat' => 'XII', 'jurusan' => 'IPS', 'jam' => 4],
            ['nama' => 'Matematika',              'kode' => 'MTK', 'tingkat' => 'XII', 'jurusan' => 'IPS', 'jam' => 4],
            ['nama' => 'Bahasa Inggris',          'kode' => 'BIG', 'tingkat' => 'XII', 'jurusan' => 'IPS', 'jam' => 3],
            ['nama' => 'Ekonomi',                 'kode' => 'EKO', 'tingkat' => 'XII', 'jurusan' => 'IPS', 'jam' => 4],
            ['nama' => 'Sosiologi',               'kode' => 'SOS', 'tingkat' => 'XII', 'jurusan' => 'IPS', 'jam' => 4],
            ['nama' => 'Geografi',                'kode' => 'GEO', 'tingkat' => 'XII', 'jurusan' => 'IPS', 'jam' => 4],
            ['nama' => 'PJOK',                    'kode' => 'PJO', 'tingkat' => 'XII', 'jurusan' => 'IPS', 'jam' => 2],
            ['nama' => 'Sejarah',                 'kode' => 'SEJ', 'tingkat' => 'XII', 'jurusan' => 'IPS', 'jam' => 2],
            ['nama' => 'Seni Budaya',             'kode' => 'SBD', 'tingkat' => 'XII', 'jurusan' => 'IPS', 'jam' => 2],
        ];

        $allMapel = array_merge($kelasX, $kelasXI_IPA, $kelasXI_IPS, $kelasXII_IPA, $kelasXII_IPS);

        $createdIds = [];
        foreach ($allMapel as $item) {
            $mapel = MataPelajaran::create([
                'nama' => $item['nama'],
                'kode' => $item['kode'] . '_' . $item['tingkat'] . '_' . ($item['jurusan'] ?? 'U'),
                'tingkat' => $item['tingkat'],
                'jurusan' => $item['jurusan'],
                'jam_per_minggu' => $item['jam'],
            ]);
            $createdIds[] = $mapel->id;
        }

        $this->command->info('Mata Pelajaran created: ' . count($createdIds));

        // ─── 3. Assign Guru ke Mata Pelajaran ──────────────────────────────────

        $guru = Guru::where('id', '!=', 22)->get()->keyBy('id');

        $assignments = [
            // Agama → guru Agama
            'AGM' => [10, 18],          // Agus Salim, H. Syamsul Arifin

            // PPKn
            'PPK' => [2, 7],            // Drs. H. Ahmad Fauzi, Dra. Hj. Nurhayati

            // Bahasa Indonesia
            'BIN' => [1, 9, 13],        // Budi Guru, Yuni Astuti, Dewi Sartika

            // Matematika
            'MTK' => [5, 6, 14, 15],    // Siti Rahmawati, Dedi Iskandar, Dr. Muhammad Ridwan, Nina Marlina

            // Bahasa Inggris
            'BIG' => [3, 11],           // Rina Wijayanti, Fitri Handayani

            // PJOK
            'PJO' => [16, 21],          // Rudi Hartono, Ratna Dewi

            // Sejarah
            'SEJ' => [2, 8],            // Drs. H. Ahmad Fauzi, Dr. Hendra Gunawan

            // Seni Budaya
            'SBD' => [13, 17],          // Dewi Sartika, Lilis Suryani

            // Fisika
            'FIS' => [6, 14],           // Dedi Iskandar, Dr. Muhammad Ridwan

            // Kimia
            'KIM' => [5, 15],           // Siti Rahmawati, Nina Marlina

            // Biologi
            'BIO' => [7, 17],           // Dra. Hj. Nurhayati, Lilis Suryani

            // Ekonomi
            'EKO' => [1, 8],            // Budi Guru, Dr. Hendra Gunawan

            // Sosiologi
            'SOS' => [2, 9],            // Drs. H. Ahmad Fauzi, Yuni Astuti

            // Geografi
            'GEO' => [11, 17],          // Fitri Handayani, Lilis Suryani

            // Informatika
            'INF' => [4, 20],           // Eko Prasetyo, Dr. Antonius Wibowo

            // Prakarya
            'PKW' => [19, 20],          // Maria Ulfah, Dr. Antonius Wibowo
        ];

        $pivotData = [];
        $mapelByKodePrefix = MataPelajaran::all()->groupBy(fn($m) => explode('_', $m->kode)[0]);

        foreach ($assignments as $prefix => $guruIds) {
            $mapels = $mapelByKodePrefix->get($prefix, collect());
            foreach ($mapels as $mapel) {
                foreach ($guruIds as $guruId) {
                    if ($guru->has($guruId)) {
                        $pivotData[] = [
                            'guru_id' => $guruId,
                            'mata_pelajaran_id' => $mapel->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }
        }

        DB::table('guru_mata_pelajaran')->insert($pivotData);

        $this->command->info('Guru-Mapel assignments created: ' . count($pivotData));
        $this->command->info('');

        // ─── 4. Summary ────────────────────────────────────────────────────────
        $totalJamX = collect($kelasX)->sum('jam');
        $totalJamXIIPA = collect($kelasXI_IPA)->sum('jam');
        $totalJamXIIPS = collect($kelasXI_IPS)->sum('jam');

        $this->command->table(
            ['Rombel', 'Total Jam/Minggu', 'Keterangan'],
            [
                ['Kelas X (Umum)', $totalJamX, '10 mapel — ' . count($kelasX) . ' mapel'],
                ['Kelas XI IPA', $totalJamXIIPA, '11 mapel'],
                ['Kelas XI IPS', $totalJamXIIPS, '11 mapel'],
                ['Kelas XII IPA', collect($kelasXII_IPA)->sum('jam'), '11 mapel'],
                ['Kelas XII IPS', collect($kelasXII_IPS)->sum('jam'), '11 mapel'],
            ]
        );

        $this->command->info("\nSiap! Buka menu Jadwal dan klik Generate Jadwal untuk testing.");
    }
}
