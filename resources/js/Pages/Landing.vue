<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import Hero from './Public/Partials/Hero.vue';
import About from './Public/Partials/About.vue';
import NewsEvents from './Public/Partials/NewsEvents.vue';
import Facilities from './Public/Partials/Facilities.vue';
import Contact from './Public/Partials/Contact.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    sliders: Array,
    berita: Array,
    pengumuman: Array,
    fasilitas: Array,
    albums: Array,
    about: Object,
    visiMisi: Object,
    menus: Array,
    settings: Object
});
</script>

<template>
    <GuestLayout :menus="menus" :settings="settings">
        <Head>
            <title>{{ settings?.nama_sekolah || 'SMA Negeri 2 Perbaungan' }}</title>
            <meta name="description" :content="settings?.meta_description || 'Website Resmi SMA Negeri 2 Perbaungan - Membangun Generasi Unggul dan Berkarakter.'">
        </Head>

        <!-- Dynamic Landing Page Content -->
        <Hero :sliders="sliders" />
        
        <About :about="about" :visiMisi="visiMisi" />

        <NewsEvents :berita="berita" :pengumuman="pengumuman" />

        <!-- Fasilitas Section -->
        <Facilities :fasilitas="fasilitas" />

        <!-- Galeri Peek Section -->
        <section class="py-24 bg-white">
            <div class="max-w-7xl mx-auto px-6">
                <div class="flex items-end justify-between mb-16">
                    <div class="space-y-4">
                        <span class="text-blue-600 font-black uppercase tracking-[0.3em] text-xs">Galeri Foto</span>
                        <h2 class="text-4xl md:text-5xl font-black text-slate-900 leading-tight">Momen Berharga Kami</h2>
                    </div>
                    <Link :href="route('public.galeri.index')" class="text-blue-600 font-black uppercase tracking-widest text-xs hover:translate-x-2 transition-transform">
                        Lihat Semua Album <span>→</span>
                    </Link>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div v-for="album in albums" :key="album.id" class="group relative aspect-square rounded-3xl overflow-hidden shadow-sm">
                        <img v-if="album.image_url" :src="album.image_url" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                        <div v-else class="w-full h-full bg-slate-100 flex items-center justify-center text-4xl">📁</div>
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-end p-6">
                            <h4 class="text-white font-black text-sm">{{ album.nama }}</h4>
                            <p class="text-slate-300 text-[10px] font-bold uppercase tracking-widest">{{ album.galeri_count }} Foto</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <Contact :settings="settings" />

    </GuestLayout>
</template>

<style>
html {
    scroll-behavior: smooth;
}
</style>
