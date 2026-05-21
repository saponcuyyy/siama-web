<script setup>
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, onMounted, onUnmounted } from 'vue';
import { RefreshCcw, Activity, AlertOctagon, CheckCircle2, Clock, Users, ServerOff, Maximize, AlertTriangle } from 'lucide-vue-next';

const props = defineProps({
    sesi: Object,
    peserta: Array,
});

const autoRefresh = ref(true);
const refreshInterval = ref(null);
const lastUpdated = ref(new Date());

const fetchLatestData = () => {
    router.reload({
        only: ['peserta'],
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            lastUpdated.value = new Date();
        }
    });
};

onMounted(() => {
    if (autoRefresh.value) {
        startAutoRefresh();
    }
});

onUnmounted(() => {
    stopAutoRefresh();
});

const startAutoRefresh = () => {
    refreshInterval.value = setInterval(fetchLatestData, 10000); // refresh every 10 seconds
};

const stopAutoRefresh = () => {
    clearInterval(refreshInterval.value);
};

const toggleAutoRefresh = () => {
    autoRefresh.value = !autoRefresh.value;
    if (autoRefresh.value) {
        startAutoRefresh();
    } else {
        stopAutoRefresh();
    }
};

const formatTime = (date) => {
    return new Intl.DateTimeFormat('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }).format(date);
};

const getStatusColor = (status) => {
    switch(status) {
        case 'mengerjakan': return 'bg-indigo-100 text-indigo-700 border-indigo-200';
        case 'selesai': return 'bg-emerald-100 text-emerald-700 border-emerald-200';
        case 'belum_mulai': return 'bg-slate-100 text-slate-700 border-slate-200';
        case 'didiskualifikasi': return 'bg-rose-100 text-rose-700 border-rose-200';
        default: return 'bg-slate-100 text-slate-700 border-slate-200';
    }
};

// ─── Diskualifikasi Manual Modal ──────────────
const showDiskualModal = ref(false);
const diskualTarget = ref(null);
const isDiskualifying = ref(false);

const diskualifikasiManual = (peserta) => {
    diskualTarget.value = peserta;
    showDiskualModal.value = true;
};

const confirmDiskualifikasi = () => {
    isDiskualifying.value = true;
    // Endpoint diskualifikasi manual — kirim request ke backend
    router.post(route('admin.ujian.sesi.monitor', props.sesi.id), 
        { _method: 'patch', peserta_id: diskualTarget.value.id, action: 'diskualifikasi' },
        {
            onSuccess: () => {
                showDiskualModal.value = false;
                fetchLatestData();
            },
            onFinish: () => { isDiskualifying.value = false; }
        }
    );
};
</script>

<template>
    <Head title="Monitor Ujian" />
    
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto space-y-6">
            
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <Activity class="w-6 h-6 text-indigo-600" />
                        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Monitor Sesi</h1>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider"
                              :class="sesi.status === 'berlangsung' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700'">
                            {{ sesi.status }}
                        </span>
                    </div>
                    <p class="text-slate-500 font-medium ml-9">{{ sesi.nama_sesi }} &mdash; {{ sesi.paket_ujian.mata_pelajaran?.nama }}</p>
                </div>

                <div class="flex items-center gap-4">
                    <div class="text-right hidden sm:block">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Pembaruan Terakhir</p>
                        <p class="text-sm font-medium text-slate-900">{{ formatTime(lastUpdated) }}</p>
                    </div>
                    <button 
                        @click="toggleAutoRefresh"
                        class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold transition-colors border"
                        :class="autoRefresh ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50'"
                    >
                        <RefreshCcw class="w-4 h-4" :class="{'animate-spin-slow': autoRefresh}" />
                        {{ autoRefresh ? 'Auto Refresh ON' : 'Auto Refresh OFF' }}
                    </button>
                    <button @click="fetchLatestData" class="p-2.5 bg-slate-900 text-white rounded-xl hover:bg-slate-800 transition-colors">
                        <RefreshCcw class="w-4 h-4" />
                    </button>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-500">
                        <Users class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-0.5">Total Peserta</p>
                        <p class="text-2xl font-black text-slate-900">{{ peserta.length }}</p>
                    </div>
                </div>
                
                <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600">
                        <Activity class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-0.5">Mengerjakan</p>
                        <p class="text-2xl font-black text-slate-900">{{ peserta.filter(p => p.status === 'mengerjakan').length }}</p>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600">
                        <CheckCircle2 class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-0.5">Selesai</p>
                        <p class="text-2xl font-black text-slate-900">{{ peserta.filter(p => p.status === 'selesai').length }}</p>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-3xl border border-rose-200 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 bg-rose-50 rounded-2xl flex items-center justify-center text-rose-600">
                        <AlertOctagon class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-xs font-bold text-rose-400 uppercase tracking-widest mb-0.5">Diskualifikasi</p>
                        <p class="text-2xl font-black text-rose-700">{{ peserta.filter(p => p.status === 'didiskualifikasi').length }}</p>
                    </div>
                </div>
            </div>

            <!-- Participant List -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider">
                                <th class="p-4 pl-6">Peserta</th>
                                <th class="p-4">Status</th>
                                <th class="p-4">Progress</th>
                                <th class="p-4">Pelanggaran</th>
                                <th class="p-4">IP / Browser</th>
                                <th class="p-4 pr-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="p in peserta" :key="p.id" class="hover:bg-slate-50/50 transition-colors">
                                <td class="p-4 pl-6">
                                    <p class="font-bold text-slate-900">{{ p.siswa?.nama }}</p>
                                    <p class="text-xs font-medium text-slate-500">{{ p.siswa?.nisn }}</p>
                                </td>
                                <td class="p-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold uppercase tracking-wider border"
                                          :class="getStatusColor(p.status)">
                                        {{ p.status.replace('_', ' ') }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center gap-3 w-48">
                                        <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden">
                                            <div class="h-full bg-indigo-500 rounded-full transition-all duration-500"
                                                 :style="`width: ${p.progress}%`"></div>
                                        </div>
                                        <span class="text-xs font-bold text-slate-600">{{ p.progress }}%</span>
                                    </div>
                                    <p class="text-[10px] font-semibold text-slate-400 mt-1 uppercase tracking-widest">
                                        {{ p.jawaban_tersimpan }} / {{ p.total_soal }} Terjawab
                                    </p>
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center gap-1.5" :class="p.jumlah_pelanggaran > 0 ? 'text-rose-600 font-bold' : 'text-slate-400'">
                                        <AlertOctagon class="w-4 h-4" />
                                        <span>{{ p.jumlah_pelanggaran }} / {{ sesi.max_pelanggaran }}</span>
                                    </div>
                                </td>
                                <td class="p-4 text-xs font-medium text-slate-500">
                                    <p>{{ p.ip_address || '-' }}</p>
                                    <p class="truncate w-32" :title="p.browser">{{ p.browser || '-' }}</p>
                                </td>
                                <td class="p-4 pr-6 text-right">
                                    <button 
                                        v-if="p.status === 'mengerjakan'"
                                        @click="diskualifikasiManual(p)"
                                        class="px-3 py-1.5 bg-rose-50 text-rose-600 hover:bg-rose-100 border border-rose-200 rounded-lg text-xs font-bold transition-colors"
                                    >
                                        Diskualifikasi
                                    </button>
                                    <span v-else class="text-slate-300 text-xs font-medium">N/A</span>
                                </td>
                            </tr>
                            
                            <tr v-if="peserta.length === 0">
                                <td colspan="6" class="p-8 text-center text-slate-500 font-medium">
                                    Belum ada peserta di sesi ini.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal: Konfirmasi Diskualifikasi Peserta -->
        <div v-if="showDiskualModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-sm">
            <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden text-center">
                <div class="p-8">
                    <div class="w-16 h-16 bg-rose-100 text-rose-600 rounded-2xl flex items-center justify-center mx-auto mb-5 animate-bounce">
                        <AlertOctagon class="w-9 h-9" />
                    </div>
                    <h3 class="text-xl font-black text-rose-700 mb-2">Diskualifikasi Peserta?</h3>
                    <div class="bg-rose-50 border border-rose-100 rounded-2xl px-4 py-3 mb-4">
                        <p class="font-black text-slate-900">{{ diskualTarget?.siswa?.nama }}</p>
                        <p class="text-rose-600 text-xs font-bold uppercase tracking-wider">{{ diskualTarget?.siswa?.nisn }}</p>
                    </div>
                    <p class="text-slate-500 text-sm font-medium leading-relaxed mb-7">
                        Peserta akan <strong class="text-rose-600">didiskualifikasi secara paksa</strong>. Sesi ujian mereka akan langsung ditutup dan nilai akhir akan menjadi <strong>0</strong>. Tindakan ini tidak dapat dibatalkan.
                    </p>
                    <div class="flex gap-3">
                        <button 
                            @click="showDiskualModal = false" 
                            :disabled="isDiskualifying"
                            class="flex-1 py-3 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors"
                        >
                            Batal
                        </button>
                        <button 
                            @click="confirmDiskualifikasi" 
                            :disabled="isDiskualifying"
                            class="flex-1 py-3 text-sm font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition-colors flex items-center justify-center gap-2"
                        >
                            <span v-if="isDiskualifying" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
                            <AlertOctagon v-else class="w-4 h-4" />
                            {{ isDiskualifying ? 'Memproses...' : 'Ya, Diskualifikasi' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-spin-slow {
    animation: spin 3s linear infinite;
}
</style>
