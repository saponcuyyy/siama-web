<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rombel_sesi_ujian', function (Blueprint $table) {
            $table->foreignId('sesi_ujian_id')
                  ->constrained('sesi_ujian')->cascadeOnDelete();
            $table->foreignId('rombel_id')
                  ->constrained('rombel')->cascadeOnDelete();
            $table->primary(['sesi_ujian_id', 'rombel_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rombel_sesi_ujian');
    }
};
