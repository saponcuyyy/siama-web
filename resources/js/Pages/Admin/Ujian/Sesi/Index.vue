<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { 
    CalendarClock, Plus, Search, MoreVertical, ShieldAlert, MonitorPlay, 
    X, Check, AlertTriangle, Play, Pause, XCircle, RefreshCcw, Printer
} from 'lucide-vue-next';
import dayjs from 'dayjs';
import 'dayjs/locale/id';
dayjs.locale('id');

const props = defineProps({
    sesiList: Object,
    filters: Object,
    paketList: Array,
    rombelList: Array,
});

const search = ref(props.filters.search || '');
const showModal = ref(false);

const form = useForm({
    nama_sesi: '',
    paket_ujian_id: '',
    rombel_ids: [],
    waktu_mulai: '',
    waktu_selesai: '',
    toleransi_menit: 15,
    max_pelanggaran: 3,
    wajib_fullscreen: true,
    catatan: '',
});

const toggleRombel = (id) => {
    const index = form.rombel_ids.indexOf(id);
    if (index === -1) {
        form.rombel_ids.push(id);
    } else {
        form.rombel_ids.splice(index, 1);
    }
};

const handleSearch = () => {
    router.get(route('admin.ujian.sesi.index'), { search: search.value }, { preserveState: true, replace: true });
};

const submitForm = () => {
    form.post(route('admin.ujian.sesi.store'), {
        onSuccess: () => {
            showModal.value = false;
            form.reset();
        }
    });
};

// ─── Toggle Status Modal ───────────────────────
const showToggleModal = ref(false);
const toggleTarget = ref(null);
const isTogglingStatus = ref(false);

const toggleStatus = (sesi, newStatus) => {
    toggleTarget.value = { ...sesi, _newStatus: newStatus };
    showToggleModal.value = true;
};

const confirmToggleStatus = () => {
    isTogglingStatus.value = true;
    router.patch(route('admin.ujian.sesi.toggle', toggleTarget.value.hashid), 
        { status: toggleTarget.value._newStatus }, 
        {
            onSuccess: () => { showToggleModal.value = false; },
            onFinish: () => { isTogglingStatus.value = false; }
        }
    );
};

const statusLabel = (newStatus) => {
    switch(newStatus) {
        case 'berlangsung': return 'Mulai Ujian';
        case 'selesai': return 'Selesaikan Ujian';
        case 'dibatalkan': return 'Batalkan Sesi';
        default: return '';
    }
};

const statusColor = (newStatus) => {
    switch(newStatus) {
        case 'berlangsung': return 'bg-emerald-600 hover:bg-emerald-700';
        case 'selesai': return 'bg-amber-500 hover:bg-amber-600';
        case 'dibatalkan': return 'bg-rose-600 hover:bg-rose-700';
        default: return 'bg-indigo-600';
    }
};

const statusDesc = (newStatus) => {
    switch(newStatus) {
        case 'berlangsung': return 'Siswa akan dapat mengakses dan memulai ujian.';
        case 'selesai': return 'Ujian akan ditutup. Siswa yang belum selesai tidak dapat mengakses lagi.';
        case 'dibatalkan': return 'Sesi ini akan dibatalkan dan tidak dapat diaktifkan kembali.';
        default: return '';
    }
};

const formatDate = (date) => dayjs(date).format('DD MMM YYYY HH:mm');

const getStatusColor = (status) => {
    switch(status) {
        case 'menunggu': return 'bg-amber-100 text-amber-700';
        case 'berlangsung': return 'bg-emerald-100 text-emerald-700';
        case 'selesai': return 'bg-indigo-100 text-indigo-700';
        case 'dibatalkan': return 'bg-rose-100 text-rose-700';
        default: return 'bg-slate-100 text-slate-700';
    }
};
</script>

