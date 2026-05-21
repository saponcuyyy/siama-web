<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    CalendarClock, Plus, Search, MoreVertical, ShieldAlert, MonitorPlay, 
    X, Check, AlertTriangle, Play, Pause, XCircle, RefreshCcw
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
    rombel_id: '',
    waktu_mulai: '',
    waktu_selesai: '',
    toleransi_menit: 15,
    max_pelanggaran: 3,
    wajib_fullscreen: true,
    catatan: '',
});

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
const toggleTarget = ref({ id: null, newStatus: '', sesiNama: '' });
const isTogglingStatus = ref(false);

const toggleStatus = (id, newStatus, sesiNama) => {
    toggleTarget.value = { id, newStatus, sesiNama };
    showToggleModal.value = true;
};

const confirmToggleStatus = () => {
    isTogglingStatus.value = true;
    router.patch(route('admin.ujian.sesi.toggle', toggleTarget.value.id), 
        { status: toggleTarget.value.newStatus }, 
        {
            onSuccess: () => { showToggleModal.value = false; },
            onFinish: () => { isTogglingStatus.value = false; }
        }
    );
};

const statusConfig = (status) => {
    switch(status) {
        case 'berlangsung': return { label: 'Mulai Ujian', color: 'bg-emerald-600 hover:bg-emerald-700', icon: 'play', desc: 'Siswa akan dapat mengakses dan memulai ujian.' };
        case 'selesai': return { label: 'Selesaikan Ujian', color: 'bg-amber-500 hover:bg-amber-600', icon: 'pause', desc: 'Ujian akan ditutup. Siswa yang belum selesai tidak dapat mengakses lagi.' };
        case 'dibatalkan': return { label: 'Batalkan Sesi', color: 'bg-rose-600 hover:bg-rose-700', icon: 'x', desc: 'Sesi ini akan dibatalkan dan tidak dapat diaktifkan kembali.' };
        default: return { label: 'Konfirmasi', color: 'bg-indigo-600', icon: 'check', desc: '' };
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
                                    <span class="px-2.5 py-1 bg-slate-100 text-slate-700 font-bold rounded-lg text-xs">
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
                                    <button v-if="sesi.status === 'menunggu'" @click="toggleStatus(sesi.id, 'berlangsung', sesi.nama_sesi)" class="p-2 text-emerald-600 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition-colors" title="Mulai Ujian">
                                        <Play class="w-4 h-4" />
                                    </button>
                                    <button v-if="sesi.status === 'berlangsung'" @click="toggleStatus(sesi.id, 'selesai', sesi.nama_sesi)" class="p-2 text-amber-600 bg-amber-50 hover:bg-amber-100 rounded-lg transition-colors" title="Selesaikan Ujian">
                                        <Pause class="w-4 h-4" />
                                    </button>
                                    
                                    <Link :href="route('admin.ujian.sesi.monitor', sesi.id)" class="inline-flex p-2 text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors" title="Monitor Proktoring">
                                        <MonitorPlay class="w-4 h-4" />
                                    </Link>
                                    
                                    <button v-if="sesi.status === 'menunggu'" @click="toggleStatus(sesi.id, 'dibatalkan', sesi.nama_sesi)" class="p-2 text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg transition-colors" title="Batalkan">
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
                
                <!-- Pagination -->
                <div v-if="sesiList.links.length > 3" class="p-4 border-t border-slate-100 flex justify-center">
                    <div class="flex flex-wrap gap-1">
                        <template v-for="(link, k) in sesiList.links" :key="k">
                            <Link 
                                v-if="link.url"
                                :href="link.url"
                                class="px-3 py-1 rounded-lg text-sm font-medium transition-colors"
                                :class="link.active ? 'bg-indigo-600 text-white' : 'bg-slate-50 text-slate-600 hover:bg-slate-100'"
                                v-html="link.label"
                            />
                            <span v-else class="px-3 py-1 text-sm text-slate-400" v-html="link.label"></span>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Create Sesi -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white w-full max-w-2xl rounded-3xl shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between p-6 border-b border-slate-100">
                    <h2 class="text-xl font-black text-slate-900">Buat Sesi Ujian Baru</h2>
                    <button @click="showModal = false" class="p-2 text-slate-400 hover:text-slate-600 bg-slate-50 rounded-xl transition-colors">
                        <X class="w-5 h-5" />
                    </button>
                </div>
                
                <form @submit.prevent="submitForm" class="p-6">
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Nama Sesi</label>
                            <input type="text" v-model="form.nama_sesi" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Paket Ujian</label>
                            <select v-model="form.paket_ujian_id" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600">
                                <option value="">-- Pilih Paket --</option>
                                <option v-for="paket in paketList" :key="paket.id" :value="paket.id">{{ paket.nama }} ({{ paket.kode }})</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Rombel (Opsional)</label>
                            <select v-model="form.rombel_id" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600">
                                <option value="">Semua Rombel (Bebas)</option>
                                <option v-for="rombel in rombelList" :key="rombel.id" :value="rombel.id">{{ rombel.nama }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Waktu Mulai</label>
                            <input type="datetime-local" v-model="form.waktu_mulai" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Waktu Selesai (Batas Login)</label>
                            <input type="datetime-local" v-model="form.waktu_selesai" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600">
                        </div>

                        <div class="md:col-span-2 p-4 bg-rose-50 border border-rose-100 rounded-2xl">
                            <h4 class="text-sm font-bold text-rose-800 mb-3 flex items-center gap-2">
                                <ShieldAlert class="w-4 h-4" /> Aturan & Anti-Curang
                            </h4>
                            <div class="grid sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-rose-700 mb-1">Max Pelanggaran (Pindah Tab)</label>
                                    <input type="number" min="1" v-model="form.max_pelanggaran" class="w-full px-3 py-2 bg-white border border-rose-200 rounded-lg focus:ring-rose-500 focus:border-rose-500 text-sm">
                                </div>
                                <div class="flex items-center mt-6">
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <input type="checkbox" v-model="form.wajib_fullscreen" class="w-5 h-5 text-rose-600 border-rose-300 rounded focus:ring-rose-600">
                                        <span class="text-sm font-bold text-rose-800">Wajibkan Fullscreen</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end gap-3">
                        <button type="button" @click="showModal = false" class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                            Batal
                        </button>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors flex items-center gap-2">
                            <Check class="w-4 h-4" /> {{ form.processing ? 'Menyimpan...' : 'Simpan Sesi' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal: Konfirmasi Toggle Status Sesi -->
        <div v-if="showToggleModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden">
                <div class="p-8 text-center">
                    <!-- Icon berdasar status -->
                    <div 
                        class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-6"
                        :class="{
                            'bg-emerald-50 text-emerald-600': toggleTarget.newStatus === 'berlangsung',
                            'bg-amber-50 text-amber-600': toggleTarget.newStatus === 'selesai',
                            'bg-rose-50 text-rose-600': toggleTarget.newStatus === 'dibatalkan',
                        }"
                    >
                        <Play v-if="toggleTarget.newStatus === 'berlangsung'" class="w-9 h-9" />
                        <Pause v-else-if="toggleTarget.newStatus === 'selesai'" class="w-9 h-9" />
                        <XCircle v-else class="w-9 h-9" />
                    </div>

                    <h3 class="text-xl font-black text-slate-900 mb-2">{{ statusConfig(toggleTarget.newStatus).label }}?</h3>
                    <p class="text-slate-500 font-medium text-sm mb-4 leading-relaxed">
                        Sesi: <span class="font-bold text-slate-900">{{ toggleTarget.sesiNama }}</span>
                    </p>

                    <!-- Warning Info -->  
                    <div 
                        class="p-4 rounded-2xl text-sm font-medium text-left mb-7 flex items-start gap-3"
                        :class="{
                            'bg-emerald-50 text-emerald-800 border border-emerald-100': toggleTarget.newStatus === 'berlangsung',
                            'bg-amber-50 text-amber-800 border border-amber-100': toggleTarget.newStatus === 'selesai',
                            'bg-rose-50 text-rose-800 border border-rose-100': toggleTarget.newStatus === 'dibatalkan',
                        }"
                    >
                        <AlertTriangle class="w-4 h-4 flex-shrink-0 mt-0.5" />
                        <span>{{ statusConfig(toggleTarget.newStatus).desc }}</span>
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
                            :class="statusConfig(toggleTarget.newStatus).color"
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
