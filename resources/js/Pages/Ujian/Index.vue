<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Calendar, Clock, FileText, CheckCircle, XCircle } from 'lucide-vue-next';
import dayjs from 'dayjs';
import 'dayjs/locale/id';

dayjs.locale('id');

const props = defineProps({
    sesi_aktif: Array,
    riwayat: Array,
});

const formatDate = (date) => dayjs(date).format('DD MMMM YYYY, HH:mm');
</script>

<template>
    <Head title="Ujian Online CBT" />
    
    <AuthenticatedLayout>
        <div class="max-w-6xl mx-auto space-y-8">
            <!-- Header -->
            <div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">Daftar Ujian (CBT)</h1>
                <p class="text-slate-500 mt-1 font-medium">Sistem Ujian Berbasis Komputer</p>
            </div>

            <!-- Sesi Aktif -->
            <div>
                <h2 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 bg-rose-500 rounded-full animate-pulse"></span>
                    Ujian Aktif
                </h2>
                
                <div v-if="sesi_aktif.length === 0" class="bg-white rounded-3xl p-8 border border-slate-200 text-center shadow-sm">
                    <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4 text-slate-400">
                        <FileText class="w-8 h-8" stroke-width="1.5" />
                    </div>
                    <h3 class="text-slate-900 font-bold mb-1">Belum ada ujian aktif</h3>
                    <p class="text-slate-500 text-sm">Silakan tunggu jadwal ujian dari guru Anda.</p>
                </div>

                <div v-else class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="sesi in sesi_aktif" :key="sesi.id" class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden hover:shadow-lg transition-shadow duration-300 relative group">
                        <div class="absolute inset-0 bg-gradient-to-br from-indigo-600 to-violet-600 opacity-0 group-hover:opacity-5 transition-opacity duration-300 pointer-events-none"></div>
                        
                        <div class="p-6">
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider mb-4"
                                :class="sesi.status === 'berlangsung' ? 'bg-indigo-100 text-indigo-600' : 'bg-amber-100 text-amber-600'">
                                {{ sesi.status === 'berlangsung' ? 'Sedang Berlangsung' : 'Menunggu Waktu' }}
                            </div>

                            <h3 class="text-xl font-bold text-slate-900 leading-tight mb-2 line-clamp-2">
                                {{ sesi.nama_sesi }}
                            </h3>
                            <p class="text-sm font-semibold text-indigo-600 mb-4">
                                {{ sesi.paket_ujian.mata_pelajaran?.nama || 'Mata Pelajaran' }}
                            </p>

                            <div class="space-y-3 mb-6">
                                <div class="flex items-center gap-3 text-sm text-slate-600 font-medium">
                                    <div class="w-8 h-8 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 flex-shrink-0">
                                        <Calendar class="w-4 h-4" />
                                    </div>
                                    <span>{{ formatDate(sesi.waktu_mulai) }}</span>
                                </div>
                                <div class="flex items-center gap-3 text-sm text-slate-600 font-medium">
                                    <div class="w-8 h-8 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 flex-shrink-0">
                                        <Clock class="w-4 h-4" />
                                    </div>
                                    <span>{{ sesi.paket_ujian.durasi_menit }} Menit</span>
                                </div>
                            </div>
                            
                            <Link :href="route('ujian.masuk', sesi.id)" class="w-full flex items-center justify-center px-4 py-3 bg-slate-900 hover:bg-slate-800 text-white text-sm font-bold rounded-2xl transition-colors">
                                Masuk Ruang Ujian
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Riwayat Ujian -->
            <div>
                <h2 class="text-lg font-bold text-slate-900 mb-4">Riwayat Ujian</h2>
                
                <div v-if="riwayat.length === 0" class="text-center py-8 text-slate-500 font-medium">
                    Belum ada riwayat ujian.
                </div>
                
                <div v-else class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm">
                    <ul class="divide-y divide-slate-100">
                        <li v-for="log in riwayat" :key="log.id" class="p-6 hover:bg-slate-50 transition-colors">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div>
                                    <h4 class="font-bold text-slate-900">{{ log.sesi_ujian.nama_sesi }}</h4>
                                    <p class="text-sm text-slate-500 font-medium mt-1">
                                        Diselesaikan pada: {{ formatDate(log.selesai_at) }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div v-if="log.status === 'selesai'" class="flex items-center gap-2 text-emerald-600 bg-emerald-50 px-3 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider">
                                        <CheckCircle class="w-4 h-4" /> Selesai
                                    </div>
                                    <div v-else class="flex items-center gap-2 text-rose-600 bg-rose-50 px-3 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider">
                                        <XCircle class="w-4 h-4" /> Diskualifikasi
                                    </div>
                                    <Link :href="route('ujian.hasil', log.sesi_ujian_id)" class="px-4 py-2 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 text-sm font-bold rounded-xl transition-colors">
                                        Lihat Hasil
                                    </Link>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
