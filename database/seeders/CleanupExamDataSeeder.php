<?php

namespace Database\Seeders;

use App\Models\SesiUjian;
use App\Models\PesertaUjian;
use App\Models\JawabanSiswa;
use App\Models\LogUjian;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CleanupExamDataSeeder extends Seeder
{
    public function run(): void
    {
        $countSesi = SesiUjian::count();
        $countPeserta = PesertaUjian::count();
        $countJawaban = JawabanSiswa::count();
        $countLog = LogUjian::count();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        JawabanSiswa::truncate();
        LogUjian::truncate();
        PesertaUjian::truncate();
        DB::table('rombel_sesi_ujian')->truncate();
        SesiUjian::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->command->info("Data ujian berhasil dibersihkan:");
        $this->command->info("  - sesi_ujian:       {$countSesi} → 0");
        $this->command->info("  - rombel_sesi_ujian: deleted → 0");
        $this->command->info("  - peserta_ujian:    {$countPeserta} → 0");
        $this->command->info("  - jawaban_siswa:    {$countJawaban} → 0");
        $this->command->info("  - log_ujian:        {$countLog} → 0");
    }
}
