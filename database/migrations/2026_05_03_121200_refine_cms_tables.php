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
        // 1. Kategori Berita
        Schema::create('kategori_berita', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        // 2. Albums
        Schema::create('albums', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('slug')->unique();
            $table->text('deskripsi')->nullable();
            $table->string('cover')->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Fasilitas
        Schema::create('fasilitas', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('slug')->unique();
            $table->text('deskripsi')->nullable();
            $table->string('foto')->nullable();
            $table->integer('urutan')->default(0);
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();
            $table->softDeletes();
        });

        // 4. Menus
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('url');
            $table->string('target')->default('_self');
            $table->foreignId('parent_id')->nullable()->constrained('menus')->onDelete('cascade');
            $table->integer('urutan')->default(0);
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();
        });

        // 5. Update existing tables
        Schema::table('berita', function (Blueprint $table) {
            $table->dropColumn('kategori');
            $table->foreignId('kategori_id')->nullable()->after('slug')->constrained('kategori_berita')->nullOnDelete();
            $table->json('tags')->nullable()->after('konten');
        });

        Schema::table('pengumuman', function (Blueprint $table) {
            $table->string('lampiran')->nullable()->after('konten');
        });

        Schema::table('galeri', function (Blueprint $table) {
            $table->foreignId('album_id')->nullable()->after('id')->constrained('albums')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('galeri', function (Blueprint $table) {
            $table->dropForeign(['album_id']);
            $table->dropColumn('album_id');
        });

        Schema::table('pengumuman', function (Blueprint $table) {
            $table->dropColumn('lampiran');
        });

        Schema::table('berita', function (Blueprint $table) {
            $table->dropForeign(['kategori_id']);
            $table->dropColumn(['kategori_id', 'tags']);
            $table->string('kategori')->default('Umum')->after('slug');
        });

        Schema::dropIfExists('menus');
        Schema::dropIfExists('fasilitas');
        Schema::dropIfExists('albums');
        Schema::dropIfExists('kategori_berita');
    }
};
