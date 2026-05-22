<script setup>
import ExamLayout from '@/Layouts/ExamLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const { sesi_aktif, riwayat } = defineProps({
    sesi_aktif: Array,
    riwayat: Array,
});

const formatDate = (date) => {
    const d = new Date(date);
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const riwayatSesiIds = new Set(riwayat.map(r => r.sesi_ujian_id));
const isDone = (sesiId) => riwayatSesiIds.has(sesiId);
</script>

<template>
    <Head title="Daftar Ujian CBT" />

    <ExamLayout>
        <div class="max-w-6xl mx-auto space-y-6 md:space-y-8">
            <!-- Header -->
            <div>
                <h1 class="text-2xl md:text-3xl font-black text-slate-900 tracking-tight">Daftar Ujian (CBT)</h1>
                <p class="text-slate-500 mt-1 text-sm font-medium">Sistem Ujian Berbasis Komputer</p>
            </div>

            <!-- Sesi Aktif -->
            <div>
                <h2 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 bg-rose-500 rounded-full animate-pulse"></span>
                    Ujian Aktif
                </h2>

                <div v-if="sesi_aktif.length === 0" class="bg-white rounded-2xl md:rounded-3xl p-6 md:p-8 border border-slate-200 text-center shadow-sm">
                    <div class="w-14 h-14 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4 text-slate-400">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <h3 class="text-slate-900 font-bold mb-1">Belum ada ujian aktif</h3>
                    <p class="text-slate-500 text-sm">Silakan tunggu jadwal ujian dari guru Anda.</p>
                </div>

                <div v-else class="grid md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
                    <div v-for="sesi in sesi_aktif" :key="sesi.id" class="bg-white rounded-2xl md:rounded-3xl border border-slate-200 shadow-sm overflow-hidden hover:shadow-lg transition-shadow duration-300 relative group">
                        <div class="absolute inset-0 bg-gradient-to-br from-indigo-600 to-violet-600 opacity-0 group-hover:opacity-5 transition-opacity duration-300 pointer-events-none"></div>

                        <div class="p-5 md:p-6">
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider mb-4"
                                :class="sesi.status === 'berlangsung' ? 'bg-indigo-100 text-indigo-600' : 'bg-amber-100 text-amber-600'">
                                {{ sesi.status === 'berlangsung' ? 'Sedang Berlangsung' : 'Menunggu Waktu' }}
                            </div>

                            <h3 class="text-lg md:text-xl font-bold text-slate-900 leading-tight mb-2 line-clamp-2">
                                {{ sesi.nama_sesi }}
                            </h3>
                            <p class="text-sm font-semibold text-indigo-600 mb-4">
                                {{ sesi.paket_ujian.mata_pelajaran?.nama || 'Mata Pelajaran' }}
                            </p>

                            <div class="space-y-3 mb-5 md:mb-6">
                                <div class="flex items-center gap-3 text-sm text-slate-600 font-medium">
                                    <div class="w-8 h-8 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                    <span>{{ formatDate(sesi.waktu_mulai) }}</span>
                                </div>
                                <div class="flex items-center gap-3 text-sm text-slate-600 font-medium">
                                    <div class="w-8 h-8 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <span>{{ sesi.paket_ujian.durasi_menit }} Menit</span>
                                </div>
                            </div>

                            <Link v-if="!isDone(sesi.id)" :href="route('ujian.masuk', sesi.hashid)" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-slate-900 hover:bg-slate-800 text-white text-sm font-bold rounded-xl md:rounded-2xl transition-colors">
                                Masuk Ruang Ujian
                            </Link>
                            <Link v-else :href="route('ujian.hasil', sesi.hashid)" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-sm font-bold rounded-xl md:rounded-2xl transition-colors border-2 border-emerald-200">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12" /></svg>
                                Sudah Dikerjakan
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Riwayat Ujian -->
            <div>
                <h2 class="text-sm font-bold text-slate-900 mb-4">Riwayat Ujian</h2>

                <div v-if="riwayat.length === 0" class="text-center py-6 md:py-8 text-slate-500 text-sm font-medium">
                    Belum ada riwayat ujian.
                </div>

                <div v-else class="bg-white rounded-2xl md:rounded-3xl border border-slate-200 overflow-hidden shadow-sm">
                    <ul class="divide-y divide-slate-100">
                        <li v-for="log in riwayat" :key="log.id" class="p-4 md:p-6 hover:bg-slate-50 transition-colors">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 md:gap-4">
                                <div>
                                    <h4 class="font-bold text-slate-900 text-sm md:text-base">{{ log.sesi_ujian.nama_sesi }}</h4>
                                    <p class="text-xs md:text-sm text-slate-500 font-medium mt-1">
                                        Diselesaikan pada: {{ formatDate(log.selesai_at) }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div v-if="log.status === 'selesai'" class="flex items-center gap-1.5 text-emerald-600 bg-emerald-50 px-2.5 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        Selesai
                                    </div>
                                    <div v-else class="flex items-center gap-1.5 text-rose-600 bg-rose-50 px-2.5 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                        Diskualifikasi
                                    </div>
                                    <Link :href="route('ujian.hasil', log.sesi_ujian.hashid)" class="px-3 md:px-4 py-2 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 text-xs md:text-sm font-bold rounded-xl transition-colors">
                                        Lihat Hasil
                                    </Link>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </ExamLayout>
</template>
