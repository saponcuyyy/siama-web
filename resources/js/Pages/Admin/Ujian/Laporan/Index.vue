<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    FileSpreadsheet, Search, Eye, Users, X
} from 'lucide-vue-next';
import dayjs from 'dayjs';
import 'dayjs/locale/id';
dayjs.locale('id');

const props = defineProps({
    sesiList: Object,
    filters: Object,
    rombelList: Array,
    semesterList: Array,
});

const showModal = ref(false);
const downloadForm = ref({
    rombel_id: '',
    semester_id: ''
});

const handleDownloadRekap = () => {
    if (!downloadForm.value.rombel_id || !downloadForm.value.semester_id) return;
    
    // Buka di tab baru untuk download file Excel
    const url = route('admin.ujian.laporan.rekap-rombel', {
        rombel_id: downloadForm.value.rombel_id,
        semester_id: downloadForm.value.semester_id
    });
    window.open(url, '_blank');
    showModal.value = false;
};

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
                <div class="flex items-center gap-3 mt-4 sm:mt-0">
                    <button @click="showModal = true" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl flex items-center gap-2 transition-colors shadow-lg shadow-emerald-200">
                        <FileSpreadsheet class="w-5 h-5" /> Download Rekap Rombel
                    </button>
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
                                        {{ sesi.peserta_ujian_count }} Peserta
                                    </span>
                                </td>
                                <td class="p-4 pr-6 text-right">
                                    <Link :href="route('admin.ujian.laporan.sesi', sesi.hashid)" class="inline-flex px-4 py-2 text-indigo-700 bg-indigo-50 hover:bg-indigo-100 font-bold text-sm rounded-xl transition-colors items-center gap-2">
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

        <!-- Modal Download Rekap Rombel -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between p-6 border-b border-slate-100 shrink-0">
                    <h2 class="text-xl font-black text-slate-900">Download Rekap Nilai</h2>
                    <button @click="showModal = false" class="p-2 text-slate-400 hover:text-slate-600 bg-slate-50 rounded-xl transition-colors">
                        <X class="w-5 h-5" />
                    </button>
                </div>
                
                <form @submit.prevent="handleDownloadRekap" class="p-6 space-y-5">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Pilih Semester / Periode</label>
                        <select v-model="downloadForm.semester_id" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600 font-medium">
                            <option value="">-- Pilih Semester --</option>
                            <option v-for="sem in semesterList" :key="sem.id" :value="sem.id">
                                {{ sem.nama }} {{ sem.is_active ? '(Aktif)' : '' }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Pilih Kelas / Rombel</label>
                        <select v-model="downloadForm.rombel_id" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600 font-medium">
                            <option value="">-- Pilih Rombel --</option>
                            <option v-for="rombel in rombelList" :key="rombel.id" :value="rombel.id">
                                {{ rombel.tingkat }} - {{ rombel.nama }}
                            </option>
                        </select>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
                        <button type="button" @click="showModal = false" class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                            Batal
                        </button>
                        <button type="submit" :disabled="!downloadForm.rombel_id || !downloadForm.semester_id" class="px-5 py-2.5 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-xl transition-colors flex items-center gap-2">
                            <FileSpreadsheet class="w-4 h-4" /> Download Excel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
