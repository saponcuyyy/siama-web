<?php

namespace Database\Seeders;

use App\Models\Album;
use App\Models\Berita;
use App\Models\Fasilitas;
use App\Models\KategoriBerita;
use App\Models\Menu;
use App\Models\Pengumuman;
use App\Models\Setting;
use App\Models\Slider;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CmsSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks for truncation
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Slider::truncate();
        Fasilitas::truncate();
        KategoriBerita::truncate();
        Berita::truncate();
        Pengumuman::truncate();
        Album::truncate();
        Menu::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. Settings
        $settings = [
            'nama_sekolah' => 'SMA Negeri 2 Perbaungan',
            'tagline' => 'Mencetak Generasi Unggul, Berkarakter, dan Berdaya Saing Global',
            'alamat' => 'Jl. Dusun Duku Desa Melati II, Kec. Perbaungan, Serdang Bedagai',
            'telepon' => '0812-3456-7890',
            'email' => 'admin@sman2perbaungan.sch.id',
            'meta_description' => 'Website Resmi SMA Negeri 2 Perbaungan - Pusat Informasi Akademik dan Kegiatan Sekolah.',
            'facebook' => 'https://facebook.com/sman2perbaungan',
            'instagram' => 'https://instagram.com/sman2perbaungan',
            'youtube' => 'https://youtube.com/@sman2perbaungan',
            'map_latitude' => '-6.200000',
            'map_longitude' => '106.816666',
            'map_zoom' => '15',
            'map_label' => 'Lokasi Sekolah',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value, 'group' => 'website']);
        }

        // 2. Sliders
        $sliders = [
            [
                'judul' => 'Selamat Datang di SMAN 2 Perbaungan',
                'subjudul' => 'Lingkungan belajar yang inspiratif untuk mencapai cita-cita Anda.',
                'file_path' => 'https://images.unsplash.com/photo-1523240735140-2236b797996a?q=80&w=2000&auto=format&fit=crop',
                'link_text' => 'Profil Sekolah',
                'link_url' => '/halaman/tentang-sekolah',
            ],
            [
                'judul' => 'Penerimaan Siswa Baru 2026/2027',
                'subjudul' => 'Bergabunglah bersama kami dan jadilah bagian dari keluarga besar SMAN 2.',
                'file_path' => 'https://images.unsplash.com/photo-1509062522246-37fa55423c13?q=80&w=2000&auto=format&fit=crop',
                'link_text' => 'Daftar Sekarang',
                'link_url' => '/kontak',
            ],
        ];
        foreach ($sliders as $s) {
            Slider::create($s);
        }

        // 3. Kategori Berita
        $kats = ['Kegiatan', 'Prestasi', 'Akademik', 'Eskul'];
        foreach ($kats as $k) {
            KategoriBerita::create(['nama' => $k, 'slug' => Str::slug($k)]);
        }

        // 4. Berita
        $katIds = KategoriBerita::pluck('id')->toArray();
        for ($i = 1; $i <= 5; $i++) {
            Berita::create([
                'judul' => 'Berita Sekolah Terkini Ke-'.$i,
                'slug' => Str::slug('Berita Sekolah Terkini Ke-'.$i),
                'kategori_id' => $katIds[array_rand($katIds)],
                'ringkasan' => 'Ini adalah ringkasan berita dummy untuk keperluan tampilan frontend.',
                'konten' => '<p>Ini adalah konten berita yang lebih lengkap. Berisi informasi mendalam tentang kejadian atau prestasi di sekolah.</p>',
                'status' => 'published',
                'published_at' => now(),
                'created_by' => 1,
            ]);
        }

        // 5. Pengumuman
        for ($i = 1; $i <= 3; $i++) {
            Pengumuman::create([
                'judul' => 'Pengumuman Penting Ke-'.$i,
                'konten' => 'Diberitahukan kepada seluruh siswa bahwa kegiatan belajar mengajar akan ditiadakan pada hari libur nasional.',
                'prioritas' => $i == 1 ? 'tinggi' : 'normal',
                'status' => 'aktif',
                'created_by' => 1,
            ]);
        }

        // 6. Fasilitas
        $fasilitas = [
            [
                'nama' => 'Laboratorium Komputer',
                'deskripsi' => 'Dilengkapi dengan 40 unit komputer high-end dan koneksi internet cepat.',
                'foto' => 'https://images.unsplash.com/photo-1562774053-701939374585?q=80&w=800&auto=format&fit=crop',
                'urutan' => 1,
            ],
            [
                'nama' => 'Perpustakaan Digital',
                'deskripsi' => 'Koleksi buku lengkap dengan akses e-book dan ruang baca nyaman.',
                'foto' => 'https://images.unsplash.com/photo-1521587760476-6c120c24443b?q=80&w=800&auto=format&fit=crop',
                'urutan' => 2,
            ],
            [
                'nama' => 'Lapangan Olahraga',
                'deskripsi' => 'Lapangan basket dan futsal standar nasional untuk kegiatan fisik.',
                'foto' => 'https://images.unsplash.com/photo-1541339907198-e08756ebafe3?q=80&w=800&auto=format&fit=crop',
                'urutan' => 3,
            ],
        ];
        foreach ($fasilitas as $f) {
            Fasilitas::create($f);
        }

        // 7. Albums
        $album = Album::create([
            'nama' => 'Kegiatan HUT RI Ke-80',
            'deskripsi' => 'Dokumentasi perayaan HUT RI di sekolah.',
            'cover' => 'https://images.unsplash.com/photo-1523240735140-2236b797996a?q=80&w=800&auto=format&fit=crop',
            'status' => 'aktif',
        ]);

        // 8. Menus
        $menus = [
            ['label' => 'Beranda', 'url' => '/', 'urutan' => 1],
            ['label' => 'Profil', 'url' => '#', 'urutan' => 2],
            ['label' => 'Berita', 'url' => '/berita', 'urutan' => 3],
            ['label' => 'Fasilitas', 'url' => '#facilities', 'urutan' => 4],
            ['label' => 'Kontak', 'url' => '/kontak', 'urutan' => 5],
        ];
        foreach ($menus as $m) {
            $parent = Menu::create($m);
            if ($m['label'] == 'Profil') {
                Menu::create(['label' => 'Visi & Misi', 'url' => '/halaman/visi-misi', 'parent_id' => $parent->id, 'urutan' => 1]);
                Menu::create(['label' => 'Struktur Organisasi', 'url' => '#', 'parent_id' => $parent->id, 'urutan' => 2]);
            }
        }
    }
}
