<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Siswa;

class SiswaSeeder extends Seeder
{
    public function run()
    {
        Siswa::create([
            'nisn' => '123456789',
            'nama' => 'Budi Santoso',
            'tanggal_lahir' => '2006-05-15',
            'status_lulus' => 'lulus',
            'keterangan' => 'Selamat, Anda lulus dengan nilai memuaskan!',
        ]);

        Siswa::create([
            'nisn' => '987654321',
            'nama' => 'Siti Aminah',
            'tanggal_lahir' => '2006-08-20',
            'status_lulus' => 'tidak_lulus',
            'keterangan' => 'Maaf, Anda belum lulus. Silakan ikuti program perbaikan.',
        ]);
        
        Siswa::create([
            'nisn' => '111111111',
            'nama' => 'Andi Darmawan',
            'tanggal_lahir' => '2006-01-01',
            'status_lulus' => 'ditunda',
            'keterangan' => 'Status kelulusan Anda ditunda karena dokumen administrasi belum lengkap.',
        ]);
    }
}
