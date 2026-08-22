<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('penugasan_mengajar');
    }

    public function down(): void
    {
        Schema::create('penugasan_mengajar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('guru')->cascadeOnDelete();
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajaran')->cascadeOnDelete();
            $table->foreignId('rombel_id')->constrained('rombel')->cascadeOnDelete();
            $table->unsignedInteger('jam_per_minggu')->default(0);
            $table->timestamps();

            $table->unique(['guru_id', 'mata_pelajaran_id', 'rombel_id']);
            $table->index(['rombel_id', 'mata_pelajaran_id']);
        });
    }
};
