<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jam per minggu kini diisi per kelas (rombel): satu baris pivot
     * = satu penugasan guru mengajar mapel tertentu di kelas tertentu.
     * Baris dengan rombel_id NULL berarti jam umum tanpa kelas spesifik.
     */
    public function up(): void
    {
        Schema::table('guru_mata_pelajaran', function (Blueprint $table) {
            // Index pengganti untuk FK guru_id agar composite PK dapat dilepas
            // (InnoDB menuntut FK selalu tercakup oleh sebuah index).
            $table->index('guru_id');
            $table->dropPrimary();
            $table->id()->first();
            $table->foreignId('rombel_id')
                ->nullable()
                ->after('mata_pelajaran_id')
                ->constrained('rombel')
                ->nullOnDelete();
            $table->unique(['guru_id', 'mata_pelajaran_id', 'rombel_id'], 'gmp_unique_assignment');
        });
    }

    public function down(): void
    {
        Schema::table('guru_mata_pelajaran', function (Blueprint $table) {
            $table->dropForeign(['rombel_id']);
            $table->dropUnique('gmp_unique_assignment');
            $table->dropColumn('rombel_id');
        });

        // Gabungkan penugasan per kelas kembali ke satu baris per (guru, mapel).
        Schema::rename('guru_mata_pelajaran', 'gmp_rollback_old');

        Schema::create('guru_mata_pelajaran', function (Blueprint $table) {
            $table->foreignId('guru_id')->constrained('guru')->cascadeOnDelete();
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajaran')->cascadeOnDelete();
            $table->unsignedInteger('jam_per_minggu')->default(0);
            $table->timestamps();
            $table->primary(['guru_id', 'mata_pelajaran_id']);
        });

        DB::statement(
            'INSERT INTO guru_mata_pelajaran (guru_id, mata_pelajaran_id, jam_per_minggu, created_at, updated_at)
             SELECT guru_id, mata_pelajaran_id, SUM(jam_per_minggu), MAX(created_at), MAX(updated_at)
             FROM gmp_rollback_old GROUP BY guru_id, mata_pelajaran_id'
        );

        Schema::drop('gmp_rollback_old');
    }
};
