<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    berita: Array,
    pengumuman: Array
});
</script>

<template>
    <section id="news" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-6">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-8 mb-16">
                <div class="max-w-2xl space-y-4">
                    <span class="text-blue-600 font-black uppercase tracking-[0.3em] text-xs">Informasi Terkini</span>
                    <h2 class="text-4xl md:text-5xl font-black text-slate-900 leading-tight">
                        Berita & Pengumuman Sekolah
                    </h2>
                </div>
                <Link :href="route('public.berita.index')" class="bg-slate-100 text-slate-900 px-8 py-3 rounded-2xl font-black text-sm hover:bg-blue-600 hover:text-white transition-all">
                    Lihat Semua Berita
                </Link>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                <!-- Main News (Left) -->
                <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div v-for="item in berita" :key="item.id" class="group flex flex-col">
                        <div class="aspect-video rounded-3xl overflow-hidden relative mb-6">
                            <img :src="item.image_url" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                            <div class="absolute top-4 left-4">
                                <span class="bg-white/90 backdrop-blur-md px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest text-blue-600 shadow-sm">
                                    {{ item.kategori?.nama || 'Umum' }}
                                </span>
                            </div>
                        </div>
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-3">
                            {{ new Date(item.published_at || item.created_at).toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'}) }}
                        </p>
                        <h3 class="text-xl font-black text-slate-900 leading-tight group-hover:text-blue-600 transition-colors mb-3 line-clamp-2">
                            <Link :href="route('public.berita.show', item.slug)">{{ item.judul }}</Link>
                        </h3>
                        <p class="text-slate-500 font-medium text-sm line-clamp-2 mb-6">{{ item.ringkasan }}</p>
                        <Link :href="route('public.berita.show', item.slug)" class="mt-auto inline-flex items-center gap-2 text-blue-600 font-black text-xs uppercase tracking-widest">
                            Baca Selengkapnya <span>→</span>
                        </Link>
                    </div>
                </div>

                <!-- Announcements (Right Sidebar) -->
                <div class="space-y-8">
                    <div class="bg-slate-50 rounded-[2.5rem] p-8 border border-slate-100">
                        <h3 class="font-black text-slate-900 text-lg mb-8 flex items-center gap-3">
                            <span class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white text-base shadow-lg shadow-blue-200">📢</span>
                            Pengumuman
                        </h3>
                        <div class="space-y-8">
                            <div v-for="p in pengumuman" :key="p.id" class="group relative pl-6 border-l-2 border-slate-200 hover:border-blue-500 transition-colors">
                                <div class="absolute -left-[5px] top-0 w-2 h-2 rounded-full bg-slate-300 group-hover:bg-blue-600 transition-colors"></div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">
                                    {{ new Date(p.created_at).toLocaleDateString('id-ID', {day: 'numeric', month: 'short'}) }}
                                </p>
                                <h4 class="font-bold text-slate-900 text-sm leading-tight hover:text-blue-600 transition-colors cursor-pointer">
                                    {{ p.judul }}
                                </h4>
                            </div>
                        </div>
                        <Link :href="route('public.pengumuman.index')" class="w-full bg-slate-200 text-slate-900 py-3 rounded-2xl font-black text-xs uppercase tracking-widest mt-8 block text-center hover:bg-blue-600 hover:text-white transition-all">
                            Semua Pengumuman
                        </Link>
                    </div>

                    <!-- Newsletter/CTA -->
                    <div class="bg-blue-600 rounded-[2.5rem] p-8 text-white relative overflow-hidden shadow-xl shadow-blue-200">
                        <div class="relative z-10">
                            <h3 class="font-black text-xl mb-3">Ingin Update Terkini?</h3>
                            <p class="text-blue-100 text-xs font-medium leading-relaxed mb-6">Dapatkan berita terbaru dari SMA Negeri 2 Perbaungan langsung ke inbox Anda.</p>
                            <div class="space-y-2">
                                <input type="email" placeholder="Email Anda" class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-sm focus:outline-none placeholder:text-white/50">
                                <button class="w-full bg-white text-blue-600 py-3 rounded-xl font-black text-xs uppercase tracking-widest shadow-lg">Berlangganan</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
