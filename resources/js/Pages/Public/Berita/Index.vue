<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    berita: Object,
    kategori: Array,
    settings: Object,
});
</script>

<template>
    <GuestLayout :settings="settings">
        <Head title="Berita & Artikel" />

        <section class="py-20 bg-slate-50 min-h-screen">
            <div class="max-w-7xl mx-auto px-6">
                <div class="mb-12">
                    <span class="text-blue-600 font-black uppercase tracking-[0.3em] text-xs">Informasi Sekolah</span>
                    <h1 class="text-4xl font-black text-slate-900 mt-2">Berita & Artikel</h1>
                    <p class="text-slate-500 mt-2">Informasi terkini dari SMA Negeri 2 Perbaungan.</p>
                </div>

                <!-- Kategori filter -->
                <div class="flex flex-wrap gap-2 mb-10">
                    <span class="px-4 py-1.5 bg-blue-600 text-white text-xs font-black rounded-full uppercase tracking-widest">Semua</span>
                    <span v-for="kat in kategori" :key="kat.id" class="px-4 py-1.5 bg-white border border-slate-200 text-slate-600 text-xs font-bold rounded-full uppercase tracking-widest hover:bg-blue-50 cursor-pointer">
                        {{ kat.nama }} ({{ kat.berita_count }})
                    </span>
                </div>

                <!-- Grid -->
                <div v-if="berita.data.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <article v-for="item in berita.data" :key="item.id" class="group bg-white rounded-3xl overflow-hidden shadow-sm border border-slate-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-500">
                        <div class="aspect-video bg-slate-100 overflow-hidden relative">
                            <img v-if="item.image_url" :src="item.image_url" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                            <div v-else class="w-full h-full bg-gradient-to-br from-blue-100 to-indigo-100 flex items-center justify-center text-4xl">📰</div>
                            <span class="absolute top-3 left-3 bg-white/90 backdrop-blur-md px-3 py-1 rounded-full text-[10px] font-black text-blue-600 uppercase tracking-widest shadow-sm">
                                {{ item.kategori?.nama || 'Umum' }}
                            </span>
                        </div>
                        <div class="p-6">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">
                                {{ new Date(item.published_at || item.created_at).toLocaleDateString('id-ID', {day:'numeric', month:'long', year:'numeric'}) }}
                            </p>
                            <h2 class="font-black text-slate-900 text-lg leading-tight mb-3 line-clamp-2 group-hover:text-blue-600 transition-colors">
                                <Link :href="route('public.berita.show', item.slug)">{{ item.judul }}</Link>
                            </h2>
                            <p class="text-sm text-slate-500 line-clamp-2 mb-4">{{ item.ringkasan }}</p>
                            <Link :href="route('public.berita.show', item.slug)" class="text-blue-600 font-black text-xs uppercase tracking-widest hover:gap-2 flex items-center gap-1 transition-all">
                                Baca Selengkapnya <span>→</span>
                            </Link>
                        </div>
                    </article>
                </div>

                <!-- Empty -->
                <div v-else class="text-center py-24 bg-white rounded-3xl border border-slate-100">
                    <p class="text-5xl mb-4">📰</p>
                    <p class="text-slate-400 font-bold">Belum ada berita yang dipublikasikan.</p>
                </div>

                <!-- Pagination -->
                <div v-if="berita.last_page > 1" class="flex justify-center gap-2 mt-12">
                    <Link v-if="berita.prev_page_url" :href="berita.prev_page_url" class="px-5 py-2.5 bg-white rounded-xl border border-slate-200 text-sm font-bold hover:bg-blue-50 transition-all">← Sebelumnya</Link>
                    <Link v-if="berita.next_page_url" :href="berita.next_page_url" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 transition-all">Selanjutnya →</Link>
                </div>
            </div>
        </section>
    </GuestLayout>
</template>
