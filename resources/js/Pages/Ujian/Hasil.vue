<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { CheckCircle, XCircle, AlertCircle, Clock, BookOpen, Target, LayoutDashboard, Calendar } from 'lucide-vue-next';
import dayjs from 'dayjs';

const props = defineProps({
    sesi: Object,
    peserta: Object,
});

const isDiskualifikasi = props.peserta.status === 'didiskualifikasi';
const formatDate = (date) => dayjs(date).format('DD MMMM YYYY, HH:mm');
</script>

<template>
    <Head title="Hasil Ujian CBT" />
    
    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto py-8">
            <!-- Disqualified Alert -->
            <div v-if="isDiskualifikasi" class="mb-8 p-6 bg-rose-50 border-2 border-rose-200 rounded-3xl flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-rose-100 rounded-full flex items-center justify-center mb-4 text-rose-600">
                    <XCircle class="w-8 h-8" />
                </div>
                <h2 class="text-2xl font-black text-rose-700 mb-2">Ujian Didiskualifikasi</h2>
                <p class="text-rose-600 font-medium">Anda telah melakukan pelanggaran berat berulang kali (pindah tab/keluar fullscreen). Silakan hubungi Proktor atau Guru Mata Pelajaran.</p>
            </div>

            <!-- Success Alert -->
            <div v-else class="mb-8 p-6 bg-emerald-50 border-2 border-emerald-100 rounded-3xl flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mb-4 text-emerald-600">
                    <CheckCircle class="w-8 h-8" />
                </div>
                <h2 class="text-2xl font-black text-emerald-700 mb-2">Ujian Selesai!</h2>
                <p class="text-emerald-600 font-medium">Terima kasih telah menyelesaikan ujian ini dengan jujur.</p>
            </div>

            <!-- Detail Ujian -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden mb-8">
                <div class="p-8 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Mata Pelajaran</h3>
                        <p class="text-xl font-black text-slate-900">{{ sesi.paket_ujian.mata_pelajaran?.nama }}</p>
                    </div>
                    <div class="text-right">
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Waktu Selesai</h3>
                        <p class="text-slate-900 font-bold">{{ formatDate(peserta.selesai_at) }}</p>
                    </div>
                </div>

                <!-- Result Cards -->
                <div class="p-8 bg-slate-50 grid sm:grid-cols-2 gap-6">
                    <!-- Nilai Akhir -->
                    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group">
                        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                            <Target class="w-24 h-24 text-indigo-600" />
                        </div>
                        <h4 class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-2 relative z-10">Nilai Akhir</h4>
                        
                        <div v-if="peserta.essay_sudah_dinilai === false" class="relative z-10 mt-4">
                            <div class="flex items-center gap-2 text-amber-600 bg-amber-50 px-3 py-2 rounded-xl font-bold text-sm inline-flex">
                                <AlertCircle class="w-5 h-5" /> Menunggu Penilaian Essay
                            </div>
                        </div>
                        <div v-else-if="!isDiskualifikasi" class="relative z-10">
                            <span class="text-5xl font-black text-slate-900 tabular-nums">{{ peserta.nilai_akhir }}</span>
                            <span class="text-slate-400 font-bold ml-1">/ 100</span>
                        </div>
                        <div v-else class="relative z-10 mt-2">
                            <span class="text-2xl font-black text-rose-500">0.00</span>
                        </div>
                    </div>

                    <!-- Statistik Jawaban -->
                    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group">
                        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                            <BookOpen class="w-24 h-24 text-emerald-600" />
                        </div>
                        <h4 class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-4 relative z-10">Rincian Nilai Obyektif</h4>
                        
                        <div class="space-y-3 relative z-10">
                            <div class="flex justify-between items-center text-sm font-bold">
                                <span class="text-slate-600">Pilihan Ganda</span>
                                <span class="text-slate-900">{{ peserta.nilai_pg }}</span>
                            </div>
                            <div class="flex justify-between items-center text-sm font-bold">
                                <span class="text-slate-600">Benar / Salah</span>
                                <span class="text-slate-900">{{ peserta.nilai_bs }}</span>
                            </div>
                            <div class="flex justify-between items-center text-sm font-bold">
                                <span class="text-slate-600">Menjodohkan</span>
                                <span class="text-slate-900">{{ peserta.nilai_menjodohkan }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center">
                <Link :href="route('ujian.index')" class="inline-flex items-center gap-2 px-6 py-3 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl transition-colors">
                    <LayoutDashboard class="w-5 h-5" /> Kembali ke Dashboard
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
