<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    albums: Object,
    settings: Object,
});
</script>

<template>
    <GuestLayout :settings="settings">
        <Head title="Galeri Foto" />

        <section class="py-20 bg-slate-50 min-h-screen">
            <div class="max-w-7xl mx-auto px-6">
                <div class="mb-12">
                    <span class="text-blue-600 font-black uppercase tracking-[0.3em] text-xs">Dokumentasi</span>
                    <h1 class="text-4xl font-black text-slate-900 mt-2">Galeri Foto</h1>
                    <p class="text-slate-500 mt-2">Koleksi momen berharga kegiatan sekolah kami.</p>
                </div>

                <div v-if="albums.data.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <Link v-for="album in albums.data" :key="album.id"
                        :href="route('public.galeri.show', album.id)"
                        class="group relative aspect-square rounded-3xl overflow-hidden shadow-sm bg-slate-200 block">
                        <img v-if="album.image_url" :src="album.image_url" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                        <div v-else class="w-full h-full bg-gradient-to-br from-blue-100 to-indigo-100 flex items-center justify-center text-5xl">📁</div>
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-transparent to-transparent flex flex-col justify-end p-5">
                            <h2 class="text-white font-black text-lg leading-tight">{{ album.nama }}</h2>
                            <p class="text-slate-300 text-[10px] font-black uppercase tracking-widest mt-1">{{ album.galeri_count }} Foto</p>
                        </div>
                    </Link>
                </div>

                <div v-else class="text-center py-24 bg-white rounded-3xl border border-slate-100">
                    <p class="text-5xl mb-4">📸</p>
                    <p class="text-slate-400 font-bold">Belum ada album galeri yang dipublikasikan.</p>
                </div>

                <!-- Pagination -->
                <div v-if="albums.last_page > 1" class="flex justify-center gap-2 mt-12">
                    <Link v-if="albums.prev_page_url" :href="albums.prev_page_url" class="px-5 py-2.5 bg-white rounded-xl border border-slate-200 text-sm font-bold hover:bg-blue-50 transition-all">← Sebelumnya</Link>
                    <Link v-if="albums.next_page_url" :href="albums.next_page_url" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 transition-all">Selanjutnya →</Link>
                </div>
            </div>
        </section>
    </GuestLayout>
</template>
