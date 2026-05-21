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
        // Drop existing plural siswas table
        Schema::dropIfExists('siswas');

        // 1. tahun_ajaran
        Schema::create('tahun_ajaran', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // e.g. "2025/2026"
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. semester
        Schema::create('semester', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // e.g. "Ganjil", "Genap"
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. mata_pelajaran
        Schema::create('mata_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('kode')->unique();
            $table->timestamps();
            $table->softDeletes();
        });

        // 4. guru
        Schema::create('guru', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nip')->unique();
            $table->string('nama');
            $table->timestamps();
            $table->softDeletes();
        });

        // 5. rombel
        Schema::create('rombel', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // e.g. "XII IPA 1"
            $table->enum('tingkat', ['X', 'XI', 'XII']);
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->restrictOnDelete();
            $table->foreignId('guru_id')->nullable()->constrained('guru')->nullOnDelete(); // wali kelas
            $table->timestamps();
        });

        // 6. siswa (singular)
        Schema::create('siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('rombel_id')->nullable()->constrained('rombel')->nullOnDelete();
            $table->string('nisn')->unique();
            $table->string('nama');
            $table->date('tanggal_lahir');
            $table->enum('status_lulus', ['lulus', 'tidak_lulus', 'ditunda'])->default('lulus');
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siswa');
        Schema::dropIfExists('rombel');
        Schema::dropIfExists('guru');
        Schema::dropIfExists('mata_pelajaran');
        Schema::dropIfExists('semester');
        Schema::dropIfExists('tahun_ajaran');

        // Recreate the old plural table if we roll back
        Schema::create('siswas', function (Blueprint $table) {
            $table->id();
            $table->string('nisn')->unique();
            $table->string('nama');
            $table->date('tanggal_lahir');
            $table->enum('status_lulus', ['lulus', 'tidak_lulus', 'ditunda'])->default('lulus');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }
};