<template>
    <Head title="Manajemen Sesi Ujian" />
    
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between gap-4 items-start sm:items-center">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                        <CalendarClock class="w-7 h-7 text-indigo-600" />
                        Sesi Ujian (Proctoring)
                    </h1>
                    <p class="text-slate-500 font-medium mt-1">Kelola jadwal pelaksanaan dan pantau ujian secara real-time.</p>
                </div>
                <button @click="showModal = true" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl flex items-center gap-2 transition-colors shadow-lg shadow-indigo-200">
                    <Plus class="w-5 h-5" /> Buat Sesi Ujian
                </button>
            </div>

            <!-- Filters -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row gap-4 items-center justify-between">
                <div class="relative w-full sm:w-96">
                    <input 
                        type="text" 
                        v-model="search" 
                        @keyup.enter="handleSearch"
                        placeholder="Cari nama sesi..." 
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
                                <th class="p-4">Paket / Pelajaran</th>
                                <th class="p-4">Waktu Pelaksanaan</th>
                                <th class="p-4">Peserta (Rombel)</th>
                                <th class="p-4">Status</th>
                                <th class="p-4 pr-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <tr v-for="sesi in sesiList.data" :key="sesi.id" class="hover:bg-slate-50/50 transition-colors">
                                <td class="p-4 pl-6">
                                    <p class="font-bold text-slate-900">{{ sesi.nama_sesi }}</p>
                                    <div class="flex items-center gap-2 mt-1 text-xs">
                                        <span class="text-slate-500 font-mono bg-slate-100 px-2 py-0.5 rounded">{{ sesi.token }}</span>
                                        <span v-if="sesi.wajib_fullscreen" class="text-rose-600 font-bold flex items-center gap-1" title="Wajib Fullscreen">
                                            <ShieldAlert class="w-3 h-3" /> Strict
                                        </span>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <p class="font-bold text-slate-800">{{ sesi.paket_ujian?.nama }}</p>
                                    <p class="text-xs text-indigo-600 font-bold mt-1">{{ sesi.paket_ujian?.mata_pelajaran?.nama }}</p>
                                </td>
                                <td class="p-4 text-slate-600">
                                    <p class="font-medium">{{ formatDate(sesi.waktu_mulai) }}</p>
                                    <p class="text-xs mt-0.5 text-slate-400">s/d {{ formatDate(sesi.waktu_selesai) }}</p>
                                </td>
                                <td class="p-4">
                                    <div v-if="sesi.rombels?.length" class="flex flex-wrap gap-1">
                                        <span v-for="r in sesi.rombels" :key="r.id" class="px-2.5 py-1 bg-slate-100 text-slate-700 font-bold rounded-lg text-xs">
                                            {{ r.nama }}
                                        </span>
                                    </div>
                                    <span v-else class="px-2.5 py-1 bg-slate-100 text-slate-700 font-bold rounded-lg text-xs">
                                        {{ sesi.rombel ? sesi.rombel.nama : 'Semua (Terbuka)' }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <span class="px-3 py-1 text-[11px] font-black uppercase tracking-wider rounded-full border border-transparent"
                                          :class="getStatusColor(sesi.status)">
                                        {{ sesi.status }}
                                    </span>
                                </td>
                                <td class="p-4 pr-6 text-right space-x-2">
                                    <!-- Aksi berdasar status -->
                                    <button v-if="sesi.status === 'menunggu'" @click="toggleStatus(sesi, 'berlangsung')" class="p-2 text-emerald-600 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition-colors" title="Mulai Ujian">
                                        <Play class="w-4 h-4" />
                                    </button>
                                    <button v-if="sesi.status === 'berlangsung'" @click="toggleStatus(sesi, 'selesai')" class="p-2 text-amber-600 bg-amber-50 hover:bg-amber-100 rounded-lg transition-colors" title="Selesaikan Ujian">
                                        <Pause class="w-4 h-4" />
                                    </button>
                                    
                                    <Link :href="route('admin.ujian.sesi.monitor', sesi.hashid)" class="inline-flex p-2 text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors" title="Monitor Proktoring">
                                        <MonitorPlay class="w-4 h-4" />
                                    </Link>
                                                                       
                                    <button v-if="sesi.status === 'menunggu'" @click="toggleStatus(sesi, 'dibatalkan')" class="p-2 text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg transition-colors" title="Batalkan">
                                        <XCircle class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="sesiList.data.length === 0">
                                <td colspan="6" class="p-8 text-center text-slate-500 font-medium">
                                    Tidak ada data sesi ujian.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <Pagination :data="sesiList" />
            </div>
        </div>

        <!-- Modal Create Sesi -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white w-full sm:max-w-2xl sm:rounded-3xl rounded-t-3xl shadow-2xl overflow-hidden animate-in fade-in sm:zoom-in-95 duration-200 sm:slide-in-from-bottom-0 slide-in-from-bottom-8 max-h-[95vh] flex flex-col">
                <div class="flex items-center justify-between px-5 sm:px-6 py-4 border-b border-slate-100 shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-indigo-100 rounded-xl flex items-center justify-center">
                            <CalendarClock class="w-5 h-5 text-indigo-600" />
                        </div>
                        <div>
                            <h2 class="text-lg font-black text-slate-900">Buat Sesi Ujian</h2>
                            <p class="text-xs text-slate-500 font-medium">Atur jadwal dan aturan ujian</p>
                        </div>
                    </div>
                    <button @click="showModal = false" class="p-2 text-slate-400 hover:text-slate-600 bg-slate-50 hover:bg-slate-100 rounded-xl transition-colors">
                        <X class="w-5 h-5" />
                    </button>
                </div>
                
                <form @submit.prevent="submitForm" class="flex-1 overflow-y-auto">
                    <div class="p-5 sm:p-6 space-y-6">
                        <!-- Basic Info Section -->
                        <div>
                            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 bg-indigo-400 rounded-full"></span>
                                Informasi Dasar
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Nama Sesi</label>
                                    <input type="text" v-model="form.nama_sesi" required placeholder="Ex: UTS Ganjil 2026" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-600/20 focus:border-indigo-600 transition-all text-sm placeholder:text-slate-400">
                                    <p v-if="form.errors.nama_sesi" class="text-xs text-rose-600 mt-1 font-bold">{{ form.errors.nama_sesi }}</p>
                                </div>

                                <div class="grid sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Paket Ujian</label>
                                        <select v-model="form.paket_ujian_id" required class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-600/20 focus:border-indigo-600 transition-all text-sm">
                                            <option value="" disabled>Pilih paket ujian</option>
                                            <option v-for="paket in paketList" :key="paket.id" :value="paket.id">{{ paket.nama }} ({{ paket.kode }})</option>
                                        </select>
                                        <p v-if="form.errors.paket_ujian_id" class="text-xs text-rose-600 mt-1 font-bold">{{ form.errors.paket_ujian_id }}</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Toleransi Keterlambatan</label>
                                        <div class="relative">
                                            <input type="number" min="0" v-model="form.toleransi_menit" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-600/20 focus:border-indigo-600 transition-all text-sm pr-16">
                                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">menit</span>
                                        </div>
                                        <p v-if="form.errors.toleransi_menit" class="text-xs text-rose-600 mt-1 font-bold">{{ form.errors.toleransi_menit }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Schedule Section -->
                        <div>
                            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full"></span>
                                Jadwal Pelaksanaan
                            </h3>
                            <div class="grid sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Waktu Mulai</label>
                                    <input type="datetime-local" v-model="form.waktu_mulai" required class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-600/20 focus:border-indigo-600 transition-all text-sm">
                                    <p v-if="form.errors.waktu_mulai" class="text-xs text-rose-600 mt-1 font-bold">{{ form.errors.waktu_mulai }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Waktu Selesai</label>
                                    <input type="datetime-local" v-model="form.waktu_selesai" required class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-600/20 focus:border-indigo-600 transition-all text-sm">
                                    <p v-if="form.errors.waktu_selesai" class="text-xs text-rose-600 mt-1 font-bold">{{ form.errors.waktu_selesai }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Rombel Section -->
                        <div>
                            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 bg-amber-400 rounded-full"></span>
                                Peserta (Rombel)
                            </h3>
                            <div class="bg-slate-50 border border-slate-200 rounded-2xl overflow-hidden">
                                <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-500">Pilih rombel yang dapat mengakses ujian</span>
                                    <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-lg">{{ form.rombel_ids.length }} dipilih</span>
                                </div>
                                <div class="max-h-48 overflow-y-auto p-3 space-y-1">
                                    <label v-for="rombel in rombelList" :key="rombel.id"
                                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl cursor-pointer transition-all"
                                        :class="form.rombel_ids.includes(rombel.id) ? 'bg-white shadow-sm border border-indigo-200' : 'hover:bg-white/60 border border-transparent'"
                                    >
                                        <div class="relative flex items-center justify-center">
                                            <input type="checkbox" :checked="form.rombel_ids.includes(rombel.id)" @change="toggleRombel(rombel.id)" class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-600">
                                        </div>
                                        <span class="text-sm font-medium text-slate-700">{{ rombel.nama }}</span>
                                    </label>
                                    <div v-if="rombelList.length === 0" class="text-sm text-slate-400 text-center py-4">
                                        Tidak ada rombel tersedia.
                                    </div>
                                </div>
                            </div>
                            <p class="text-xs text-slate-400 mt-2 font-medium flex items-center gap-1">
                                <span class="w-1 h-1 bg-slate-300 rounded-full"></span>
                                Kosongkan semua jika ujian terbuka untuk semua rombel
                            </p>
                        </div>

                        <!-- Anti-Cheat Section -->
                        <div class="bg-gradient-to-br from-rose-50 to-orange-50 border border-rose-200 rounded-2xl p-5">
                            <h4 class="text-sm font-bold text-rose-800 mb-4 flex items-center gap-2">
                                <ShieldAlert class="w-4 h-4" /> Aturan & Anti-Curang
                            </h4>
                            <div class="flex flex-col sm:flex-row sm:items-center gap-4 sm:gap-6">
                                <div class="flex-1">
                                    <label class="block text-xs font-bold text-rose-700 mb-1.5">Maks. Pelanggaran</label>
                                    <div class="relative">
                                        <input type="number" min="1" v-model="form.max_pelanggaran" class="w-full px-4 py-2.5 bg-white border border-rose-200 rounded-xl focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition-all text-sm pr-16">
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-rose-400">kali</span>
                                    </div>
                                    <p v-if="form.errors.max_pelanggaran" class="text-xs text-rose-600 mt-1 font-bold">{{ form.errors.max_pelanggaran }}</p>
                                    <p class="text-[11px] text-rose-500 mt-1">Peringatan saat siswa berpindah tab</p>
                                </div>
                                <div class="flex items-center">
                                    <label class="flex items-center gap-3 cursor-pointer p-3 bg-white/60 rounded-xl border border-rose-200 hover:bg-white transition-all">
                                        <input type="checkbox" v-model="form.wajib_fullscreen" class="w-5 h-5 text-rose-600 border-rose-300 rounded focus:ring-rose-600">
                                        <div>
                                            <span class="text-sm font-bold text-rose-800">Wajibkan Fullscreen</span>
                                            <p class="text-[11px] text-rose-500">Siswa tidak bisa mengecilkan layar</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-5 sm:px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 shrink-0">
                        <div class="text-xs text-slate-400 font-medium">
                            <span class="text-indigo-600 font-bold">{{ form.rombel_ids.length }}</span> rombel terpilih
                        </div>
                        <div class="flex gap-3 w-full sm:w-auto">
                            <button type="button" @click="showModal = false" class="flex-1 sm:flex-none px-5 py-2.5 text-sm font-bold text-slate-600 bg-white hover:bg-slate-50 border border-slate-200 rounded-xl transition-all">
                                Batal
                            </button>
                            <button type="submit" :disabled="form.processing" class="flex-1 sm:flex-none px-6 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:hover:bg-indigo-600 rounded-xl transition-all flex items-center gap-2 shadow-lg shadow-indigo-200">
                                <Check class="w-4 h-4" /> {{ form.processing ? 'Menyimpan...' : 'Simpan Sesi' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal: Konfirmasi Toggle Status Sesi -->
        <div v-if="showToggleModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white w-full sm:max-w-md sm:rounded-3xl rounded-t-3xl shadow-2xl overflow-hidden animate-in fade-in sm:zoom-in-95 duration-200 sm:slide-in-from-bottom-0 slide-in-from-bottom-8">
                <div class="p-6 sm:p-8 text-center">
                    <div 
                        class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl flex items-center justify-center mx-auto mb-5"
                        :class="{
                            'bg-emerald-50 text-emerald-600': toggleTarget._newStatus === 'berlangsung',
                            'bg-amber-50 text-amber-600': toggleTarget._newStatus === 'selesai',
                            'bg-rose-50 text-rose-600': toggleTarget._newStatus === 'dibatalkan',
                        }"
                    >
                        <Play v-if="toggleTarget._newStatus === 'berlangsung'" class="w-7 h-7 sm:w-9 sm:h-9" />
                        <Pause v-else-if="toggleTarget._newStatus === 'selesai'" class="w-7 h-7 sm:w-9 sm:h-9" />
                        <XCircle v-else class="w-7 h-7 sm:w-9 sm:h-9" />
                    </div>

                    <h3 class="text-lg sm:text-xl font-black text-slate-900 mb-2">{{ statusLabel(toggleTarget._newStatus) }}?</h3>
                    <p class="text-xs sm:text-sm text-slate-500 font-medium mb-5 leading-relaxed">
                        Sesi: <span class="font-bold text-slate-900">{{ toggleTarget.nama_sesi }}</span>
                    </p>

                    <div 
                        class="p-3 sm:p-4 rounded-2xl text-xs sm:text-sm font-medium text-left mb-6 flex items-start gap-3"
                        :class="{
                            'bg-emerald-50 text-emerald-800 border border-emerald-100': toggleTarget._newStatus === 'berlangsung',
                            'bg-amber-50 text-amber-800 border border-amber-100': toggleTarget._newStatus === 'selesai',
                            'bg-rose-50 text-rose-800 border border-rose-100': toggleTarget._newStatus === 'dibatalkan',
                        }"
                    >
                        <AlertTriangle class="w-4 h-4 flex-shrink-0 mt-0.5" />
                        <span>{{ statusDesc(toggleTarget._newStatus) }}</span>
                    </div>

                    <div class="flex gap-3">
                        <button 
                            @click="showToggleModal = false" 
                            :disabled="isTogglingStatus"
                            class="flex-1 py-3 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors"
                        >
                            Batal
                        </button>
                        <button 
                            @click="confirmToggleStatus"
                            :disabled="isTogglingStatus"
                            class="flex-1 py-3 text-sm font-bold text-white rounded-xl transition-colors flex items-center justify-center gap-2"
                            :class="statusColor(toggleTarget._newStatus)"
                        >
                            <span v-if="isTogglingStatus" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
                            <span>{{ isTogglingStatus ? 'Memproses...' : 'Ya, Konfirmasi' }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
