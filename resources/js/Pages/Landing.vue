<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const mobileMenuOpen = ref(false);
const activeTab = ref('ipa');

const jurusan = {
  ipa: { name: 'IPA', desc: 'Program studi sains dengan fokus pada Matematika, Fisika, Kimia, dan Biologi. Mempersiapkan siswa untuk jenjang perguruan tinggi di bidang eksakta dan kesehatan.', icon: '🔬' },
  ips: { name: 'IPS', desc: 'Program studi sosial dengan fokus pada Ekonomi, Geografi, Sejarah, dan Sosiologi. Mempersiapkan siswa untuk bidang bisnis, hukum, dan ilmu sosial.', icon: '📊' },
};

const stats = [
  { label: 'Siswa Aktif', value: '1.200+' },
  { label: 'Tenaga Pendidik', value: '80+' },
  { label: 'Tahun Berdiri', value: '1985' },
  { label: 'Akreditasi', value: 'A' },
];

const berita = [
  { tanggal: '1 Mei 2024', judul: 'Siswa SMAN 2 Perbaungan Raih Juara 1 Olimpiade Sains Kabupaten', kategori: 'Prestasi' },
  { tanggal: '25 Apr 2024', judul: 'Penerimaan Peserta Didik Baru (PPDB) Tahun Ajaran 2024/2025', kategori: 'Pengumuman' },
  { tanggal: '20 Apr 2024', judul: 'Peringatan Hari Kartini: Pentas Seni dan Budaya Siswa', kategori: 'Kegiatan' },
];

const fasilitas = [
  { nama: 'Laboratorium IPA', icon: '🔬', desc: 'Lab Fisika, Kimia & Biologi modern' },
  { nama: 'Perpustakaan Digital', icon: '📚', desc: 'Koleksi 10.000+ buku & e-library' },
  { nama: 'Lapangan Olahraga', icon: '⚽', desc: 'Futsal, Basket & Badminton' },
  { nama: 'Ruang Komputer', icon: '💻', desc: '60 unit komputer terkoneksi internet' },
  { nama: 'Aula Serbaguna', icon: '🏛️', desc: 'Kapasitas 500 orang' },
  { nama: 'Masjid Sekolah', icon: '🕌', desc: 'Fasilitas ibadah yang nyaman' },
];
</script>

