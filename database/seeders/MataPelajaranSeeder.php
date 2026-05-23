<?php

namespace Database\Seeders;

use App\Models\MataPelajaran;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MataPelajaranSeeder extends Seeder
{
    private array $common = [
        ['kode' => 'PAI',   'nama' => 'Pendidikan Agama Islam & Budi Pekerti'],
        ['kode' => 'PP',    'nama' => 'Pendidikan Pancasila'],
        ['kode' => 'BINDO', 'nama' => 'Bahasa Indonesia'],
        ['kode' => 'MTK',   'nama' => 'Matematika Wajib'],
        ['kode' => 'BING',  'nama' => 'Bahasa Inggris'],
        ['kode' => 'PJOK',  'nama' => 'PJOK'],
        ['kode' => 'SJI',   'nama' => 'Sejarah Indonesia'],
        ['kode' => 'SB',    'nama' => 'Seni Budaya'],
        ['kode' => 'PKWU',  'nama' => 'Prakarya dan Kewirausahaan'],
        ['kode' => 'INF',   'nama' => 'Informatika'],
        ['kode' => 'BSN',   'nama' => 'Bahasa Sunda'],
    ];

    private array $ipa = [
        ['kode' => 'MTK-P', 'nama' => 'Matematika Peminatan'],
        ['kode' => 'FIS',   'nama' => 'Fisika'],
        ['kode' => 'KIM',   'nama' => 'Kimia'],
        ['kode' => 'BIO',   'nama' => 'Biologi'],
    ];

    private array $ips = [
        ['kode' => 'GEO', 'nama' => 'Geografi'],
        ['kode' => 'SOS', 'nama' => 'Sosiologi'],
        ['kode' => 'EKO', 'nama' => 'Ekonomi'],
    ];

    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        MataPelajaran::withTrashed()->forceDelete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Kelas X — hanya mapel umum (belum ada penjurusan)
        foreach ($this->common as $item) {
            MataPelajaran::create([
                'kode'    => $item['kode'],
                'nama'    => $item['nama'],
                'tingkat' => 'X',
                'jurusan' => null,
            ]);
        }

        // Kelas XI & XII — mapel umum + peminatan IPA/IPS
        foreach (['XI', 'XII'] as $tingkat) {
            foreach ($this->common as $item) {
                MataPelajaran::create([
                    'kode'    => $item['kode'],
                    'nama'    => $item['nama'],
                    'tingkat' => $tingkat,
                    'jurusan' => null,
                ]);
            }

            foreach ($this->ipa as $item) {
                MataPelajaran::create([
                    'kode'    => $item['kode'],
                    'nama'    => $item['nama'],
                    'tingkat' => $tingkat,
                    'jurusan' => 'IPA',
                ]);
            }

            foreach ($this->ips as $item) {
                MataPelajaran::create([
                    'kode'    => $item['kode'],
                    'nama'    => $item['nama'],
                    'tingkat' => $tingkat,
                    'jurusan' => 'IPS',
                ]);
            }
        }
    }
}
