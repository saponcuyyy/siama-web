<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { sanitize } from '@/sanitize';

defineProps({
    pengumuman: Object,
    settings: Object,
});
</script>

<template>
    <GuestLayout :settings="settings">
        <Head title="Pengumuman" />

        <section class="py-20 bg-slate-50 min-h-screen">
            <div class="max-w-4xl mx-auto px-6">
                <div class="mb-12">
                    <span class="text-blue-600 font-black uppercase tracking-[0.3em] text-xs">Informasi Resmi</span>
                    <h1 class="text-4xl font-black text-slate-900 mt-2">Pengumuman</h1>
                    <p class="text-slate-500 mt-2">Pengumuman resmi dari SMA Negeri 2 Perbaungan.</p>
                </div>

                <div v-if="pengumuman.data.length" class="space-y-4">
                    <div v-for="item in pengumuman.data" :key="item.id"
                        class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm hover:shadow-md transition-all">
                        <div class="flex items-start gap-4">
                            <div :class="['w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 text-white font-black text-xs uppercase',
                                item.prioritas === 'tinggi' ? 'bg-red-500' :
                                item.prioritas === 'normal' ? 'bg-blue-600' : 'bg-slate-400'
                            ]">
                                {{ item.prioritas === 'tinggi' ? '❗' : '📢' }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-3 mb-2">
                                    <h2 class="font-black text-slate-900 text-lg">{{ item.judul }}</h2>
                                    <span :class="['text-[10px] font-black px-2 py-0.5 rounded-full uppercase tracking-widest',
                                        item.prioritas === 'tinggi' ? 'bg-red-100 text-red-600' :
                                        item.prioritas === 'normal' ? 'bg-blue-100 text-blue-600' : 'bg-slate-100 text-slate-600'
                                    ]">{{ item.prioritas }}</span>
                                </div>
                                <div class="prose prose-sm max-w-none text-slate-600 mb-3" v-html="sanitize(item.konten)"></div>
                                <div class="flex flex-wrap gap-4 text-xs text-slate-400 font-bold uppercase tracking-widest">
                                    <span>{{ new Date(item.created_at).toLocaleDateString('id-ID', {day:'numeric', month:'long', year:'numeric'}) }}</span>
                                    <span v-if="item.tanggal_selesai">· Berlaku s.d. {{ new Date(item.tanggal_selesai).toLocaleDateString('id-ID', {day:'numeric', month:'long', year:'numeric'}) }}</span>
                                </div>
                                <!-- Halaman Web Pengumuman -->
                                <div v-if="item.lampiran" class="mt-3 pt-3 border-t border-slate-100">
                                    <Link
                                        :href="route('public.pengumuman.baca', item.hashid)"
                                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-50 hover:bg-blue-100 border border-blue-200 text-blue-700 text-xs font-bold transition-all group"
                                    >
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                        </svg>
                                        Baca Pengumuman Selengkapnya
                                        <svg class="w-3.5 h-3.5 shrink-0 opacity-60 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </Link>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-24 bg-white rounded-3xl border border-slate-100">
                    <p class="text-5xl mb-4">📢</p>
                    <p class="text-slate-400 font-bold">Tidak ada pengumuman aktif saat ini.</p>
                </div>

                <!-- Pagination -->
                <div v-if="pengumuman.last_page > 1" class="flex justify-center gap-2 mt-12">
                    <a v-if="pengumuman.prev_page_url" :href="pengumuman.prev_page_url" class="px-5 py-2.5 bg-white rounded-xl border border-slate-200 text-sm font-bold hover:bg-blue-50 transition-all">← Sebelumnya</a>
                    <a v-if="pengumuman.next_page_url" :href="pengumuman.next_page_url" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 transition-all">Selanjutnya →</a>
                </div>
            </div>
        </section>
    </GuestLayout>
</template>
