<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    Package, ArrowLeft, Plus, Search, Trash2, ListChecks, Check, X, AlertTriangle, Database
} from 'lucide-vue-next';

const props = defineProps({
    paket: Object,
    availableSoal: Array,
});

const searchSoal = ref('');
const showAddModal = ref(false);

const attachForm = useForm({
    soal_ids: [],
});

const selectedBankId = ref(null);
const isAddingBank = ref(false);

const availableBanks = computed(() => {
    const seen = new Map();
    props.availableSoal.forEach(s => {
        if (s.bank_soal && !seen.has(s.bank_soal.id)) {
            seen.set(s.bank_soal.id, s.bank_soal);
        }
    });
    return Array.from(seen.values());
});

const bankSoalCount = (bankId) => {
    return props.availableSoal.filter(s => s.bank_soal?.id === bankId).length;
};

const filteredAvailableSoal = computed(() => {
    if (!searchSoal.value) return props.availableSoal;
    return props.availableSoal.filter(s => 
        s.pertanyaan.toLowerCase().includes(searchSoal.value.toLowerCase()) || 
        s.bank_soal?.nama.toLowerCase().includes(searchSoal.value.toLowerCase())
    );
});

const toggleSoalSelection = (id) => {
    const index = attachForm.soal_ids.indexOf(id);
    if (index === -1) {
        attachForm.soal_ids.push(id);
    } else {
        attachForm.soal_ids.splice(index, 1);
    }
};

const submitAddSoal = () => {
    attachForm.post(route('admin.ujian.paket.tambah-soal', props.paket.hashid), {
        onSuccess: () => {
            showAddModal.value = false;
            attachForm.reset();
        }
    });
};

const submitAddBankSoal = () => {
    if (!selectedBankId.value) return;
    isAddingBank.value = true;
    router.post(route('admin.ujian.paket.tambah-soal', props.paket.hashid), {
        bank_soal_id: selectedBankId.value,
    }, {
        onSuccess: () => {
            showAddModal.value = false;
            selectedBankId.value = null;
        },
        onFinish: () => {
            isAddingBank.value = false;
        },
    });
};

// ─── Delete Soal from Paket Modal ──────────────
const showDeleteSoalModal = ref(false);
const deleteSoalTarget = ref(null);
const isDeletingSoal = ref(false);

const hapusSoal = (soal) => {
    deleteSoalTarget.value = soal;
    showDeleteSoalModal.value = true;
};

const confirmHapusSoal = () => {
    isDeletingSoal.value = true;
    router.delete(route('admin.ujian.paket.hapus-soal', { paket: props.paket.hashid, soal: deleteSoalTarget.value.hashid }), {
        onSuccess: () => { showDeleteSoalModal.value = false; },
        onFinish: () => { isDeletingSoal.value = false; }
    });
};
</script>

