<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { sanitize } from '@/sanitize';

defineProps({
    berita: Object,
    related: Array,
    settings: Object,
});
</script>

<template>
    <GuestLayout :settings="settings">
        <Head :title="berita.judul" />

        <article class="min-h-screen bg-white">
            <!-- Hero -->
            <div class="relative h-[50vh] min-h-[350px] bg-slate-900 overflow-hidden">
                <img v-if="berita.image_url" :src="berita.image_url" class="absolute inset-0 w-full h-full object-cover opacity-50">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900 to-transparent"></div>
                <div class="relative z-10 h-full flex flex-col justify-end max-w-4xl mx-auto px-6 pb-12">
                    <div class="flex items-center gap-3 mb-4">
                        <Link :href="route('public.berita.index')" class="text-white/60 hover:text-white text-sm font-bold transition-colors">← Kembali ke Berita</Link>
                        <span class="text-white/30">•</span>
                        <span class="bg-blue-600 text-white px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">{{ berita.kategori?.nama }}</span>
                    </div>
                    <h1 class="text-3xl md:text-4xl font-black text-white leading-tight">{{ berita.judul }}</h1>
                    <div class="flex items-center gap-4 mt-4 text-white/60 text-xs font-bold uppercase tracking-widest">
                        <span>{{ berita.author?.name ?? 'Admin' }}</span>
                        <span>•</span>
                        <span>{{ new Date(berita.published_at || berita.created_at).toLocaleDateString('id-ID', {day:'numeric', month:'long', year:'numeric'}) }}</span>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="max-w-4xl mx-auto px-6 py-12">
                <div class="prose prose-lg prose-slate max-w-none" v-html="sanitize(berita.konten)"></div>

                <!-- Related -->
                <div v-if="related.length" class="mt-16 pt-12 border-t border-slate-100">
                    <h2 class="font-black text-slate-900 text-xl mb-8">Berita Terkait</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <Link v-for="item in related" :key="item.id" :href="route('public.berita.show', item.slug)"
                            class="group bg-slate-50 rounded-2xl overflow-hidden hover:shadow-md transition-all">
                            <div class="aspect-video bg-slate-200 overflow-hidden">
                                <img v-if="item.image_url" :src="item.image_url" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            </div>
                            <div class="p-4">
                                <p class="font-black text-sm text-slate-900 group-hover:text-blue-600 line-clamp-2 transition-colors">{{ item.judul }}</p>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </article>
    </GuestLayout>
</template>
