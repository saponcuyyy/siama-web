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
        // 1. bank_soal
        Schema::create('bank_soal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mata_pelajaran_id')
                ->constrained('mata_pelajaran')->restrictOnDelete();
            $table->foreignId('guru_id')
                ->constrained('guru')->restrictOnDelete();
            $table->foreignId('tahun_ajaran_id')
                ->constrained('tahun_ajaran')->restrictOnDelete();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->enum('tingkat', ['X', 'XI', 'XII']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. soal
        Schema::create('soal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_soal_id')
                ->constrained('bank_soal')->cascadeOnDelete();
            $table->enum('tipe', ['pg', 'essay', 'benar_salah', 'menjodohkan']);
            $table->longText('pertanyaan');
            $table->unsignedTinyInteger('bobot')->default(1);
            $table->enum('tingkat_kesulitan', ['mudah', 'sedang', 'sulit'])->default('sedang');
            $table->string('bab')->nullable();
            $table->string('indikator')->nullable();
            $table->text('kunci_jawaban')->nullable();
            $table->text('pembahasan')->nullable();
            $table->string('gambar_path')->nullable();
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['bank_soal_id', 'tipe']);
        });

        // 3. pilihan_jawaban
        Schema::create('pilihan_jawaban', function (Blueprint $table) {
            $table->id();
            $table->foreignId('soal_id')
                ->constrained('soal')->cascadeOnDelete();
            $table->string('kode', 1);
            $table->text('teks');
            $table->string('gambar_path')->nullable();
            $table->boolean('is_benar')->default(false);
            $table->unsignedTinyInteger('urutan')->default(0);
            $table->timestamps();
            $table->unique(['soal_id', 'kode']);
        });

        // 4. pasangan_menjodohkan
        Schema::create('pasangan_menjodohkan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('soal_id')
                ->constrained('soal')->cascadeOnDelete();
            $table->string('kiri');
            $table->string('kanan');
            $table->unsignedTinyInteger('urutan')->default(0);
            $table->timestamps();
        });

        // 5. paket_ujian
        Schema::create('paket_ujian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mata_pelajaran_id')
                ->constrained('mata_pelajaran')->restrictOnDelete();
            $table->foreignId('guru_id')
                ->constrained('guru')->restrictOnDelete();
            $table->foreignId('tahun_ajaran_id')
                ->constrained('tahun_ajaran')->restrictOnDelete();
            $table->foreignId('semester_id')
                ->constrained('semester')->restrictOnDelete();
            $table->string('nama');
            $table->string('kode', 20)->unique();
            $table->enum('jenis', ['uh', 'uts', 'uas', 'pas', 'try_out', 'lainnya']);
            $table->enum('tingkat', ['X', 'XI', 'XII']);
            $table->unsignedSmallInteger('durasi_menit');
            $table->unsignedSmallInteger('jumlah_soal_pg')->default(0);
            $table->unsignedSmallInteger('jumlah_soal_bs')->default(0);
            $table->unsignedSmallInteger('jumlah_soal_menjodohkan')->default(0);
            $table->unsignedSmallInteger('jumlah_soal_essay')->default(0);
            $table->unsignedSmallInteger('nilai_maksimal')->default(100);
            $table->boolean('acak_soal')->default(true);
            $table->boolean('acak_jawaban')->default(true);
            $table->text('petunjuk')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });

        // 6. paket_soal (pivot)
        Schema::create('paket_soal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paket_ujian_id')
                ->constrained('paket_ujian')->cascadeOnDelete();
            $table->foreignId('soal_id')
                ->constrained('soal')->cascadeOnDelete();
            $table->unsignedTinyInteger('urutan')->default(0);
            $table->unsignedTinyInteger('bobot_override')->nullable();
            $table->timestamps();
            $table->unique(['paket_ujian_id', 'soal_id']);
        });

        // 7. sesi_ujian
        Schema::create('sesi_ujian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paket_ujian_id')
                ->constrained('paket_ujian')->restrictOnDelete();
            $table->foreignId('rombel_id')
                ->nullable()->constrained('rombel')->nullOnDelete();
            $table->string('nama_sesi');
            $table->string('token', 8)->unique();
            $table->datetime('waktu_mulai');
            $table->datetime('waktu_selesai');
            $table->unsignedTinyInteger('toleransi_menit')->default(15);
            $table->enum('status', ['menunggu', 'berlangsung', 'selesai', 'dibatalkan'])->default('menunggu');
            $table->unsignedTinyInteger('max_pelanggaran')->default(3);
            $table->boolean('wajib_fullscreen')->default(true);
            $table->text('catatan')->nullable();
            $table->foreignId('dibuat_oleh')
                ->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'waktu_mulai', 'waktu_selesai']);
        });

        // 8. peserta_ujian
        Schema::create('peserta_ujian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesi_ujian_id')
                ->constrained('sesi_ujian')->cascadeOnDelete();
            $table->foreignId('siswa_id')
                ->constrained('siswa')->cascadeOnDelete();
            $table->enum('status', [
                'belum_mulai',
                'mengerjakan',
                'selesai',
                'tidak_hadir',
                'didiskualifikasi',
            ])->default('belum_mulai');
            $table->datetime('mulai_at')->nullable();
            $table->datetime('selesai_at')->nullable();
            $table->unsignedInteger('sisa_detik')->nullable();
            $table->string('device_token', 64)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('browser')->nullable();
            $table->unsignedTinyInteger('jumlah_pelanggaran')->default(0);
            $table->json('urutan_soal')->nullable();
            $table->json('urutan_jawaban')->nullable();
            $table->decimal('nilai_pg', 5, 2)->nullable();
            $table->decimal('nilai_bs', 5, 2)->nullable();
            $table->decimal('nilai_menjodohkan', 5, 2)->nullable();
            $table->decimal('nilai_essay', 5, 2)->nullable();
            $table->decimal('nilai_akhir', 5, 2)->nullable();
            $table->boolean('sudah_dikoreksi')->default(false);
            $table->boolean('essay_sudah_dinilai')->default(false);
            $table->timestamps();
            $table->unique(['sesi_ujian_id', 'siswa_id']);
            $table->index(['sesi_ujian_id', 'status']);
        });

        // 9. jawaban_siswa
        Schema::create('jawaban_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peserta_ujian_id')
                ->constrained('peserta_ujian')->cascadeOnDelete();
            $table->foreignId('soal_id')
                ->constrained('soal')->cascadeOnDelete();
            $table->text('jawaban')->nullable();
            $table->json('jawaban_menjodohkan')->nullable();
            $table->boolean('is_benar')->nullable();
            $table->decimal('nilai', 4, 2)->nullable();
            $table->text('catatan_guru')->nullable();
            $table->foreignId('dinilai_oleh')
                ->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('dinilai_at')->nullable();
            $table->timestamp('dijawab_at')->nullable();
            $table->unsignedInteger('durasi_detik')->nullable();
            $table->timestamps();
            $table->unique(['peserta_ujian_id', 'soal_id']);
            $table->index(['peserta_ujian_id']);
        });

        // 10. log_ujian
        Schema::create('log_ujian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peserta_ujian_id')
                ->constrained('peserta_ujian')->cascadeOnDelete();
            $table->enum('tipe_event', [
                'mulai_ujian',
                'submit_ujian',
                'pindah_tab',
                'keluar_fullscreen',
                'blur_window',
                'kembali_ke_ujian',
                'koneksi_putus',
                'koneksi_kembali',
                'auto_save',
                'diskualifikasi',
                'waktu_habis',
                'ganti_soal',
            ]);
            $table->text('keterangan')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('terjadi_at');
            $table->index(['peserta_ujian_id', 'tipe_event']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_ujian');
        Schema::dropIfExists('jawaban_siswa');
        Schema::dropIfExists('peserta_ujian');
        Schema::dropIfExists('sesi_ujian');
        Schema::dropIfExists('paket_soal');
        Schema::dropIfExists('paket_ujian');
        Schema::dropIfExists('pasangan_menjodohkan');
        Schema::dropIfExists('pilihan_jawaban');
        Schema::dropIfExists('soal');
        Schema::dropIfExists('bank_soal');
    }
};
