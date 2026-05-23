<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mata_pelajaran', function (Blueprint $table) {
            $table->dropUnique('mata_pelajaran_kode_unique');
            $table->enum('tingkat', ['X', 'XI', 'XII'])->nullable()->after('kode');
            $table->enum('jurusan', ['IPA', 'IPS'])->nullable()->after('tingkat');
        });
    }

    public function down(): void
    {
        Schema::table('mata_pelajaran', function (Blueprint $table) {
            $table->dropColumn('jurusan');
            $table->dropColumn('tingkat');
            $table->string('kode')->unique()->change();
        });
    }
};