<template>
    <Head :title="`Paket: ${paket.nama}`" />
    
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <Link :href="route('admin.ujian.paket.index')" class="p-2 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">
                    <ArrowLeft class="w-5 h-5 text-slate-600" />
                </Link>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                        {{ paket.nama }} <span class="px-2.5 py-1 bg-indigo-100 text-indigo-700 text-xs rounded-lg uppercase">{{ paket.kode }}</span>
                    </h1>
                    <p class="text-slate-500 font-medium mt-1">{{ paket.mata_pelajaran?.nama }} &mdash; {{ paket.durasi_menit }} Menit</p>
                </div>
            </div>

            <!-- Content Area -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <ListChecks class="w-5 h-5 text-indigo-600" />
                        Daftar Soal Terpilih ({{ paket.soal.length }})
                    </h2>
                    
                    <button @click="showAddModal = true" class="px-4 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-sm font-bold rounded-xl flex items-center gap-2 transition-colors">
                        <Plus class="w-4 h-4" /> Tambah Soal dari Bank
                    </button>
                </div>

                <div class="p-6">
                    <div v-if="paket.soal.length === 0" class="text-center py-12">
                        <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4 text-slate-400">
                            <Package class="w-8 h-8" />
                        </div>
                        <h3 class="text-slate-900 font-bold mb-1">Paket Masih Kosong</h3>
                        <p class="text-slate-500 text-sm">Tambahkan soal dari bank soal mata pelajaran ini.</p>
                    </div>

                    <div v-else class="space-y-4">
                        <div v-for="(soal, index) in paket.soal" :key="soal.id" class="flex items-start gap-4 p-4 border border-slate-200 rounded-2xl hover:border-indigo-300 transition-colors group">
                            <div class="w-8 h-8 bg-indigo-50 text-indigo-700 font-black rounded-lg flex items-center justify-center shrink-0">
                                {{ index + 1 }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="px-2 py-0.5 bg-slate-100 text-slate-600 text-[10px] font-bold uppercase rounded tracking-wider">
                                        {{ soal.tipe.replace('_', ' ') }}
                                    </span>
                                    <span class="px-2 py-0.5 bg-slate-100 text-slate-600 text-[10px] font-bold uppercase rounded tracking-wider">
                                        Bobot: {{ soal.bobot }}
                                    </span>
                                    <span class="text-xs text-slate-400 font-medium">Dari: {{ soal.bank_soal?.nama }}</span>
                                </div>
                                <div class="prose prose-sm prose-slate max-w-none text-slate-800 font-medium line-clamp-2" v-html="soal.pertanyaan"></div>
                            </div>
                            <button @click="hapusSoal(soal)" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-colors opacity-0 group-hover:opacity-100">
                                <Trash2 class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Tambah Soal -->
        <div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white w-full max-w-4xl max-h-[90vh] flex flex-col rounded-3xl shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between p-6 border-b border-slate-100 shrink-0">
                    <div>
                        <h2 class="text-xl font-black text-slate-900">Pilih Soal dari Bank</h2>
                        <p class="text-sm text-slate-500 mt-1">Mata Pelajaran: {{ paket.mata_pelajaran?.nama }}</p>
                    </div>
                    <button @click="showAddModal = false" class="p-2 text-slate-400 hover:text-slate-600 bg-slate-50 rounded-xl transition-colors">
                        <X class="w-5 h-5" />
                    </button>
                </div>
                
                <div class="p-4 border-b border-slate-100 bg-indigo-50/50 shrink-0">
                    <label class="block text-xs font-bold text-slate-600 mb-2 flex items-center gap-1.5">
                        <Database class="w-3.5 h-3.5" /> Tambah Semua dari Bank Soal
                    </label>
                    <div class="flex items-end gap-3">
                        <div class="flex-1">
                            <select v-model="selectedBankId" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-indigo-600 focus:border-indigo-600">
                                <option :value="null">— Pilih Bank Soal —</option>
                                <option v-for="bank in availableBanks" :key="bank.id" :value="bank.id">
                                    {{ bank.nama }} ({{ bankSoalCount(bank.id) }} soal)
                                </option>
                            </select>
                        </div>
                        <button @click="submitAddBankSoal" :disabled="!selectedBankId || isAddingBank"
                            class="px-5 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:hover:bg-indigo-600 rounded-xl transition-colors flex items-center gap-2 shrink-0">
                            <Plus class="w-4 h-4" /> Tambah Semua
                        </button>
                    </div>
                </div>

                <div class="p-4 border-b border-slate-100 bg-slate-50 shrink-0">
                    <div class="relative">
                        <input type="text" v-model="searchSoal" placeholder="Cari pertanyaan atau nama bank soal..." class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600 text-sm">
                        <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5" />
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto p-6 space-y-3">
                    <div v-if="filteredAvailableSoal.length === 0" class="text-center py-8 text-slate-500">
                        Tidak ada soal yang tersedia untuk ditambahkan.
                    </div>
                    
                    <label 
                        v-for="soal in filteredAvailableSoal" 
                        :key="soal.id" 
                        class="flex items-start gap-4 p-4 border-2 rounded-2xl cursor-pointer transition-colors"
                        :class="attachForm.soal_ids.includes(soal.id) ? 'border-indigo-600 bg-indigo-50/30' : 'border-slate-200 hover:border-indigo-300'"
                    >
                        <div class="mt-1">
                            <input type="checkbox" :checked="attachForm.soal_ids.includes(soal.id)" @change="toggleSoalSelection(soal.id)" class="w-5 h-5 text-indigo-600 border-slate-300 rounded focus:ring-indigo-600 cursor-pointer">
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs font-bold text-indigo-700 bg-indigo-100 px-2 py-0.5 rounded">{{ soal.tipe.replace('_', ' ') }}</span>
                                <span class="text-xs text-slate-500 font-medium">Bank: {{ soal.bank_soal?.nama }}</span>
                            </div>
                            <div class="prose prose-sm prose-slate max-w-none text-slate-800 line-clamp-3" v-html="soal.pertanyaan"></div>
                        </div>
                    </label>
                </div>

                <div class="p-6 border-t border-slate-100 shrink-0 flex items-center justify-between bg-white">
                    <div class="text-sm font-bold text-slate-600">
                        <span class="text-indigo-600">{{ attachForm.soal_ids.length }}</span> soal dipilih
                    </div>
                    <div class="flex gap-3">
                        <button @click="showAddModal = false" class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                            Batal
                        </button>
                        <button 
                            @click="submitAddSoal" 
                            :disabled="attachForm.soal_ids.length === 0 || attachForm.processing"
                            class="px-5 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:hover:bg-indigo-600 rounded-xl transition-colors flex items-center gap-2"
                        >
                            <Check class="w-4 h-4" /> Tambahkan ke Paket
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal: Konfirmasi Hapus Soal dari Paket -->
        <div v-if="showDeleteSoalModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white w-full max-w-sm rounded-3xl shadow-2xl overflow-hidden text-center">
                <div class="p-8">
                    <div class="w-14 h-14 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center mx-auto mb-5">
                        <Trash2 class="w-7 h-7" />
                    </div>
                    <h3 class="text-xl font-black text-slate-900 mb-3">Keluarkan Soal?</h3>
                    <p class="text-slate-500 text-sm font-medium leading-relaxed mb-7">
                        Soal ini akan dikeluarkan dari paket <span class="font-bold text-slate-900">{{ paket.nama }}</span>. Soal masih tersimpan di bank soal dan tidak akan terhapus permanen.
                    </p>
                    <div class="flex gap-3">
                        <button 
                            @click="showDeleteSoalModal = false" 
                            :disabled="isDeletingSoal"
                            class="flex-1 py-3 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors"
                        >
                            Batal
                        </button>
                        <button 
                            @click="confirmHapusSoal" 
                            :disabled="isDeletingSoal"
                            class="flex-1 py-3 text-sm font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition-colors flex items-center justify-center gap-2"
                        >
                            <span v-if="isDeletingSoal" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
                            <Trash2 v-else class="w-4 h-4" />
                            {{ isDeletingSoal ? 'Menghapus...' : 'Ya, Keluarkan' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
