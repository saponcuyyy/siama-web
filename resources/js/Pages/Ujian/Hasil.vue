<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ExamLayout from '@/Layouts/ExamLayout.vue';

const { sesi, peserta } = defineProps({
    sesi: Object,
    peserta: Object,
});

const isDiskualifikasi = peserta.status === 'didiskualifikasi';

const formatDate = (date) => {
    const d = new Date(date);
    return d.toLocaleDateString('id-ID', {
        day: 'numeric', month: 'long', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
};

const nilaiBreakdown = [
    { label: 'Pilihan Ganda', nilai: peserta.nilai_pg },
    { label: 'Benar / Salah', nilai: peserta.nilai_bs },
    { label: 'Menjodohkan', nilai: peserta.nilai_menjodohkan },
];
</script>

<template>
    <Head title="Hasil Ujian CBT" />

    <ExamLayout>
        <div class="max-w-4xl mx-auto space-y-6 md:space-y-8">

            <!-- Disqualified Alert -->
            <div v-if="isDiskualifikasi"
                class="p-5 md:p-6 bg-rose-50 border-2 border-rose-200 rounded-2xl md:rounded-3xl flex flex-col items-center text-center">
                <div class="w-14 h-14 md:w-16 md:h-16 bg-rose-100 rounded-full flex items-center justify-center mb-4 text-rose-600">
                    <svg class="w-7 h-7 md:w-8 md:h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10" /><line x1="15" y1="9" x2="9" y2="15" /><line x1="9" y1="9" x2="15" y2="15" />
                    </svg>
                </div>
                <h2 class="text-xl md:text-2xl font-black text-rose-700 mb-2">Ujian Didiskualifikasi</h2>
                <p class="text-rose-600 text-sm md:text-base font-medium">
                    Anda telah melakukan pelanggaran berat berulang kali (pindah tab/keluar fullscreen). Silakan hubungi Proktor atau Guru Mata Pelajaran.
                </p>
            </div>

            <!-- Success Alert -->
            <div v-else
                class="p-5 md:p-6 bg-emerald-50 border-2 border-emerald-100 rounded-2xl md:rounded-3xl flex flex-col items-center text-center">
                <div class="w-14 h-14 md:w-16 md:h-16 bg-emerald-100 rounded-full flex items-center justify-center mb-4 text-emerald-600">
                    <svg class="w-7 h-7 md:w-8 md:h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                </div>
                <h2 class="text-xl md:text-2xl font-black text-emerald-700 mb-2">Ujian Selesai!</h2>
                <p class="text-emerald-600 text-sm md:text-base font-medium">
                    Terima kasih telah menyelesaikan ujian ini dengan jujur.
                </p>
            </div>

            <!-- Detail Ujian -->
            <div class="bg-white rounded-2xl md:rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-5 md:p-8 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-0">
                    <div>
                        <h3 class="text-[10px] md:text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Mata Pelajaran</h3>
                        <p class="text-base md:text-xl font-black text-slate-900">{{ sesi.paket_ujian.mata_pelajaran?.nama || 'Mata Pelajaran' }}</p>
                    </div>
                    <div class="text-left sm:text-right">
                        <h3 class="text-[10px] md:text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Waktu Selesai</h3>
                        <p class="text-sm md:text-base text-slate-900 font-bold">{{ formatDate(peserta.selesai_at) }}</p>
                    </div>
                </div>

                <!-- Result Cards -->
                <div class="p-5 md:p-8 bg-slate-50 grid sm:grid-cols-2 gap-4 md:gap-6">
                    <!-- Nilai Akhir -->
                    <div class="bg-white p-5 md:p-6 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group">
                        <div class="absolute top-0 right-0 p-4 opacity-[0.04] pointer-events-none">
                            <svg class="w-20 h-20 md:w-24 md:h-24 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                            </svg>
                        </div>
                        <h4 class="text-[10px] md:text-sm font-bold text-slate-500 uppercase tracking-widest mb-2 relative z-10">Nilai Akhir</h4>

                        <div v-if="peserta.essay_sudah_dinilai === false" class="relative z-10 mt-4">
                            <div class="inline-flex items-center gap-2 text-amber-600 bg-amber-50 px-3 py-2 rounded-xl font-bold text-xs md:text-sm">
                                <svg class="w-4 h-4 md:w-5 md:h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                                    <line x1="12" y1="9" x2="12" y2="13" /><line x1="12" y1="17" x2="12.01" y2="17" />
                                </svg>
                                Menunggu Penilaian Essay
                            </div>
                        </div>
                        <div v-else-if="!isDiskualifikasi" class="relative z-10">
                            <span class="text-3xl md:text-5xl font-black text-slate-900 tabular-nums">{{ peserta.nilai_akhir }}</span>
                            <span class="text-slate-400 font-bold ml-1 text-sm md:text-base">/ 100</span>
                        </div>
                        <div v-else class="relative z-10 mt-2">
                            <span class="text-xl md:text-2xl font-black text-rose-500">0.00</span>
                        </div>
                    </div>

                    <!-- Statistik Jawaban -->
                    <div class="bg-white p-5 md:p-6 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group">
                        <div class="absolute top-0 right-0 p-4 opacity-[0.04] pointer-events-none">
                            <svg class="w-20 h-20 md:w-24 md:h-24 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" /><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />
                            </svg>
                        </div>
                        <h4 class="text-[10px] md:text-sm font-bold text-slate-500 uppercase tracking-widest mb-4 relative z-10">Rincian Nilai Obyektif</h4>

                        <div class="space-y-3 relative z-10">
                            <div v-for="item in nilaiBreakdown" :key="item.label"
                                class="flex justify-between items-center text-xs md:text-sm font-bold">
                                <span class="text-slate-600">{{ item.label }}</span>
                                <span class="text-slate-900">{{ item.nilai }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Back Button -->
            <div class="text-center">
                <Link :href="route('ujian.index')"
                    class="inline-flex items-center gap-2 px-5 md:px-6 py-3 bg-slate-900 hover:bg-slate-800 text-white text-sm md:text-base font-bold rounded-xl transition-colors">
                    <svg class="w-4 h-4 md:w-5 md:h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="7" /><rect x="14" y="3" width="7" height="7" />
                        <rect x="14" y="14" width="7" height="7" /><rect x="3" y="14" width="7" height="7" />
                    </svg>
                    Kembali ke Dashboard
                </Link>
            </div>
        </div>
    </ExamLayout>
</template>
