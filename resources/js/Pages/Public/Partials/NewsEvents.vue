<script setup>
import { Link } from '@inertiajs/vue3';
import { Megaphone, Calendar, FileText, ArrowRight, Mail } from 'lucide-vue-next';

defineProps({
    berita: Array,
    pengumuman: Array
});
</script>

<template>
    <section id="news" class="py-24 bg-slate-50/50">
        <div class="max-w-7xl mx-auto px-6">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-8 mb-16">
                <div class="max-w-2xl space-y-4">
                    <span class="text-blue-600 font-black uppercase tracking-[0.3em] text-xs">Informasi Terkini</span>
                    <h2 class="text-4xl md:text-5xl font-black text-slate-900 leading-tight">
                        Berita & Pengumuman Sekolah
                    </h2>
                </div>
                <Link :href="route('public.berita.index')" class="bg-white text-slate-900 px-8 py-3.5 rounded-2xl font-black text-sm border border-slate-200 hover:border-blue-600 hover:bg-blue-600 hover:text-white shadow-sm hover:shadow transition-all duration-300">
                    Lihat Semua Berita
                </Link>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                <!-- Main News (Left) -->
                <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div v-for="item in berita" :key="item.id" class="group flex flex-col bg-white p-6 rounded-[2rem] border border-slate-200/60 shadow-sm hover:shadow-md transition-all duration-300">
                        <div class="aspect-video rounded-2xl overflow-hidden relative mb-6">
                            <img :src="item.image_url" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                            <div class="absolute top-4 left-4">
                                <span class="bg-white/95 backdrop-blur-md px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest text-blue-600 shadow-sm">
                                    {{ item.kategori?.nama || 'Umum' }}
                                </span>
                            </div>
                        </div>
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-3">
                            {{ new Date(item.published_at || item.created_at).toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'}) }}
                        </p>
                        <h3 class="text-xl font-bold text-slate-900 leading-snug group-hover:text-blue-600 transition-colors mb-3 line-clamp-2">
                            <Link :href="route('public.berita.show', item.slug)">{{ item.judul }}</Link>
                        </h3>
                        <p class="text-slate-500 font-medium text-sm line-clamp-2 mb-6">{{ item.ringkasan }}</p>
                        <Link :href="route('public.berita.show', item.slug)" class="mt-auto inline-flex items-center gap-2 text-blue-600 font-black text-xs uppercase tracking-widest group-hover:gap-3 transition-all">
                            Baca Selengkapnya <span>→</span>
                        </Link>
                    </div>
                </div>

                <!-- Announcements (Right Sidebar) -->
                <div class="space-y-8">
                    <div class="bg-white rounded-[2.5rem] p-8 border border-slate-200/80 shadow-sm flex flex-col">
                        <div class="flex items-center justify-between mb-8">
                            <h3 class="font-black text-slate-955 text-xl flex items-center gap-3">
                                <span class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white text-base shadow-lg shadow-blue-200">
                                    <Megaphone class="w-5 h-5 text-white" />
                                </span>
                                Pengumuman
                            </h3>
                        </div>

                        <!-- Announcement List -->
                        <div class="space-y-4">
                            <Link
                                v-for="p in pengumuman"
                                :key="p.id"
                                :href="route('public.pengumuman.baca', p.hashid)"
                                class="group flex flex-col p-5 bg-slate-50 hover:bg-blue-50/30 rounded-2xl border border-slate-100 hover:border-blue-100 transition-all duration-300"
                            >
                                <!-- Top Row: Date & Priority -->
                                <div class="flex items-center justify-between gap-4 mb-2.5">
                                    <span class="inline-flex items-center gap-1.5 text-[10px] text-slate-400 font-bold uppercase tracking-wider">
                                        <Calendar class="w-3.5 h-3.5 text-slate-400" />
                                        {{ new Date(p.created_at).toLocaleDateString('id-ID', {day: 'numeric', month: 'short', year: 'numeric'}) }}
                                    </span>
                                    <span :class="[
                                        'inline-flex items-center text-[9px] font-black px-2.5 py-0.5 rounded-full uppercase tracking-wider border',
                                        p.prioritas === 'tinggi' ? 'bg-red-50 text-red-600 border-red-100' :
                                        p.prioritas === 'normal' ? 'bg-blue-50 text-blue-600 border-blue-100' :
                                        'bg-slate-100 text-slate-600 border-slate-200'
                                    ]">
                                        {{ p.prioritas }}
                                    </span>
                                </div>

                                <!-- Title -->
                                <h4 class="font-bold text-slate-900 text-sm leading-snug group-hover:text-blue-600 transition-colors line-clamp-2">
                                    {{ p.judul }}
                                </h4>

                                <!-- Footer details -->
                                <div class="flex items-center justify-between mt-3 pt-3 border-t border-slate-200/50">
                                    <span v-if="p.lampiran" class="inline-flex items-center gap-1.5 text-xs text-slate-400 font-semibold">
                                        <FileText class="w-3.5 h-3.5 text-slate-400" />
                                        Lampiran PDF
                                    </span>
                                    <span v-else class="text-xs text-slate-300 font-semibold">Pengumuman</span>

                                    <span class="inline-flex items-center gap-1 text-[11px] font-black text-blue-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                        Baca <ArrowRight class="w-3 h-3" />
                                    </span>
                                </div>
                            </Link>

                            <!-- Empty State -->
                            <div v-if="!pengumuman || pengumuman.length === 0" class="text-center py-8 text-slate-400 text-sm">
                                Belum ada pengumuman terbaru.
                            </div>
                        </div>

                        <Link :href="route('public.pengumuman.index')" class="w-full bg-slate-100 text-slate-700 py-3.5 rounded-2xl font-black text-xs uppercase tracking-widest mt-6 block text-center hover:bg-blue-600 hover:text-white transition-all shadow-sm">
                            Semua Pengumuman
                        </Link>
                    </div>

                    <!-- Newsletter/CTA -->
                    <div class="bg-gradient-to-tr from-blue-700 to-blue-600 rounded-[2.5rem] p-8 text-white relative overflow-hidden shadow-xl shadow-blue-200">
                        <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-white/5 rounded-full blur-2xl"></div>
                        <div class="absolute -left-10 -top-10 w-40 h-40 bg-blue-500/20 rounded-full blur-2xl"></div>
                        
                        <div class="relative z-10">
                            <h3 class="font-black text-xl mb-3 flex items-center gap-2">
                                <Mail class="w-5 h-5" />
                                Ingin Update Terkini?
                            </h3>
                            <p class="text-blue-100 text-xs font-medium leading-relaxed mb-6">Dapatkan berita terbaru dari SMA Negeri 2 Perbaungan langsung ke email Anda.</p>
                            <div class="space-y-3">
                                <input type="email" placeholder="Email Anda" class="w-full bg-white/10 border border-white/15 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-white/20 placeholder:text-white/40 text-white">
                                <button class="w-full bg-white text-blue-600 hover:bg-blue-50 py-3 rounded-xl font-black text-xs uppercase tracking-widest shadow-lg transition-colors">Berlangganan</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

