<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    FileSpreadsheet, ArrowLeft, Download, Award
} from 'lucide-vue-next';
import dayjs from 'dayjs';
import 'dayjs/locale/id';
dayjs.locale('id');

const props = defineProps({
    sesi: Object,
    peserta: Array,
    perRombel: Array,
});

const formatDate = (date) => dayjs(date).format('DD MMM YYYY HH:mm');
</script>

<template>
    <Head :title="`Laporan Nilai: ${sesi.nama_sesi}`" />
    
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex items-center gap-4">
                    <Link :href="route('admin.ujian.laporan.index')" class="p-2 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">
                        <ArrowLeft class="w-5 h-5 text-slate-600" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                            {{ sesi.nama_sesi }}
                        </h1>
                        <p class="text-slate-500 font-medium mt-1">
                            {{ sesi.paket_ujian?.mata_pelajaran?.nama }} &mdash; Selesai: {{ formatDate(sesi.waktu_selesai) }}
                        </p>
                    </div>
                </div>
                
                <a :href="route('admin.ujian.laporan.export', { sesi: sesi.hashid, format: 'pdf' })" target="_blank" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold rounded-xl flex items-center gap-2 transition-colors shadow-lg shadow-rose-200">
                    <Download class="w-5 h-5" /> Export PDF
                </a>
            </div>

            <!-- Recap per Rombel -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3" v-if="perRombel.length > 1">
                <div v-for="r in perRombel" :key="r.rombel"
                     class="bg-white rounded-3xl border border-slate-200 shadow-sm p-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="font-bold text-slate-900 text-lg">{{ r.rombel }}</h3>
                        <span class="px-2.5 py-0.5 bg-indigo-100 text-indigo-700 text-xs font-bold rounded-lg">
                            {{ r.jumlah }} peserta
                        </span>
                    </div>
                    <div class="flex items-end gap-4">
                        <div class="text-center flex-1">
                            <p class="text-2xl font-black text-slate-900">{{ r.rata_rata }}</p>
                            <p class="text-xs text-slate-500 font-medium mt-0.5">Rata-rata</p>
                        </div>
                        <div class="text-center flex-1">
                            <p class="text-2xl font-black text-emerald-600">{{ r.tertinggi }}</p>
                            <p class="text-xs text-slate-500 font-medium mt-0.5">Tertinggi</p>
                        </div>
                        <div class="text-center flex-1">
                            <p class="text-2xl font-black text-rose-600">{{ r.terendah }}</p>
                            <p class="text-xs text-slate-500 font-medium mt-0.5">Terendah</p>
                        </div>
                    </div>
                    <div class="flex gap-3 text-xs font-bold pt-2 border-t border-slate-100">
                        <span class="px-2 py-1 bg-emerald-50 text-emerald-700 rounded-lg border border-emerald-200">
                            Lulus: {{ r.lulus }}
                        </span>
                        <span class="px-2 py-1 bg-rose-50 text-rose-700 rounded-lg border border-rose-200">
                            Tidak Lulus: {{ r.tidak_lulus }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                    <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <Award class="w-5 h-5 text-indigo-600" />
                        Rekapitulasi Nilai Siswa
                    </h2>
                    <span class="px-3 py-1 bg-indigo-100 text-indigo-700 text-sm font-bold rounded-lg border border-indigo-200">
                        {{ peserta.length }} Peserta
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-white border-b border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider">
                                <th class="p-4 pl-6 w-16 text-center">No</th>
                                <th class="p-4">Nama Siswa / NISN</th>
                                <th class="p-4">Rombel</th>
                                <th class="p-4 text-center">Waktu Submit</th>
                                <th class="p-4 pr-6 text-right">Nilai Akhir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <tr v-for="(p, index) in peserta" :key="p.id" class="hover:bg-slate-50/50 transition-colors">
                                <td class="p-4 pl-6 text-center font-bold text-slate-400">
                                    {{ index + 1 }}
                                </td>
                                <td class="p-4">
                                    <p class="font-bold text-slate-900">{{ p.siswa?.nama }}</p>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ p.siswa?.nisn }}</p>
                                </td>
                                <td class="p-4 font-medium text-slate-600">
                                    {{ p.siswa?.rombel?.nama || '-' }}
                                </td>
                                <td class="p-4 text-center text-slate-500 font-medium">
                                    {{ p.waktu_selesai ? dayjs(p.waktu_selesai).format('HH:mm:ss') : '-' }}
                                </td>
                                <td class="p-4 pr-6 text-right">
                                    <span class="inline-flex items-center justify-center min-w-[3rem] px-3 py-1 rounded-xl font-black text-lg border-2"
                                          :class="p.nilai_akhir >= 75 ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-rose-50 border-rose-200 text-rose-700'">
                                        {{ p.nilai_akhir || 0 }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="peserta.length === 0">
                                <td colspan="5" class="p-8 text-center text-slate-500 font-medium">
                                    Belum ada peserta yang menyelesaikan ujian ini.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