<template>
  <Head title="SMA Negeri 2 Perbaungan - Unggul, Berkarakter, Berprestasi" />

  <div class="min-h-screen bg-white font-sans">

    <!-- Navbar -->
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white/95 backdrop-blur border-b border-slate-100 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-gradient-to-br from-blue-700 to-blue-500 rounded-xl flex items-center justify-center shadow">
            <span class="text-white font-black text-lg">2</span>
          </div>
          <div>
            <p class="font-black text-slate-900 leading-none text-sm">SMAN 2 PERBAUNGAN</p>
            <p class="text-[10px] text-slate-500">Kab. Serdang Bedagai</p>
          </div>
        </div>
        <div class="hidden md:flex items-center gap-8">
          <a href="#profil" class="text-sm font-medium text-slate-600 hover:text-blue-600 transition-colors">Profil</a>
          <a href="#jurusan" class="text-sm font-medium text-slate-600 hover:text-blue-600 transition-colors">Jurusan</a>
          <a href="#fasilitas" class="text-sm font-medium text-slate-600 hover:text-blue-600 transition-colors">Fasilitas</a>
          <a href="#berita" class="text-sm font-medium text-slate-600 hover:text-blue-600 transition-colors">Berita</a>
          <a href="#kontak" class="text-sm font-medium text-slate-600 hover:text-blue-600 transition-colors">Kontak</a>
          <Link :href="route('login')" class="bg-blue-600 text-white text-sm font-semibold px-5 py-2 rounded-full hover:bg-blue-700 transition-all shadow-md shadow-blue-200 active:scale-95">
            Login Akademik
          </Link>
        </div>
        <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 text-slate-600">
          <svg v-if="!mobileMenuOpen" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
      <!-- Mobile Menu -->
      <div v-if="mobileMenuOpen" class="md:hidden px-4 pb-4 space-y-2 bg-white border-t border-slate-100">
        <a href="#profil" class="block py-2 text-sm text-slate-700">Profil</a>
        <a href="#jurusan" class="block py-2 text-sm text-slate-700">Jurusan</a>
        <a href="#fasilitas" class="block py-2 text-sm text-slate-700">Fasilitas</a>
        <a href="#berita" class="block py-2 text-sm text-slate-700">Berita</a>
        <a href="#kontak" class="block py-2 text-sm text-slate-700">Kontak</a>
        <Link :href="route('login')" class="block text-center bg-blue-600 text-white text-sm font-semibold px-5 py-2 rounded-full mt-2">Login Akademik</Link>
      </div>
    </nav>

    <!-- Hero -->
    <section class="relative min-h-screen flex items-center pt-16 overflow-hidden bg-gradient-to-br from-blue-950 via-blue-800 to-blue-600">
      <div class="absolute inset-0 opacity-10" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
      <div class="relative max-w-7xl mx-auto px-6 lg:px-8 py-20 grid lg:grid-cols-2 gap-16 items-center">
        <div>
          <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur border border-white/20 rounded-full px-4 py-1.5 mb-6">
            <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
            <span class="text-white/90 text-xs font-medium">Terakreditasi A — Tahun 2024</span>
          </div>
          <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-white leading-tight">
            SMA Negeri 2<br/>
            <span class="text-yellow-300">Perbaungan</span>
          </h1>
          <p class="mt-4 text-blue-100 text-lg max-w-lg leading-relaxed">
            Unggul dalam prestasi, berkarakter mulia, dan berwawasan global. Mencetak generasi penerus bangsa yang siap menghadapi tantangan masa depan.
          </p>
          <div class="mt-8 flex flex-wrap gap-4">
            <a href="#profil" class="bg-yellow-400 hover:bg-yellow-300 text-blue-950 font-bold px-8 py-3 rounded-full transition-all shadow-lg shadow-yellow-400/30 active:scale-95">
              Profil Sekolah
            </a>
            <Link :href="route('login')" class="bg-white/10 hover:bg-white/20 backdrop-blur border border-white/30 text-white font-semibold px-8 py-3 rounded-full transition-all">
              Portal Akademik →
            </Link>
          </div>
          <!-- Stats Row -->
          <div class="mt-12 grid grid-cols-4 gap-4">
            <div v-for="stat in stats" :key="stat.label" class="text-center">
              <p class="text-2xl font-black text-yellow-300">{{ stat.value }}</p>
              <p class="text-xs text-blue-200 mt-1">{{ stat.label }}</p>
            </div>
          </div>
        </div>
        <div class="hidden lg:flex justify-center">
          <div class="relative">
            <div class="w-80 h-80 bg-white/10 backdrop-blur rounded-3xl border border-white/20 flex flex-col items-center justify-center p-8 text-center shadow-2xl">
              <div class="w-24 h-24 bg-gradient-to-br from-yellow-300 to-yellow-500 rounded-2xl flex items-center justify-center mb-4 shadow-lg">
                <span class="text-4xl font-black text-blue-900">2</span>
              </div>
              <h2 class="text-white font-black text-xl leading-tight">SMA NEGERI 2<br>PERBAUNGAN</h2>
              <p class="text-blue-200 text-sm mt-2">Jl. Dusun Duku Desa Melati II<br>Kec. Perbaungan, Serdang Bedagai</p>
              <div class="mt-4 bg-green-500/20 border border-green-400/30 rounded-full px-3 py-1">
                <span class="text-green-300 text-xs font-semibold">✓ Akreditasi A</span>
              </div>
            </div>
            <div class="absolute -top-4 -right-4 w-16 h-16 bg-yellow-400 rounded-2xl flex items-center justify-center shadow-lg animate-bounce">
              <span class="text-2xl">🏆</span>
            </div>
          </div>
        </div>
      </div>
      <!-- Wave -->
      <div class="absolute bottom-0 left-0 right-0">
        <svg viewBox="0 0 1440 80" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M0 80L1440 80L1440 40C1200 80 720 0 0 40L0 80Z" fill="white"/>
        </svg>
      </div>
    </section>

    <!-- Profil -->
    <section id="profil" class="py-20 bg-white">
      <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="text-center mb-12">
          <span class="text-blue-600 font-semibold text-sm uppercase tracking-widest">Tentang Kami</span>
          <h2 class="text-3xl lg:text-4xl font-black text-slate-900 mt-2">Profil Sekolah</h2>
        </div>
        <div class="grid lg:grid-cols-2 gap-12 items-start">
          <div class="space-y-6">
            <div class="bg-slate-50 rounded-2xl p-6 border border-slate-100">
              <h3 class="font-bold text-slate-900 mb-4 text-lg">📋 Identitas Sekolah</h3>
              <table class="w-full text-sm">
                <tbody class="space-y-2">
                  <tr class="border-b border-slate-100"><td class="py-2 text-slate-500 w-40">Nama Sekolah</td><td class="py-2 font-semibold text-slate-800">SMA Negeri 2 Perbaungan</td></tr>
                  <tr class="border-b border-slate-100"><td class="py-2 text-slate-500">NPSN</td><td class="py-2 font-semibold text-slate-800">10213456</td></tr>
                  <tr class="border-b border-slate-100"><td class="py-2 text-slate-500">Status</td><td class="py-2"><span class="bg-green-100 text-green-700 text-xs font-bold px-2 py-1 rounded-full">Negeri</span></td></tr>
                  <tr class="border-b border-slate-100"><td class="py-2 text-slate-500">Akreditasi</td><td class="py-2"><span class="bg-blue-100 text-blue-700 text-xs font-bold px-2 py-1 rounded-full">A — Sangat Baik</span></td></tr>
                  <tr class="border-b border-slate-100"><td class="py-2 text-slate-500">Kecamatan</td><td class="py-2 font-semibold text-slate-800">Perbaungan</td></tr>
                  <tr class="border-b border-slate-100"><td class="py-2 text-slate-500">Kabupaten</td><td class="py-2 font-semibold text-slate-800">Serdang Bedagai</td></tr>
                  <tr><td class="py-2 text-slate-500">Provinsi</td><td class="py-2 font-semibold text-slate-800">Sumatera Utara</td></tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="space-y-6">
            <div class="bg-blue-50 rounded-2xl p-6 border border-blue-100">
              <h3 class="font-bold text-blue-900 mb-3 text-lg">🎯 Visi</h3>
              <p class="text-blue-800 leading-relaxed italic">
                "Terwujudnya peserta didik yang beriman, berakhlak mulia, berprestasi, berbudaya, dan berwawasan lingkungan dalam era globalisasi."
              </p>
            </div>
            <div class="bg-slate-50 rounded-2xl p-6 border border-slate-100">
              <h3 class="font-bold text-slate-900 mb-3 text-lg">📌 Misi</h3>
              <ul class="space-y-2 text-sm text-slate-700">
                <li class="flex gap-2"><span class="text-blue-500 font-bold mt-0.5">•</span>Menyelenggarakan pendidikan yang berkualitas dan berkarakter</li>
                <li class="flex gap-2"><span class="text-blue-500 font-bold mt-0.5">•</span>Meningkatkan kompetensi tenaga pendidik dan kependidikan</li>
                <li class="flex gap-2"><span class="text-blue-500 font-bold mt-0.5">•</span>Mengembangkan potensi siswa di bidang akademik dan non-akademik</li>
                <li class="flex gap-2"><span class="text-blue-500 font-bold mt-0.5">•</span>Membangun budaya sekolah yang kondusif dan berwawasan lingkungan</li>
                <li class="flex gap-2"><span class="text-blue-500 font-bold mt-0.5">•</span>Menjalin kerjasama dengan masyarakat dan instansi terkait</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Jurusan -->
    <section id="jurusan" class="py-20 bg-slate-50">
      <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="text-center mb-12">
          <span class="text-blue-600 font-semibold text-sm uppercase tracking-widest">Program Studi</span>
          <h2 class="text-3xl lg:text-4xl font-black text-slate-900 mt-2">Jurusan yang Tersedia</h2>
        </div>
        <div class="flex justify-center gap-4 mb-8">
          <button v-for="(j, key) in jurusan" :key="key" @click="activeTab = key"
            :class="['px-6 py-2.5 rounded-full font-semibold text-sm transition-all', activeTab === key ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : 'bg-white text-slate-600 border border-slate-200 hover:border-blue-300']">
            {{ j.icon }} {{ j.name }}
          </button>
        </div>
        <div class="max-w-2xl mx-auto bg-white rounded-3xl p-8 shadow-lg border border-slate-100 text-center">
          <div class="text-5xl mb-4">{{ jurusan[activeTab].icon }}</div>
          <h3 class="text-2xl font-black text-slate-900 mb-2">Program {{ jurusan[activeTab].name }}</h3>
          <p class="text-slate-600 leading-relaxed">{{ jurusan[activeTab].desc }}</p>
          <div class="mt-6 grid grid-cols-3 gap-3">
            <template v-if="activeTab === 'ipa'">
              <div class="bg-blue-50 rounded-xl p-3 text-xs font-semibold text-blue-700">Matematika</div>
              <div class="bg-blue-50 rounded-xl p-3 text-xs font-semibold text-blue-700">Fisika</div>
              <div class="bg-blue-50 rounded-xl p-3 text-xs font-semibold text-blue-700">Kimia</div>
              <div class="bg-blue-50 rounded-xl p-3 text-xs font-semibold text-blue-700">Biologi</div>
              <div class="bg-blue-50 rounded-xl p-3 text-xs font-semibold text-blue-700">Informatika</div>
              <div class="bg-blue-50 rounded-xl p-3 text-xs font-semibold text-blue-700">B. Inggris</div>
            </template>
            <template v-else>
              <div class="bg-amber-50 rounded-xl p-3 text-xs font-semibold text-amber-700">Ekonomi</div>
              <div class="bg-amber-50 rounded-xl p-3 text-xs font-semibold text-amber-700">Geografi</div>
              <div class="bg-amber-50 rounded-xl p-3 text-xs font-semibold text-amber-700">Sosiologi</div>
              <div class="bg-amber-50 rounded-xl p-3 text-xs font-semibold text-amber-700">Sejarah</div>
              <div class="bg-amber-50 rounded-xl p-3 text-xs font-semibold text-amber-700">Akuntansi</div>
              <div class="bg-amber-50 rounded-xl p-3 text-xs font-semibold text-amber-700">B. Inggris</div>
            </template>
          </div>
        </div>
      </div>
    </section>

    <!-- Fasilitas -->
    <section id="fasilitas" class="py-20 bg-white">
      <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="text-center mb-12">
          <span class="text-blue-600 font-semibold text-sm uppercase tracking-widest">Sarana & Prasarana</span>
          <h2 class="text-3xl lg:text-4xl font-black text-slate-900 mt-2">Fasilitas Sekolah</h2>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="f in fasilitas" :key="f.nama"
            class="group bg-slate-50 hover:bg-blue-600 rounded-2xl p-6 border border-slate-100 hover:border-blue-600 transition-all duration-300 cursor-default">
            <div class="text-4xl mb-4">{{ f.icon }}</div>
            <h3 class="font-bold text-slate-900 group-hover:text-white transition-colors">{{ f.nama }}</h3>
            <p class="text-sm text-slate-500 group-hover:text-blue-100 mt-1 transition-colors">{{ f.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Berita -->
    <section id="berita" class="py-20 bg-slate-50">
      <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="flex items-end justify-between mb-12">
          <div>
            <span class="text-blue-600 font-semibold text-sm uppercase tracking-widest">Informasi Terkini</span>
            <h2 class="text-3xl lg:text-4xl font-black text-slate-900 mt-2">Berita & Pengumuman</h2>
          </div>
          <a href="#" class="text-blue-600 text-sm font-semibold hover:underline hidden sm:block">Lihat semua →</a>
        </div>
        <div class="grid md:grid-cols-3 gap-6">
          <div v-for="b in berita" :key="b.judul" class="bg-white rounded-2xl overflow-hidden border border-slate-100 shadow-sm hover:shadow-md transition-shadow group">
            <div class="h-2 bg-gradient-to-r from-blue-500 to-blue-700"></div>
            <div class="p-6">
              <span :class="['text-xs font-bold px-2 py-1 rounded-full', b.kategori === 'Prestasi' ? 'bg-yellow-100 text-yellow-700' : b.kategori === 'Pengumuman' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700']">
                {{ b.kategori }}
              </span>
              <h3 class="font-bold text-slate-900 mt-3 leading-snug group-hover:text-blue-600 transition-colors">{{ b.judul }}</h3>
              <p class="text-slate-400 text-xs mt-3">{{ b.tanggal }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Kontak -->
    <section id="kontak" class="py-20 bg-blue-950">
      <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="text-center mb-12">
          <span class="text-yellow-400 font-semibold text-sm uppercase tracking-widest">Hubungi Kami</span>
          <h2 class="text-3xl lg:text-4xl font-black text-white mt-2">Informasi Kontak</h2>
        </div>
        <div class="grid md:grid-cols-3 gap-6 mb-10">
          <div class="bg-white/10 backdrop-blur rounded-2xl p-6 border border-white/10 text-center">
            <div class="text-3xl mb-3">📍</div>
            <h3 class="font-bold text-white mb-2">Alamat</h3>
            <p class="text-blue-200 text-sm leading-relaxed">Jl. Dusun Duku Desa Melati II<br>Kec. Perbaungan, Kab. Serdang Bedagai<br>Provinsi Sumatera Utara</p>
          </div>
          <div class="bg-white/10 backdrop-blur rounded-2xl p-6 border border-white/10 text-center">
            <div class="text-3xl mb-3">📞</div>
            <h3 class="font-bold text-white mb-2">Telepon</h3>
            <p class="text-blue-200 text-sm">(0621) 123-456</p>
            <p class="text-blue-200 text-sm mt-1">Senin – Jumat: 07.00 – 15.00</p>
          </div>
          <div class="bg-white/10 backdrop-blur rounded-2xl p-6 border border-white/10 text-center">
            <div class="text-3xl mb-3">✉️</div>
            <h3 class="font-bold text-white mb-2">Email</h3>
            <p class="text-blue-200 text-sm">info@sman2perbaungan.sch.id</p>
            <p class="text-blue-200 text-sm mt-1">Respon dalam 1x24 jam</p>
          </div>
        </div>
        <div class="rounded-3xl overflow-hidden border border-white/10 shadow-2xl">
          <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3981.6!2d99.05!3d3.57!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zM8KwMzQnMTIuMCJOIDk5wrAwMyc0OC4wIkU!5e0!3m2!1sid!2sid!4v1620000000000!5m2!1sid!2sid"
            class="w-full h-64" style="border:0;" allowfullscreen="" loading="lazy">
          </iframe>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-slate-950 py-8">
      <div class="max-w-7xl mx-auto px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-4">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
            <span class="text-white font-black text-sm">2</span>
          </div>
          <span class="text-white font-bold text-sm">SMA Negeri 2 Perbaungan</span>
        </div>
        <p class="text-slate-500 text-xs text-center">
          &copy; {{ new Date().getFullYear() }} SMA Negeri 2 Perbaungan. Powered by SIAMA Academic System.
        </p>
        <Link :href="route('login')" class="text-blue-400 text-xs font-semibold hover:text-blue-300 transition-colors">
          Portal Akademik →
        </Link>
      </div>
    </footer>
  </div>
</template>
