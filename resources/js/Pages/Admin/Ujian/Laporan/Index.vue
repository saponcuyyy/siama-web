<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    FileSpreadsheet, Search, Eye, Users
} from 'lucide-vue-next';
import dayjs from 'dayjs';
import 'dayjs/locale/id';
dayjs.locale('id');

const props = defineProps({
    sesiList: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');

const handleSearch = () => {
    router.get(route('admin.ujian.laporan.index'), { search: search.value }, { preserveState: true, replace: true });
};

const formatDate = (date) => dayjs(date).format('DD MMM YYYY');
</script>

<template>
    <Head title="Laporan Ujian" />
    
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between gap-4 items-start sm:items-center">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                        <FileSpreadsheet class="w-7 h-7 text-indigo-600" />
                        Laporan Hasil Ujian
                    </h1>
                    <p class="text-slate-500 font-medium mt-1">Rekapitulasi dan riwayat nilai dari sesi ujian yang telah berjalan.</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row gap-4 items-center justify-between">
                <div class="relative w-full sm:w-96">
                    <input 
                        type="text" 
                        v-model="search" 
                        @keyup.enter="handleSearch"
                        placeholder="Cari sesi ujian..." 
                        class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600 transition-colors"
                    >
                    <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider">
                                <th class="p-4 pl-6">Sesi Ujian</th>
                                <th class="p-4">Mata Pelajaran</th>
                                <th class="p-4">Waktu Selesai</th>
                                <th class="p-4 text-center">Partisipan</th>
                                <th class="p-4 pr-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <tr v-for="sesi in sesiList.data" :key="sesi.id" class="hover:bg-slate-50/50 transition-colors">
                                <td class="p-4 pl-6">
                                    <p class="font-bold text-slate-900">{{ sesi.nama_sesi }}</p>
                                    <p class="text-xs text-slate-500 font-mono mt-1">Token: {{ sesi.token }}</p>
                                </td>
                                <td class="p-4">
                                    <p class="font-bold text-slate-800">{{ sesi.paket_ujian?.mata_pelajaran?.nama }}</p>
                                    <p class="text-xs text-indigo-600 mt-1">{{ sesi.paket_ujian?.nama }}</p>
                                </td>
                                <td class="p-4 font-medium text-slate-600">
                                    {{ formatDate(sesi.waktu_selesai) }}
                                </td>
                                <td class="p-4 text-center">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 text-slate-700 font-bold rounded-lg text-xs">
                                        <Users class="w-3.5 h-3.5 text-slate-400" />
                                        {{ sesi.peserta_count }} Peserta
                                    </span>
                                </td>
                                <td class="p-4 pr-6 text-right">
                                    <Link :href="route('admin.ujian.laporan.sesi', sesi.id)" class="inline-flex px-4 py-2 text-indigo-700 bg-indigo-50 hover:bg-indigo-100 font-bold text-sm rounded-xl transition-colors items-center gap-2">
                                        <Eye class="w-4 h-4" /> Lihat Nilai
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="sesiList.data.length === 0">
                                <td colspan="5" class="p-8 text-center text-slate-500 font-medium">
                                    Tidak ada riwayat ujian yang telah selesai.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination omitted for brevity, standard implementation -->
            </div>
        </div>
    </AuthenticatedLayout>
</template>
