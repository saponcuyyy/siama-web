<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\MataPelajaran;
use App\Models\Rombel;
use App\Models\TahunAjaran;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JadwalDummySeeder extends Seeder
{
    /**
     * Struktur mapel & alokasi JP mengikuti tabel beban mengajar sekolah.
     *
     * Format: [Nama Mapel, Kode, Tingkat, Jurusan, JP/Kelas, [Guru]]
     * Kelompok kelas: -1 s/d -3 = IPA, -4 s/d -6 = IPS.
     */
    public function run(): void
    {
        $tahunAjaran = TahunAjaran::where('is_active', true)->first()
            ?? TahunAjaran::create(['nama' => '2025/2026', 'is_active' => true]);

        // ─── 1. Rombel X-1 .. XII-6 ────────────────────────────────────────────
        foreach (['X', 'XI', 'XII'] as $tingkat) {
            for ($i = 1; $i <= 6; $i++) {
                Rombel::updateOrCreate(
                    ['nama' => "{$tingkat}-{$i}", 'tahun_ajaran_id' => $tahunAjaran->id],
                    ['tingkat' => $tingkat, 'jurusan' => $i <= 3 ? 'IPA' : 'IPS']
                );
            }
        }

        $this->command->info('Rombel X-1 s.d. XII-6 disiapkan.');

        // ─── 2. Bersihkan jadwal & mapel lama ──────────────────────────────────
        Jadwal::query()->delete();

        DB::table('guru_mata_pelajaran')->delete();
        $mapelTerhapus = 0;
        $mapelTerlewat = 0;
        foreach (MataPelajaran::withTrashed()->get() as $m) {
            try {
                $m->forceDelete();
                $mapelTerhapus++;
            } catch (\Throwable $e) {
                // Masih dipakai modul lain (mis. bank soal): sembunyikan via soft delete.
                // Pakai query langsung karena flag forceDeleting pada instance sudah aktif.
                DB::table('mata_pelajaran')->where('id', $m->id)->update(['deleted_at' => now()]);
                $mapelTerlewat++;
            }
        }

        $this->command->info("Mapel lama dihapus: {$mapelTerhapus}".($mapelTerlewat > 0 ? " ({$mapelTerlewat} terlewat karena masih dipakai modul lain)" : '').'.');

        // ─── 3. Kurikulum & penugasan guru ─────────────────────────────────────
        $mapelData = [
            // ── Kelas X (umum) ──
            ['Bahasa Indonesia',        'BIND', 'X', null, 4, ['Nuradliani']],
            ['Pendidikan Pancasila',    'PP',   'X', null, 2, ['Kiki Octania']],
            ['Bahasa Inggris Wajib',    'BIGW', 'X', null, 3, ['Gunawan']],
            ['Matematika',              'MTK',  'X', null, 4, ['Yuanda Elsa Zahara']],
            ['Fisika',                  'FIS',  'X', null, 3, ['Nurjanna Lubis']],
            ['Kimia',                   'KIM',  'X', null, 3, ['Meylia Syahfitri']],
            ['Biologi',                 'BIO',  'X', null, 3, ['Nong Suita']],
            ['Sejarah Indonesia',       'SEJ',  'X', null, 3, ['Chusnul Khotimah']],
            ['Ekonomi',                 'EKO',  'X', null, 3, ['Darmilawati Pohan']],
            ['TIK',                     'TIK',  'X', null, 2, ['Setya Hadi Utomo']],
            ['PJOK',                    'PJO',  'X', null, 3, ['Muhammad Andre']],
            ['Agama Islam',             'AGM',  'X', null, 3, ['Nuramalina']],

            // ── Kelas X IPA (X-1 s/d X-3) ──
            ['Geografi',                'GEO',  'X', 'IPA', 3, ['Asna Susanti']],
            ['Sosiologi',               'SOS',  'X', 'IPA', 3, ['Kiki Octania']],
            ['Prakarya',                'PKW',  'X', 'IPA', 2, ['Darmilawati Pohan']],
            ['Agama Kristen',           'AGK',  'X', 'IPA', 3, ['Gustina Gultom']],

            // ── Kelas X IPS (X-4 s/d X-6) ──
            ['Geografi',                'GEO',  'X', 'IPS', 3, ['Tukini']],
            ['Sosiologi',               'SOS',  'X', 'IPS', 3, ['Sasmitha Putri']],
            ['Prakarya',                'PKW',  'X', 'IPS', 2, ['Meylia Syahfitri']],

            // ── Kelas XI (umum) ──
            ['Bahasa Indonesia',        'BIND', 'XI', null, 4, ['Agustinawaty']],
            ['Pendidikan Pancasila',    'PP',   'XI', null, 2, ['Sasmitha Putri']],
            ['Seni Budaya',             'SBD',  'XI', null, 2, ['Siti Khodijah Batu Bara']],
            ['Matematika',              'MTK',  'XI', null, 4, ['Cut Mutiara']],
            ['PJOK',                    'PJO',  'XI', null, 3, ['Apriani']],

            // ── Kelas XI IPA (XI-1 s/d XI-3) ──
            ['Bahasa Inggris Wajib',    'BIGW', 'XI', 'IPA', 3, ['Gunawan']],
            ['Fisika',                  'FIS',  'XI', 'IPA', 5, ['Nurjanna Lubis']],
            ['Kimia',                   'KIM',  'XI', 'IPA', 5, ['Murnihayati Purba']],
            ['Biologi',                 'BIO',  'XI', 'IPA', 5, ['Lasmauli Tampubolon']],
            ['TIK',                     'TIK',  'XI', 'IPA', 5, ['Setya Hadi Utomo']],
            ['Agama Islam',             'AGM',  'XI', 'IPA', 3, ['Fatimah']],
            ['Matematika Lanjutan',     'MLJ',  'XI', 'IPA', 5, ['Syahriani Efendi']],

            // ── Kelas XI IPS (XI-4 s/d XI-6) ──
            ['Bahasa Inggris Wajib',    'BIGW', 'XI', 'IPS', 3, ['Sartika Panjaitan']],
            ['Sejarah Indonesia',       'SEJ',  'XI', 'IPS', 2, ['Chusnul Khotimah']],
            ['Ekonomi',                 'EKO',  'XI', 'IPS', 5, ['Maya Sari']],
            ['Agama Islam',             'AGM',  'XI', 'IPS', 3, ['M. Irfan']],
            ['Prakarya',                'PKW',  'XI', 'IPS', 2, ['Helena CH J Pasaribu']],

            // ── Kelas XII (umum) ──
            ['Pendidikan Pancasila',    'PP',   'XII', null, 2, ['Abdul Wahid']],
            ['Bahasa Indonesia',        'BIND', 'XII', null, 4, ['Suningsih']],
            ['Seni Budaya',             'SBD',  'XII', null, 2, ['Siti Khodijah Batu Bara']],
            ['Matematika',              'MTK',  'XII', null, 4, ['Lisna Sujati']],
            ['Bahasa Inggris Wajib',    'BIGW', 'XII', null, 3, ['Sartika Panjaitan']],
            ['Sejarah Indonesia',       'SEJ',  'XII', null, 2, ['Arbaiyah Batubara']],

            // ── Kelas XII IPA (XII-1 s/d XII-3) ──
            ['Biologi',                 'BIO',  'XII', 'IPA', 5, ['Nong Suita']],
            ['Kimia',                   'KIM',  'XII', 'IPA', 5, ['Murnihayati Purba']],
            ['Ekonomi',                 'EKO',  'XII', 'IPA', 5, ['Maya Sari']],
            ['Prakarya',                'PKW',  'XII', 'IPA', 2, ['Fatimah']],
            ['Agama Kristen',           'AGK',  'XII', 'IPA', 3, ['Gustina Gultom']],

            // ── Kelas XII IPS (XII-4 s/d XII-6) ──
            ['Biologi',                 'BIO',  'XII', 'IPS', 5, ['Lasmauli Tampubolon']],
            ['Ekonomi',                 'EKO',  'XII', 'IPS', 5, ['Helena CH J Pasaribu']],
            ['Prakarya',                'PKW',  'XII', 'IPS', 2, ['Setya Hadi Utomo']],
            ['Agama Islam',             'AGM',  'XII', 'IPS', 3, ['Fatimah']],
            ['Geografi',                'GEO',  'XII', 'IPS', 5, ['Asna Susanti']],
            ['Agama Kristen',           'AGK',  'XII', 'IPS', 3, ['Gustina Gultom']],

            // ── Mapel literasi (tanpa alokasi jam tetap; hanya inventaris) ──
            ['Bahasa Inggris Literasi', 'BIGL', 'XII', null,  0, ['Gunawan', 'Sartika Panjaitan']],
            ['Sejarah Literasi',        'SEJL', 'XII', null,  0, ['Arbaiyah Batubara']],
            ['Matematika Literasi',     'MTKL', 'XI',  null,  0, ['Setya Hadi Utomo']],
        ];

        $guruByNama = Guru::get()->keyBy(
            fn ($g) => strtolower(trim(explode(',', $g->nama)[0]))
        );
        $pivotRows = [];
        $mapelDibuat = 0;

        foreach ($mapelData as [$nama, $kode, $tingkat, $jurusan, $jam, $guruNamaList]) {
            $mapel = MataPelajaran::create([
                'nama' => $nama,
                'kode' => $kode.'_'.$tingkat.'_'.($jurusan ?? 'U'),
                'tingkat' => $tingkat,
                'jurusan' => $jurusan,
                'jam_per_minggu' => 0,
            ]);

            $totalJpMapel = 0;
            foreach ($guruNamaList as $guruNama) {
                $guru = $guruByNama->get(strtolower(trim($guruNama)));
                if (! $guru) {
                    $this->command->warn("Guru tidak ditemukan: {$guruNama}");
                    continue;
                }

                $pivotRows[] = [
                    'guru_id' => $guru->id,
                    'mata_pelajaran_id' => $mapel->id,
                    'jam_per_minggu' => $jam,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $totalJpMapel += $jam;
            }

            $mapel->update(['jam_per_minggu' => $totalJpMapel]);
            $mapelDibuat++;
        }

        foreach (array_chunk($pivotRows, 200) as $chunk) {
            DB::table('guru_mata_pelajaran')->insert($chunk);
        }

        $this->command->info("Mapel dibuat: {$mapelDibuat}, penugasan guru: ".count($pivotRows).'.');
        $this->command->info('');

        // ─── 4. Summary ────────────────────────────────────────────────────────
        $rombelsBaru = Rombel::where('tahun_ajaran_id', $tahunAjaran->id)
            ->whereIn('nama', collect(['X', 'XI', 'XII'])->flatMap(
                fn ($t) => collect(range(1, 6))->map(fn ($i) => "{$t}-{$i}")
            ))
            ->get();

        $summary = [];
        foreach (['X', 'XI', 'XII'] as $tingkat) {
            foreach ($rombelsBaru->where('tingkat', $tingkat) as $rombel) {
                $totalJp = MataPelajaran::query()
                    ->where('tingkat', $tingkat)
                    ->where(function ($q) use ($rombel) {
                        $q->whereNull('jurusan')->orWhere('jurusan', $rombel->jurusan);
                    })
                    ->where('jam_per_minggu', '>', 0)
                    ->sum('jam_per_minggu');

                $summary[] = [$rombel->nama, $rombel->jurusan ?? 'Umum', $totalJp];
            }
        }

        $this->command->table(['Rombel', 'Jurusan', 'Total JP/Minggu'], $summary);
        $this->command->info("\nSiap! Buka menu Jadwal dan klik Generate Jadwal untuk testing.");
    }
}
