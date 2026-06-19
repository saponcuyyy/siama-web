<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rombel', function (Blueprint $table) {
            if (!Schema::hasColumn('rombel', 'jurusan')) {
                $table->enum('jurusan', ['IPA', 'IPS'])->nullable()->after('tingkat');
            }
        });

        Schema::table('mata_pelajaran', function (Blueprint $table) {
            if (!Schema::hasColumn('mata_pelajaran', 'jam_per_minggu')) {
                $table->integer('jam_per_minggu')->default(4)->after('jurusan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rombel', function (Blueprint $table) {
            $table->dropColumn('jurusan');
        });

        Schema::table('mata_pelajaran', function (Blueprint $table) {
            $table->dropColumn('jam_per_minggu');
        });
    }
};
