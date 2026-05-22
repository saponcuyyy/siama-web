<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // mulai_at & selesai_at in peserta_ujian were stored as UTC (set by now())
        // but app timezone is now Asia/Jakarta, so +7 hours to keep the same real time
        DB::statement('UPDATE peserta_ujian SET mulai_at = DATE_ADD(mulai_at, INTERVAL 7 HOUR) WHERE mulai_at IS NOT NULL');
        DB::statement('UPDATE peserta_ujian SET selesai_at = DATE_ADD(selesai_at, INTERVAL 7 HOUR) WHERE selesai_at IS NOT NULL');
    }

    public function down(): void
    {
        DB::statement('UPDATE peserta_ujian SET mulai_at = DATE_SUB(mulai_at, INTERVAL 7 HOUR) WHERE mulai_at IS NOT NULL');
        DB::statement('UPDATE peserta_ujian SET selesai_at = DATE_SUB(selesai_at, INTERVAL 7 HOUR) WHERE selesai_at IS NOT NULL');
    }
};
