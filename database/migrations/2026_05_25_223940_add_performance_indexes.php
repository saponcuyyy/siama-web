<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('peserta_ujian', function (Blueprint $table) {
            if (! Schema::hasIndex('peserta_ujian', 'idx_peserta_siswa_id')) {
                $table->index('siswa_id', 'idx_peserta_siswa_id');
            }
            if (! Schema::hasIndex('peserta_ujian', 'idx_peserta_status')) {
                $table->index('status', 'idx_peserta_status');
            }
            if (! Schema::hasIndex('peserta_ujian', 'idx_peserta_sesi_status_siswa')) {
                $table->index(['sesi_ujian_id', 'status', 'siswa_id'], 'idx_peserta_sesi_status_siswa');
            }
        });

        Schema::table('jawaban_siswa', function (Blueprint $table) {
            if (! Schema::hasIndex('jawaban_siswa', 'idx_jawaban_peserta_soal_nilai')) {
                $table->index(['peserta_ujian_id', 'soal_id', 'nilai'], 'idx_jawaban_peserta_soal_nilai');
            }
        });

        Schema::table('log_ujian', function (Blueprint $table) {
            if (! Schema::hasIndex('log_ujian', 'idx_log_terjadi_at')) {
                $table->index('terjadi_at', 'idx_log_terjadi_at');
            }
        });

        Schema::table('sesi_ujian', function (Blueprint $table) {
            if (! Schema::hasIndex('sesi_ujian', 'idx_sesi_dibuat_oleh')) {
                $table->index('dibuat_oleh', 'idx_sesi_dibuat_oleh');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sesi_ujian', function (Blueprint $table) {
            $table->dropIndex('idx_sesi_dibuat_oleh');
        });

        Schema::table('log_ujian', function (Blueprint $table) {
            $table->dropIndex('idx_log_terjadi_at');
        });

        Schema::table('jawaban_siswa', function (Blueprint $table) {
            $table->dropIndex('idx_jawaban_peserta_soal_nilai');
        });

        Schema::table('peserta_ujian', function (Blueprint $table) {
            $table->dropIndex('idx_peserta_sesi_status_siswa');
            $table->dropIndex('idx_peserta_status');
            $table->dropIndex('idx_peserta_siswa_id');
        });
    }
};
