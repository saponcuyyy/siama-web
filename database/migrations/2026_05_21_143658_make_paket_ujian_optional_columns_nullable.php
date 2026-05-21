<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paket_ujian', function (Blueprint $table) {
            // Drop foreign key constraints before changing columns
            $table->dropForeign(['guru_id']);
            $table->dropForeign(['tahun_ajaran_id']);
            $table->dropForeign(['semester_id']);

            // Make previously required relational columns nullable
            $table->foreignId('guru_id')->nullable()->change();
            $table->foreignId('tahun_ajaran_id')->nullable()->change();
            $table->foreignId('semester_id')->nullable()->change();

            // Re-add foreign keys as nullable
            $table->foreign('guru_id')->references('id')->on('guru')->nullOnDelete();
            $table->foreign('tahun_ajaran_id')->references('id')->on('tahun_ajaran')->nullOnDelete();
            $table->foreign('semester_id')->references('id')->on('semester')->nullOnDelete();

            // Make enum columns nullable with defaults
            $table->enum('jenis', ['uh','uts','uas','pas','try_out','lainnya'])
                  ->nullable()->default(null)->change();
            $table->enum('tingkat', ['X','XI','XII'])
                  ->nullable()->default(null)->change();

            // Add dibuat_oleh that the controller uses (if not already present)
            if (!Schema::hasColumn('paket_ujian', 'dibuat_oleh')) {
                $table->foreignId('dibuat_oleh')
                      ->nullable()
                      ->constrained('users')
                      ->nullOnDelete();
            }

            // Add deskripsi if not present
            if (!Schema::hasColumn('paket_ujian', 'deskripsi')) {
                $table->text('deskripsi')->nullable()->after('nama');
            }
        });
    }

    public function down(): void
    {
        Schema::table('paket_ujian', function (Blueprint $table) {
            $table->dropForeign(['guru_id']);
            $table->dropForeign(['tahun_ajaran_id']);
            $table->dropForeign(['semester_id']);

            $table->foreignId('guru_id')->nullable(false)->change();
            $table->foreignId('tahun_ajaran_id')->nullable(false)->change();
            $table->foreignId('semester_id')->nullable(false)->change();

            $table->foreign('guru_id')->references('id')->on('guru')->restrictOnDelete();
            $table->foreign('tahun_ajaran_id')->references('id')->on('tahun_ajaran')->restrictOnDelete();
            $table->foreign('semester_id')->references('id')->on('semester')->restrictOnDelete();
        });
    }
};
