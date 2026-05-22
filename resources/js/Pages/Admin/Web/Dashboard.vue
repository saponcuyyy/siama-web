<script setup>
import AdminWebLayout from '@/Layouts/AdminWebLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ stats: Object, berita_terbaru: Array, pengumuman_aktif: Array, pesan_terbaru: Array });
</script>

<template>
    <AdminWebLayout>
        <Head title="Dashboard CMS" />

        <div class="space-y-6">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-black text-slate-900">Dashboard Manajemen Website</h1>
                <p class="text-slate-500 text-sm mt-1">Kelola konten website SMA Negeri 2 Perbaungan</p>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
                    <p class="text-slate-500 text-xs font-semibold uppercase tracking-wider">Total Berita</p>
                    <p class="text-3xl font-black text-blue-600 mt-1">{{ stats.berita }}</p>
                    <Link :href="route('admin.web.berita.index')" class="text-xs text-blue-500 hover:underline mt-1 block">Kelola →</Link>
                </div>
                <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
                    <p class="text-slate-500 text-xs font-semibold uppercase tracking-wider">Pengumuman Aktif</p>
                    <p class="text-3xl font-black text-green-600 mt-1">{{ stats.pengumuman }}</p>
                    <Link :href="route('admin.web.pengumuman.index')" class="text-xs text-green-500 hover:underline mt-1 block">Kelola →</Link>
                </div>
                <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
                    <p class="text-slate-500 text-xs font-semibold uppercase tracking-wider">Pesan Belum Dibaca</p>
                    <p class="text-3xl font-black text-red-600 mt-1">{{ stats.pesan_baru }}</p>
                    <Link :href="route('admin.web.pesan.index')" class="text-xs text-red-500 hover:underline mt-1 block">Lihat →</Link>
                </div>
                <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
                    <p class="text-slate-500 text-xs font-semibold uppercase tracking-wider">Foto Galeri</p>
                    <p class="text-3xl font-black text-purple-600 mt-1">{{ stats.galeri }}</p>
                    <Link :href="route('admin.web.album.index')" class="text-xs text-purple-500 hover:underline mt-1 block">Kelola →</Link>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <Link :href="route('admin.web.berita.create')" class="flex items-center gap-2 bg-blue-600 text-white px-4 py-3 rounded-xl font-semibold text-sm hover:bg-blue-700 transition-all active:scale-95 shadow-sm shadow-blue-200">
                    <span>📰</span> Tulis Berita
                </Link>
                <Link :href="route('admin.web.pengumuman.create')" class="flex items-center gap-2 bg-green-600 text-white px-4 py-3 rounded-xl font-semibold text-sm hover:bg-green-700 transition-all active:scale-95">
                    <span>📢</span> Tambah Pengumuman
                </Link>
                <Link :href="route('admin.web.album.create')" class="flex items-center gap-2 bg-purple-600 text-white px-4 py-3 rounded-xl font-semibold text-sm hover:bg-purple-700 transition-all active:scale-95">
                    <span>🖼️</span> Upload Galeri
                </Link>
                <Link :href="route('admin.web.setting')" class="flex items-center gap-2 bg-slate-700 text-white px-4 py-3 rounded-xl font-semibold text-sm hover:bg-slate-800 transition-all active:scale-95">
                    <span>⚙️</span> Pengaturan
                </Link>
            </div>

            <!-- Tables -->
            <div class="grid lg:grid-cols-2 gap-6">
                <!-- Berita Terbaru -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                        <h2 class="font-bold text-slate-900">Berita Terbaru</h2>
                        <Link :href="route('admin.web.berita.index')" class="text-xs text-blue-600 hover:underline">Lihat semua</Link>
                    </div>
                    <div class="divide-y divide-slate-50">
                        <div v-for="b in berita_terbaru" :key="b.id" class="flex items-center justify-between px-5 py-3 hover:bg-slate-50 transition-colors">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-slate-900 truncate">{{ b.judul }}</p>
                                <p class="text-xs text-slate-400">{{ new Date(b.created_at).toLocaleDateString('id-ID') }}</p>
                            </div>
                            <span :class="['text-xs font-semibold px-2 py-1 rounded-full ml-3 flex-shrink-0', b.status === 'published' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700']">
                                {{ b.status === 'published' ? 'Publik' : 'Draft' }}
                            </span>
                        </div>
                        <div v-if="!berita_terbaru?.length" class="px-5 py-6 text-center text-slate-400 text-sm">Belum ada berita</div>
                    </div>
                </div>

                <!-- Pesan Masuk -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                        <h2 class="font-bold text-slate-900">Pesan Masuk Baru</h2>
                        <Link :href="route('admin.web.pesan.index')" class="text-xs text-blue-600 hover:underline">Lihat semua</Link>
                    </div>
                    <div class="divide-y divide-slate-50">
                        <div v-for="p in pesan_terbaru" :key="p.id" class="flex items-center justify-between px-5 py-3 hover:bg-slate-50 transition-colors">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-slate-900 truncate">{{ p.nama }}</p>
                                <p class="text-xs text-slate-400 truncate">{{ p.subjek }}</p>
                            </div>
                            <Link :href="route('admin.web.pesan.show', p.hashid)" class="text-xs text-blue-600 hover:underline ml-3 flex-shrink-0">Baca</Link>
                        </div>
                        <div v-if="!pesan_terbaru?.length" class="px-5 py-6 text-center text-slate-400 text-sm">Tidak ada pesan baru</div>
                    </div>
                </div>
            </div>
        </div>
    </AdminWebLayout>
</template>
