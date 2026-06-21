<script setup>
import ExamLayout from '@/Layouts/ExamLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';

const { sesi_aktif, riwayat, peserta_saya } = defineProps({
    sesi_aktif: Array,
    riwayat: Object,
    peserta_saya: Object,
});

const formatTime = (dateStr) => {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    const h = String(d.getHours()).padStart(2, '0');
    const m = String(d.getMinutes()).padStart(2, '0');
    return `${h}.${m}`;
};

const formatDate = (date) => {
    const d = new Date(date);
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const formatJadwal = (mulaiStr, selesaiStr) => {
    if (!mulaiStr) return '';
    const mulai = new Date(mulaiStr);
    const optionsDate = { day: 'numeric', month: 'long', year: 'numeric' };
    const dateFormatted = mulai.toLocaleDateString('id-ID', optionsDate);
    const start = formatTime(mulaiStr);
    const end = selesaiStr ? formatTime(selesaiStr) : 'selesai';
    return `${dateFormatted.toLowerCase()} pukul ${start} - ${end}`;
};
</script>

<template>
    <Head title="Ujian Saya" />

    <ExamLayout>
        <div class="max-w-4xl mx-auto space-y-5 sm:space-y-7 pb-8">

            <!-- Active Exams -->
            <section>
                <div class="flex items-center gap-2 mb-4 sm:mb-5">
                    <span class="w-2 h-2 bg-rose-500 rounded-full animate-pulse"></span>
                    <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Ujian Aktif</h2>
                </div>

                <div v-if="sesi_aktif.length === 0"
                    class="bg-white rounded-2xl sm:rounded-3xl px-5 sm:px-8 py-8 sm:py-10 border border-slate-100 text-center shadow-sm">
                    <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <h3 class="text-slate-800 font-bold mb-1">Tidak ada ujian aktif</h3>
                    <p class="text-slate-400 text-sm">Kamu akan melihat ujian yang tersedia di sini.</p>
                </div>

                <div v-else class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                    <div v-for="sesi in sesi_aktif" :key="sesi.id"
                        class="bg-white rounded-2xl sm:rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden">

                        <!-- Top color bar -->
                        <div class="h-1.5" :class="sesi.status === 'berlangsung' ? 'bg-gradient-to-r from-indigo-500 to-violet-500' : 'bg-gradient-to-r from-amber-400 to-orange-400'"></div>

                        <div class="p-4 sm:p-5">
                            <!-- Badge + Meta row -->
                            <div class="flex items-center justify-between mb-3">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider"
                                    :class="sesi.status === 'berlangsung' ? 'bg-indigo-50 text-indigo-600' : 'bg-amber-50 text-amber-600'">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="sesi.status === 'berlangsung' ? 'bg-indigo-500 animate-pulse' : 'bg-amber-500'"></span>
                                    {{ sesi.status === 'berlangsung' ? 'Berlangsung' : 'Akan Datang' }}
                                </span>
                                <span class="text-[10px] font-bold text-slate-400">{{ sesi.paket_ujian.durasi_menit }} menit</span>
                            </div>

                            <!-- Title -->
                            <h3 class="text-[15px] font-bold text-slate-900 leading-snug mb-1 line-clamp-2">{{ sesi.nama_sesi }}</h3>
                            <p class="text-xs font-semibold text-indigo-500 mb-3">{{ sesi.paket_ujian.mata_pelajaran?.nama }}</p>

                            <!-- Schedule row -->
                            <div class="flex items-start gap-2.5 text-xs text-slate-500 mb-4">
                                <svg class="w-4 h-4 text-slate-400 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span class="font-medium">{{ formatJadwal(sesi.waktu_mulai, sesi.waktu_selesai) }}</span>
                            </div>

                            <!-- Action button -->
                            <Link
                                v-if="!peserta_saya[sesi.id] || peserta_saya[sesi.id].status === 'belum_mulai'"
                                :href="route('ujian.masuk', sesi.hashid)"
                                class="w-full flex items-center justify-center gap-2 py-2.5 bg-slate-900 hover:bg-slate-800 active:scale-[0.98] text-white text-xs font-bold rounded-xl transition-all"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                                Masuk Ruang Ujian
                            </Link>
                            <Link
                                v-else-if="peserta_saya[sesi.id].status === 'mengerjakan'"
                                :href="route('ujian.ruang', sesi.hashid)"
                                class="w-full flex items-center justify-center gap-2 py-2.5 bg-amber-500 hover:bg-amber-400 active:scale-[0.98] text-white text-xs font-bold rounded-xl transition-all"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Lanjutkan
                            </Link>
                            <button v-else disabled
                                class="w-full flex items-center justify-center gap-2 py-2.5 bg-slate-50 text-slate-400 text-xs font-bold rounded-xl cursor-not-allowed border border-slate-100"
                            >
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12" /></svg>
                                Selesai
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Exam History -->
            <section>
                <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 sm:mb-5">Riwayat Ujian</h2>

                <div v-if="riwayat.data.length === 0"
                    class="bg-white rounded-2xl sm:rounded-3xl px-5 sm:px-8 py-8 sm:py-10 border border-slate-100 text-center shadow-sm">
                    <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="text-slate-800 font-bold mb-1">Belum ada riwayat</h3>
                    <p class="text-slate-400 text-sm">Ujian yang sudah selesai akan muncul di sini.</p>
                </div>

                <div v-else class="space-y-2 sm:space-y-3">
                    <div v-for="log in riwayat.data" :key="log.id"
                        class="bg-white rounded-xl sm:rounded-2xl border border-slate-100 shadow-sm p-3 sm:p-4 flex items-center gap-3 sm:gap-4 hover:shadow-md transition-all">

                        <!-- Icon -->
                        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl flex items-center justify-center shrink-0"
                            :class="log.status === 'selesai' ? 'bg-emerald-50 text-emerald-500' : 'bg-rose-50 text-rose-500'">
                            <svg v-if="log.status === 'selesai'" class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <svg v-else class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </div>

                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <h4 class="font-bold text-slate-900 text-sm sm:text-[15px] leading-tight truncate">{{ log.sesi_ujian.nama_sesi }}</h4>
                            <p class="text-xs text-slate-400 font-medium mt-0.5 truncate">{{ log.sesi_ujian.paket_ujian.mata_pelajaran?.nama || '-' }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-md"
                                    :class="log.status === 'selesai' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'">
                                    {{ log.status === 'selesai' ? 'Selesai' : 'Diskualifikasi' }}
                                </span>
                                <span class="text-[10px] text-slate-400 font-medium">{{ new Date(log.selesai_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }) }}</span>
                            </div>
                        </div>

                        <!-- Arrow -->
                        <Link :href="route('ujian.hasil', log.sesi_ujian.hashid)"
                            class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-50 hover:bg-indigo-50 text-slate-400 hover:text-indigo-600 flex items-center justify-center shrink-0 transition-colors">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </Link>
                    </div>

                    <!-- Pagination -->
                    <Pagination :data="riwayat" />
                </div>
            </section>
        </div>
    </ExamLayout>
</template>
