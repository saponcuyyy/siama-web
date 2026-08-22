<script setup>
import { ref, computed, watch } from 'vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    Calendar,
    CalendarDays,
    Plus,
    Pencil,
    Trash2,
    X,
    Check,
    BookOpen,
    Users,
    School,
    Clock,
    Filter,
    AlertCircle,
    Zap,
    Settings,
    Loader2,
    Printer,
} from 'lucide-vue-next';

const props = defineProps({
    jadwalGrouped: [Array, Object],
    rombels: Array,
    guruList: Array,
    mapelList: Array,
    tahunAjaranList: Array,
    filters: Object,
    selectedRombelId: [String, Number],
    hariList: Array,
    semuaHariList: {
        type: Array,
        default: () => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'],
    },
    maxJam: Number,
});

const authUser = usePage().props.auth?.user;
const canManage = computed(() => {
    if (!authUser) return false;
    if (authUser.roles?.includes('super_admin')) return true;
    return authUser.permissions?.includes('jadwal.manage');
});

const filterRombel = ref(props.filters.rombel_id || '');
const filterTahun = ref(props.filters.tahun_ajaran_id || '');

const showModal = ref(false);
const editTarget = ref(null);

const form = useForm({
    rombel_id: '',
    mata_pelajaran_id: '',
    guru_id: '',
    tahun_ajaran_id: '',
    hari: '',
    jam_ke: '',
});

const handleFilter = () => {
    router.get(route('admin.jadwal.index'), {
        rombel_id: filterRombel.value,
        tahun_ajaran_id: filterTahun.value,
    }, { preserveState: true, replace: true });
};

const openCreate = (hari, jam) => {
    editTarget.value = null;
    form.reset();
    const aktifTa = props.tahunAjaranList.find(t => t.is_active);
    if (aktifTa) form.tahun_ajaran_id = aktifTa.id;
    if (filterRombel.value) form.rombel_id = filterRombel.value;
    if (hari) form.hari = hari;
    if (jam) form.jam_ke = jam;
    showModal.value = true;
};

// Rombel pada form tambah hanya milik tahun ajaran yang dipilih di form
const formRombels = computed(() =>
    props.rombels.filter(r => !form.tahun_ajaran_id || r.tahun_ajaran_id == form.tahun_ajaran_id)
);

const openEdit = (entry) => {
    editTarget.value = entry;
    form.rombel_id = entry.rombel_id;
    form.mata_pelajaran_id = entry.mata_pelajaran_id;
    form.guru_id = entry.guru_id;
    form.tahun_ajaran_id = entry.tahun_ajaran_id;
    form.hari = entry.hari;
    form.jam_ke = entry.jam_ke;
    showModal.value = true;
};

const submitForm = () => {
    if (editTarget.value) {
        form.put(route('admin.jadwal.update', editTarget.value.hashid), {
            onSuccess: () => { showModal.value = false; form.reset(); },
        });
    } else {
        form.post(route('admin.jadwal.store'), {
            onSuccess: () => { showModal.value = false; form.reset(); },
        });
    }
};

const hapus = (entry) => {
    deleteTarget.value = entry;
    showDeleteModal.value = true;
};

const showDeleteModal = ref(false);
const deleteTarget = ref(null);
const isDeleting = ref(false);

const confirmHapus = () => {
    if (!deleteTarget.value) return;
    isDeleting.value = true;
    router.delete(route('admin.jadwal.destroy', deleteTarget.value.hashid), {
        onSuccess: () => {
            showDeleteModal.value = false;
            deleteTarget.value = null;
        },
        onFinish: () => {
            isDeleting.value = false;
        },
    });
};

const conflictWarning = computed(() => {
    if (!form.guru_id || !form.hari || !form.jam_ke) {
        return null;
    }

    const currentHashid = editTarget.value?.hashid;
    const selectedGuru = props.guruList.find(g => g.id == form.guru_id);
    const targetJam = Number(form.jam_ke);
    const targetTaId = form.tahun_ajaran_id || (props.filters.tahun_ajaran_id || props.tahunAjaranList.find(t => t.is_active)?.id);

    let allEntries = [];
    if (Array.isArray(props.jadwalGrouped)) {
        props.jadwalGrouped.forEach(group => {
            if (group.grid) {
                Object.values(group.grid).forEach(daySlots => {
                    Object.values(daySlots).forEach(entry => {
                        if (entry) allEntries.push(entry);
                    });
                });
            }
        });
    } else if (props.jadwalGrouped?.grid) {
        Object.values(props.jadwalGrouped.grid).forEach(daySlots => {
            Object.values(daySlots).forEach(entry => {
                if (entry) allEntries.push(entry);
            });
        });
    }

    const guruConflict = allEntries.find(item =>
        item.guru_id == form.guru_id &&
        item.hari === form.hari &&
        Number(item.jam_ke) === targetJam &&
        (!targetTaId || item.tahun_ajaran_id == targetTaId) &&
        item.hashid !== currentHashid
    );

    if (guruConflict) {
        return {
            type: 'guru',
            title: 'Bentrok Jadwal Guru!',
            message: `Guru ${selectedGuru?.nama || 'Guru'} sudah ada jadwal mengajar di kelas ${guruConflict.rombel?.nama || 'lain'} pada hari ${form.hari} jam ke-${form.jam_ke}.`
        };
    }

    const targetRombelId = form.rombel_id || props.selectedRombelId;
    if (targetRombelId) {
        const rombelConflict = allEntries.find(item =>
            item.rombel_id == targetRombelId &&
            item.hari === form.hari &&
            Number(item.jam_ke) === targetJam &&
            (!targetTaId || item.tahun_ajaran_id == targetTaId) &&
            item.hashid !== currentHashid
        );

        if (rombelConflict) {
            return {
                type: 'rombel',
                title: 'Jam Pelajaran Terisi!',
                message: `Kelas ini sudah terisi jadwal ${rombelConflict.mata_pelajaran?.nama || ''} (${rombelConflict.guru?.nama || ''}) pada hari ${form.hari} jam ke-${form.jam_ke}.`
            };
        }
    }

    return null;
});

const isGroupMode = computed(() => !filterRombel.value);

// ─── Hari Aktif Modal ──────────────────────────
const showHariAktifModal = ref(false);
const hariAktifForm = useForm({
    hari: [...(props.hariList || [])],
});

const openHariAktifModal = () => {
    hariAktifForm.hari = [...(props.hariList || [])];
    showHariAktifModal.value = true;
};

const toggleHariAktif = (hari) => {
    const idx = hariAktifForm.hari.indexOf(hari);
    if (idx === -1) {
        hariAktifForm.hari.push(hari);
    } else {
        if (hariAktifForm.hari.length > 1) {
            hariAktifForm.hari.splice(idx, 1);
        }
    }
};

const setPresetHari = (preset) => {
    if (preset === '5-hari') {
        hariAktifForm.hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
    } else if (preset === '6-hari') {
        hariAktifForm.hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    } else if (preset === '7-hari') {
        hariAktifForm.hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
    }
};

const submitHariAktif = () => {
    hariAktifForm.post(route('admin.jadwal.update-hari-aktif'), {
        onSuccess: () => {
            showHariAktifModal.value = false;
        },
    });
};

const getDayTextColor = (hari) => {
    const map = {
        Senin: 'text-blue-700',
        Selasa: 'text-emerald-700',
        Rabu: 'text-violet-700',
        Kamis: 'text-rose-700',
        Jumat: 'text-amber-700',
        Sabtu: 'text-cyan-700',
        Minggu: 'text-pink-700',
    };
    return map[hari] || 'text-slate-700';
};

const getDayBgColor = (hari) => {
    const map = {
        Senin: 'bg-blue-500',
        Selasa: 'bg-emerald-500',
        Rabu: 'bg-violet-500',
        Kamis: 'bg-rose-500',
        Jumat: 'bg-amber-500',
        Sabtu: 'bg-cyan-500',
        Minggu: 'bg-pink-500',
    };
    return map[hari] || 'bg-slate-500';
};

// ─── Generate Modal ──────────────────────────
const showGenerateModal = ref(false);
const isGenerating = ref(false);

const generateForm = useForm({
    tahun_ajaran_id: '',
    rombel_ids: [],
    max_jam: 8,
    max_jam_tingkat: {
        X: 8,
        XI: 8,
        XII: 8,
    },
});

const openGenerate = () => {
    const aktifTa = props.tahunAjaranList.find(t => t.is_active);
    generateForm.tahun_ajaran_id = aktifTa?.id || '';
    generateForm.rombel_ids = [];
    generateForm.max_jam = 8;
    generateForm.max_jam_tingkat = { X: 8, XI: 8, XII: 8 };
    showGenerateModal.value = true;
};

// Rombel yang tersedia di modal generate hanya milik tahun ajaran terpilih
const generateRombels = computed(() =>
    props.rombels.filter(r => !generateForm.tahun_ajaran_id || r.tahun_ajaran_id == generateForm.tahun_ajaran_id)
);

// Jika tahun ajaran diganti, buang pilihan rombel yang tidak termasuk tahun ajaran baru
watch(() => generateForm.tahun_ajaran_id, () => {
    const valid = new Set(generateRombels.value.map(r => r.id));
    generateForm.rombel_ids = generateForm.rombel_ids.filter(id => valid.has(id));
});

const toggleRombel = (id) => {
    const idx = generateForm.rombel_ids.indexOf(id);
    if (idx === -1) {
        generateForm.rombel_ids.push(id);
    } else {
        generateForm.rombel_ids.splice(idx, 1);
    }
};

const selectAllRombel = () => {
    generateForm.rombel_ids = generateRombels.value.map(r => r.id);
};

const submitGenerate = () => {
    isGenerating.value = true;
    generateForm.post(route('admin.jadwal.generate'), {
        onSuccess: () => {
            showGenerateModal.value = false;
            isGenerating.value = false;
        },
        onError: () => {
            isGenerating.value = false;
        },
    });
};

const exportPdf = () => {
    const url = route('admin.jadwal.export-pdf', {
        rombel_id: filterRombel.value || '',
        tahun_ajaran_id: filterTahun.value || '',
    });
    window.open(url, '_blank');
};

// Lock body scroll when modal is open
watch(showGenerateModal, (v) => {
    document.body.style.overflow = v ? 'hidden' : '';
});
watch(showHariAktifModal, (v) => {
    document.body.style.overflow = v ? 'hidden' : '';
});
</script>

<template>
    <Head title="Jadwal Pelajaran" />

    <AuthenticatedLayout>
        <div class="max-w-full mx-auto space-y-6">

            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between gap-4 items-start sm:items-center">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                        <Calendar class="w-7 h-7 text-indigo-600" />
                        Jadwal Pelajaran
                    </h1>
                    <p class="text-slate-500 font-medium mt-1">
                        Kelola jadwal mata pelajaran untuk semua rombongan belajar.
                    </p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <button
                        @click="exportPdf"
                        class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold rounded-xl flex items-center gap-2 transition-colors shadow-lg shadow-rose-200"
                        title="Cetak PDF Jadwal Pelajaran"
                    >
                        <Printer class="w-5 h-5 text-rose-100" />
                        <span>Cetak PDF</span>
                    </button>
                    <button
                        v-if="canManage"
                        @click="openHariAktifModal"
                        class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white text-sm font-bold rounded-xl flex items-center gap-2 transition-colors shadow-lg shadow-slate-200"
                    >
                        <CalendarDays class="w-5 h-5 text-indigo-400" />
                        <span>Hari Aktif ({{ hariList.length }} Hari)</span>
                    </button>
                    <button
                        v-if="canManage"
                        @click="openGenerate"
                        class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl flex items-center gap-2 transition-colors shadow-lg shadow-emerald-200"
                    >
                        <Zap class="w-5 h-5" /> Generate Jadwal
                    </button>
                    <button
                        v-if="canManage"
                        @click="openCreate(null, null)"
                        class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl flex items-center gap-2 transition-colors shadow-lg shadow-indigo-200"
                    >
                        <Plus class="w-5 h-5" /> Tambah Jadwal
                    </button>
                </div>
            </div>

            <!-- Flash Messages -->
            <div
                v-if="$page.props.flash?.success"
                class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 font-medium rounded-xl text-sm flex items-center gap-2"
            >
                <Check class="w-4 h-4 text-emerald-600" /> {{ $page.props.flash.success }}
            </div>
            <div
                v-if="$page.props.flash?.error"
                class="p-4 bg-rose-50 border border-rose-200 text-rose-800 font-medium rounded-xl text-sm flex items-center gap-2"
            >
                <AlertCircle class="w-4 h-4 text-rose-600" /> {{ $page.props.flash.error }}
            </div>

            <!-- Filters -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row gap-4 items-center">
                <Filter class="w-5 h-5 text-slate-400 hidden sm:block shrink-0" />
                <div class="relative w-full sm:w-72">
                    <School class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
                    <select
                        v-model="filterRombel"
                        @change="handleFilter"
                        class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600 text-sm font-medium appearance-none"
                    >
                        <option value="">Semua Rombel (Tampil per Kelas)</option>
                        <option v-for="r in rombels" :key="r.id" :value="r.id">
                            {{ r.nama }}
                        </option>
                    </select>
                </div>
                <select
                    v-model="filterTahun"
                    @change="handleFilter"
                    class="w-full sm:w-56 px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600 text-sm font-medium"
                >
                    <option value="">Semua Tahun Ajaran</option>
                    <option v-for="t in tahunAjaranList" :key="t.id" :value="t.id">
                        {{ t.nama }} {{ t.is_active ? '(Aktif)' : '' }}
                    </option>
                </select>
            </div>

            <!-- Empty State -->
            <div
                v-if="!jadwalGrouped || (Array.isArray(jadwalGrouped) && jadwalGrouped.length === 0) || (!Array.isArray(jadwalGrouped) && !jadwalGrouped.grid)"
                class="bg-white rounded-3xl border border-slate-200 p-12 text-center"
            >
                <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4 text-slate-400">
                    <Calendar class="w-8 h-8" />
                </div>
                <h3 class="text-slate-900 font-bold text-lg mb-1">Belum Ada Jadwal</h3>
                <p class="text-slate-500 text-sm">Tambahkan jadwal mata pelajaran untuk rombongan belajar.</p>
            </div>

            <!-- Schedule Grid -->
            <template v-else>
                <!-- Single Rombel View -->
                <div v-if="!isGroupMode && jadwalGrouped && jadwalGrouped.grid" class="space-y-4">
                    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="bg-gradient-to-r from-indigo-600 to-purple-700 px-6 py-4">
                            <h2 class="text-lg font-black text-white flex items-center gap-2">
                                <School class="w-5 h-5" />
                                {{ jadwalGrouped.rombel?.nama || 'Rombel' }}
                            </h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[640px]">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-200">
                                        <th class="text-left px-4 py-3 text-xs font-black text-slate-500 uppercase tracking-wider w-28">
                                            Hari
                                        </th>
                                        <th
                                            v-for="j in maxJam"
                                            :key="j"
                                            class="text-center px-3 py-3 text-xs font-black text-slate-500 uppercase tracking-wider border-l border-slate-100"
                                        >
                                            <div class="flex items-center justify-center gap-1">
                                                <Clock class="w-3 h-3" />
                                                {{ j }}
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="hari in hariList" :key="hari" class="border-b border-slate-100 hover:bg-slate-50/50 transition-colors">
                                        <td
                                            class="px-4 py-3 font-black text-sm"
                                            :class="getDayTextColor(hari)"
                                        >
                                            <div class="flex items-center gap-2">
                                                <span
                                                    class="w-2 h-2 rounded-full shrink-0"
                                                    :class="getDayBgColor(hari)"
                                                />
                                                {{ hari }}
                                            </div>
                                        </td>
                                        <td v-for="j in maxJam" :key="j" class="border-l border-slate-100 p-1.5">
                                            <div
                                                v-if="jadwalGrouped.grid[hari] && jadwalGrouped.grid[hari][j]"
                                                class="relative group/cell"
                                            >
                                                <div
                                                    class="bg-gradient-to-br from-indigo-50 to-indigo-100/50 border border-indigo-200 rounded-xl px-3 py-2.5 cursor-pointer hover:shadow-md hover:border-indigo-300 transition-all min-h-[72px]"
                                                >
                                                    <div class="text-sm font-bold text-slate-800 leading-tight">
                                                        {{ jadwalGrouped.grid[hari][j].mata_pelajaran?.nama }}
                                                    </div>
                                                    <div class="text-xs font-medium text-slate-500 mt-1 leading-tight">
                                                        {{ jadwalGrouped.grid[hari][j].guru?.nama }}
                                                    </div>
                                                    <div v-if="canManage" class="absolute top-1 right-1 flex gap-0.5 opacity-0 group-hover/cell:opacity-100 transition-opacity">
                                                        <button
                                                            @click="openEdit(jadwalGrouped.grid[hari][j])"
                                                            class="p-1 text-indigo-500 hover:text-indigo-700 hover:bg-indigo-100 rounded-lg transition-colors"
                                                        >
                                                            <Pencil class="w-3.5 h-3.5" />
                                                        </button>
                                                        <button
                                                            @click="hapus(jadwalGrouped.grid[hari][j])"
                                                            class="p-1 text-rose-500 hover:text-rose-700 hover:bg-rose-100 rounded-lg transition-colors"
                                                        >
                                                            <Trash2 class="w-3.5 h-3.5" />
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div v-else>
                                                <button
                                                    v-if="canManage"
                                                    @click="openCreate(hari, j)"
                                                    class="w-full min-h-[72px] border-2 border-dashed border-slate-200 rounded-xl flex items-center justify-center text-slate-300 hover:text-indigo-500 hover:border-indigo-300 hover:bg-indigo-50/50 transition-all"
                                                >
                                                    <Plus class="w-5 h-5" />
                                                </button>
                                                <div v-else class="w-full min-h-[72px]"></div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- All Rombel View -->
                <div v-else-if="isGroupMode && Array.isArray(jadwalGrouped)" class="space-y-8">
                    <div
                        v-for="group in jadwalGrouped"
                        :key="group.rombel?.id"
                        class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden"
                    >
                        <div class="bg-gradient-to-r from-indigo-600 to-purple-700 px-6 py-4">
                            <h2 class="text-lg font-black text-white flex items-center gap-2">
                                <School class="w-5 h-5" />
                                {{ group.rombel?.nama || 'Rombel' }}
                            </h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[640px]">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-200">
                                        <th class="text-left px-4 py-3 text-xs font-black text-slate-500 uppercase tracking-wider w-28">Hari</th>
                                        <th
                                            v-for="j in group.max_jam"
                                            :key="j"
                                            class="text-center px-3 py-3 text-xs font-black text-slate-500 uppercase tracking-wider border-l border-slate-100"
                                        >
                                            <div class="flex items-center justify-center gap-1">
                                                <Clock class="w-3 h-3" />
                                                {{ j }}
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="hari in hariList" :key="hari" class="border-b border-slate-100 hover:bg-slate-50/50 transition-colors">
                                        <td class="px-4 py-3 font-black text-sm"
                                            :class="getDayTextColor(hari)"
                                        >
                                            <div class="flex items-center gap-2">
                                                <span class="w-2 h-2 rounded-full shrink-0"
                                                    :class="getDayBgColor(hari)"
                                                />
                                                {{ hari }}
                                            </div>
                                        </td>
                                        <td v-for="j in group.max_jam" :key="j" class="border-l border-slate-100 p-1.5">
                                            <div v-if="group.grid[hari] && group.grid[hari][j]" class="relative group/cell">
                                                <div class="bg-gradient-to-br from-indigo-50 to-indigo-100/50 border border-indigo-200 rounded-xl px-3 py-2.5 cursor-pointer hover:shadow-md hover:border-indigo-300 transition-all min-h-[72px]">
                                                    <div class="text-sm font-bold text-slate-800 leading-tight">
                                                        {{ group.grid[hari][j].mata_pelajaran?.nama }}
                                                    </div>
                                                    <div class="text-xs font-medium text-slate-500 mt-1 leading-tight">
                                                        {{ group.grid[hari][j].guru?.nama }}
                                                    </div>
                                                    <div v-if="canManage" class="absolute top-1 right-1 flex gap-0.5 opacity-0 group-hover/cell:opacity-100 transition-opacity">
                                                        <button @click="openEdit(group.grid[hari][j])" class="p-1 text-indigo-500 hover:text-indigo-700 hover:bg-indigo-100 rounded-lg transition-colors">
                                                            <Pencil class="w-3.5 h-3.5" />
                                                        </button>
                                                        <button @click="hapus(group.grid[hari][j])" class="p-1 text-rose-500 hover:text-rose-700 hover:bg-rose-100 rounded-lg transition-colors">
                                                            <Trash2 class="w-3.5 h-3.5" />
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div v-else>
                                                <button v-if="canManage" @click="openCreate(hari, j)" class="w-full min-h-[72px] border-2 border-dashed border-slate-200 rounded-xl flex items-center justify-center text-slate-300 hover:text-indigo-500 hover:border-indigo-300 hover:bg-indigo-50/50 transition-all">
                                                    <Plus class="w-5 h-5" />
                                                </button>
                                                <div v-else class="w-full min-h-[72px]"></div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Legend -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                <h4 class="text-sm font-bold text-slate-700 mb-3 flex items-center gap-2">
                    <BookOpen class="w-4 h-4 text-indigo-500" />
                    Keterangan
                </h4>
                <div class="flex flex-wrap gap-6 text-sm text-slate-600">
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded bg-indigo-100 border border-indigo-200" />
                        <span>Terisi mata pelajaran</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded border-2 border-dashed border-slate-300" />
                        <span>Kosong / belum terisi</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <BookOpen class="w-4 h-4 text-slate-400" />
                        <span>Mata Pelajaran</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <Users class="w-4 h-4 text-slate-400" />
                        <span>Guru Pengampu</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Generate -->
        <Transition name="modal">
            <div
                v-if="showGenerateModal"
                class="fixed inset-0 z-50 overflow-y-auto"
                @click.self="showGenerateModal = false"
            >
                <div class="flex min-h-screen items-start justify-center px-4 pt-10 pb-20 sm:pt-16 sm:pb-24">
                    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showGenerateModal = false" />

                    <div class="modal-card relative bg-white w-full max-w-2xl rounded-3xl shadow-2xl overflow-hidden">
                        <!-- Header (fixed/sticky) -->
                        <div class="relative bg-gradient-to-r from-emerald-600 to-teal-700 px-6 py-5">
                            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_rgba(255,255,255,0.1)_0%,_transparent_60%)]" />
                            <div class="absolute -top-16 -right-16 w-48 h-48 rounded-full bg-white/5 blur-2xl" />
                            <div class="relative flex items-center gap-4">
                                <div class="w-12 h-12 bg-white/15 backdrop-blur-sm rounded-2xl flex items-center justify-center ring-1 ring-white/20 shrink-0">
                                    <Zap class="w-6 h-6 text-white" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h2 class="text-lg font-black text-white tracking-tight">Generate Jadwal Otomatis</h2>
                                    <p class="text-sm text-emerald-100 font-medium truncate">Susun jadwal pelajaran untuk semua rombel dengan algoritma cerdas</p>
                                </div>
                                <button type="button" @click="showGenerateModal = false"
                                    class="p-2 text-white/60 hover:text-white bg-white/10 hover:bg-white/20 rounded-xl transition-all shrink-0"
                                >
                                    <X class="w-5 h-5" />
                                </button>
                            </div>
                        </div>

                        <form @submit.prevent="submitGenerate">
                            <!-- Scrollable Body -->
                            <div class="form-scroll overflow-y-auto max-h-[calc(100vh-18rem)]">
                                <div class="p-6 space-y-5">

                                    <!-- Row: Tahun Ajaran + Max Jam -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-bold text-slate-700 mb-1.5">
                                                <Calendar class="w-3.5 h-3.5 inline mr-1.5 text-slate-400" />
                                                Tahun Ajaran
                                            </label>
                                            <select v-model="generateForm.tahun_ajaran_id" required
                                                class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 font-medium text-sm transition-shadow"
                                            >
                                                <option value="">Pilih tahun ajaran</option>
                                                <option v-for="t in tahunAjaranList" :key="t.id" :value="t.id">
                                                    {{ t.nama }} {{ t.is_active ? '(Aktif)' : '' }}
                                                </option>
                                            </select>
                                            <p v-if="generateForm.errors.tahun_ajaran_id" class="text-rose-600 text-xs font-bold mt-1">{{ generateForm.errors.tahun_ajaran_id }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-bold text-slate-700 mb-1.5">
                                                <Clock class="w-3.5 h-3.5 inline mr-1.5 text-slate-400" />
                                                Maks Jam Per Hari (Default)
                                            </label>
                                            <select v-model="generateForm.max_jam" required
                                                class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 font-medium text-sm transition-shadow"
                                            >
                                                <option v-for="j in [6,7,8,9,10,11,12]" :key="j" :value="j">{{ j }} Jam Pelajaran</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Max Jam Per Tingkat Settings -->
                                    <div class="bg-slate-50/80 border border-slate-200 rounded-2xl p-4 space-y-2">
                                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                            Maksimal Jam per Hari per Tingkat Kelas
                                        </label>
                                        <div class="grid grid-cols-3 gap-3">
                                            <div class="bg-white border border-slate-200 rounded-xl p-2.5 text-center shadow-xs">
                                                <span class="text-[11px] font-black text-purple-700 uppercase tracking-wider block mb-1">Tingkat X</span>
                                                <input type="number" v-model.number="generateForm.max_jam_tingkat.X" min="1" max="15"
                                                    class="w-full px-2 py-1 bg-slate-50 border border-slate-200 rounded-lg font-extrabold text-center text-sm focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500" />
                                            </div>
                                            <div class="bg-white border border-slate-200 rounded-xl p-2.5 text-center shadow-xs">
                                                <span class="text-[11px] font-black text-sky-700 uppercase tracking-wider block mb-1">Tingkat XI</span>
                                                <input type="number" v-model.number="generateForm.max_jam_tingkat.XI" min="1" max="15"
                                                    class="w-full px-2 py-1 bg-slate-50 border border-slate-200 rounded-lg font-extrabold text-center text-sm focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500" />
                                            </div>
                                            <div class="bg-white border border-slate-200 rounded-xl p-2.5 text-center shadow-xs">
                                                <span class="text-[11px] font-black text-emerald-700 uppercase tracking-wider block mb-1">Tingkat XII</span>
                                                <input type="number" v-model.number="generateForm.max_jam_tingkat.XII" min="1" max="15"
                                                    class="w-full px-2 py-1 bg-slate-50 border border-slate-200 rounded-lg font-extrabold text-center text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />
                                            </div>
                                        </div>
                                        <p class="text-[11px] text-slate-500 font-medium pt-1">💡 Algoritma akan menyusun mapel dalam 2 jam berurutan dan memisah jam ganjil (1 jam) ke hari yang berbeda.</p>
                                    </div>

                                    <!-- Pilih Rombel -->
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <label class="text-sm font-bold text-slate-700 flex items-center gap-1.5">
                                                <School class="w-3.5 h-3.5 text-slate-400" />
                                                Pilih Rombel
                                                <span v-if="generateForm.rombel_ids.length > 0"
                                                    class="ml-1.5 px-1.5 py-0.5 text-[10px] font-black text-white bg-emerald-600 rounded-md"
                                                >
                                                    {{ generateForm.rombel_ids.length }} terpilih
                                                </span>
                                            </label>
                                            <div class="flex gap-2">
                                                <button type="button" @click="selectAllRombel"
                                                    class="text-xs font-bold text-emerald-600 hover:text-emerald-700 bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded-lg transition-colors"
                                                >
                                                    Pilih Semua
                                                </button>
                                                <button type="button" @click="generateForm.rombel_ids = []"
                                                    v-if="generateForm.rombel_ids.length > 0"
                                                    class="text-xs font-bold text-slate-500 hover:text-slate-600 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-lg transition-colors"
                                                >
                                                    Reset
                                                </button>
                                            </div>
                                        </div>
                                        <div class="border border-slate-200 rounded-xl divide-y divide-slate-100 bg-white max-h-56 overflow-y-auto">
                                            <label
                                                v-for="r in generateRombels"
                                                :key="r.id"
                                                class="flex items-center gap-3 px-4 py-2.5 cursor-pointer transition-colors hover:bg-slate-50 group"
                                            >
                                                <div
                                                    class="w-5 h-5 rounded-md border-2 flex items-center justify-center transition-all shrink-0"
                                                    :class="generateForm.rombel_ids.includes(r.id)
                                                        ? 'bg-emerald-600 border-emerald-600 shadow-sm shadow-emerald-200'
                                                        : 'border-slate-300 group-hover:border-slate-400'"
                                                >
                                                    <Check v-if="generateForm.rombel_ids.includes(r.id)" class="w-3.5 h-3.5 text-white" />
                                                </div>
                                                <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center shrink-0 group-hover:bg-slate-200 transition-colors">
                                                    <School class="w-3.5 h-3.5 text-slate-500" />
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <span class="text-sm font-semibold text-slate-800">{{ r.nama }}</span>
                                                </div>
                                                <span class="text-xs font-bold px-2 py-0.5 rounded-md"
                                                    :class="{
                                                        'bg-purple-100 text-purple-700': r.tingkat === 'X',
                                                        'bg-sky-100 text-sky-700': r.tingkat === 'XI',
                                                        'bg-emerald-100 text-emerald-700': r.tingkat === 'XII',
                                                    }"
                                                >{{ r.tingkat }}</span>
                                                <input type="checkbox" :value="r.id" v-model="generateForm.rombel_ids" class="sr-only" />
                                            </label>
                                            <div v-if="generateRombels.length === 0" class="p-6 text-center text-slate-400 text-sm">
                                                <School class="w-8 h-8 mx-auto mb-2 text-slate-300" />
                                                <p>Tidak ada rombel pada tahun ajaran ini.</p>
                                                <p v-if="generateForm.tahun_ajaran_id" class="text-xs mt-1">Pilih tahun ajaran lain yang memiliki rombel.</p>
                                            </div>
                                        </div>
                                        <p v-if="generateForm.errors.rombel_ids" class="text-rose-600 text-xs font-bold mt-1.5">{{ generateForm.errors.rombel_ids }}</p>
                                    </div>

                                    <!-- Summary Stats -->
                                    <div v-if="generateForm.tahun_ajaran_id && generateForm.rombel_ids.length > 0"
                                        class="bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-200 rounded-xl p-4 transition-all duration-300"
                                    >
                                        <h4 class="text-xs font-bold text-emerald-800 uppercase tracking-wider mb-3">Ringkasan Generate</h4>
                                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                            <div class="bg-white/80 backdrop-blur-sm rounded-lg px-3 py-2.5 text-center border border-emerald-100">
                                                <p class="text-lg font-black text-emerald-700">{{ generateForm.rombel_ids.length }}</p>
                                                <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Rombel</p>
                                            </div>
                                            <div class="bg-white/80 backdrop-blur-sm rounded-lg px-3 py-2.5 text-center border border-emerald-100">
                                                <p class="text-lg font-black text-emerald-700">{{ generateForm.max_jam * hariList.length }}</p>
                                                <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Slot/Rombel</p>
                                            </div>
                                            <div class="bg-white/80 backdrop-blur-sm rounded-lg px-3 py-2.5 text-center border border-emerald-100">
                                                <p class="text-lg font-black text-emerald-700">{{ (generateForm.rombel_ids.length * generateForm.max_jam * hariList.length).toLocaleString() }}</p>
                                                <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Total Slot</p>
                                            </div>
                                            <div class="bg-white/80 backdrop-blur-sm rounded-lg px-3 py-2.5 text-center border border-emerald-100">
                                                <p class="text-lg font-black text-emerald-700">{{ hariList.length }}</p>
                                                <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Hari Aktif</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Informasi -->
                                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
                                        <div class="flex items-start gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center shrink-0">
                                                <AlertCircle class="w-4 h-4 text-amber-600" />
                                            </div>
                                            <div class="text-xs text-slate-600 leading-relaxed flex-1">
                                                <p class="font-bold text-slate-800 mb-1.5">Yang perlu diperhatikan:</p>
                                                <ul class="space-y-1">
                                                    <li class="flex items-start gap-2">
                                                        <span class="w-1 h-1 rounded-full bg-amber-400 mt-1.5 shrink-0" />
                                                        <span>Jadwal yang sudah ada untuk rombel yang dipilih akan <span class="font-bold text-rose-600">dihapus</span> dan digenerate ulang.</span>
                                                    </li>
                                                    <li class="flex items-start gap-2">
                                                        <span class="w-1 h-1 rounded-full bg-amber-400 mt-1.5 shrink-0" />
                                                        <span>Mapel harus memiliki <span class="font-bold">guru pengampu</span> (di menu Guru) dan <span class="font-bold">jam per minggu</span> (di menu Mata Pelajaran).</span>
                                                    </li>
                                                    <li class="flex items-start gap-2">
                                                        <span class="w-1 h-1 rounded-full bg-amber-400 mt-1.5 shrink-0" />
                                                        <span>Bentrok jadwal guru akan <span class="font-bold text-emerald-600">dihindari otomatis</span> oleh algoritma.</span>
                                                    </li>
                                                    <li class="flex items-start gap-2">
                                                        <span class="w-1 h-1 rounded-full bg-amber-400 mt-1.5 shrink-0" />
                                                        <span>Setelah generate, Anda tetap bisa <span class="font-bold">mengedit manual</span> jadwal yang sudah dibuat.</span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer (fixed) -->
                            <div class="sticky bottom-0 px-6 py-4 bg-white border-t border-slate-100 flex items-center justify-between gap-3">
                                <p class="text-xs text-slate-400 hidden sm:block">
                                    <Zap class="w-3 h-3 inline mr-1 text-emerald-500" />
                                    Algoritma penjadwalan otomatis
                                </p>
                                <div class="flex gap-3 w-full sm:w-auto">
                                    <button type="button" @click="showGenerateModal = false"
                                        class="flex-1 sm:flex-none px-5 py-2.5 text-sm font-bold text-slate-600 bg-white hover:bg-slate-100 border border-slate-200 rounded-xl transition-colors"
                                    >
                                        Batal
                                    </button>
                                    <button type="submit" :disabled="isGenerating || generateForm.processing"
                                        class="flex-1 sm:flex-none px-6 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 rounded-xl flex items-center justify-center gap-2 shadow-lg shadow-emerald-200/50 disabled:opacity-60 disabled:cursor-not-allowed transition-all"
                                    >
                                        <span v-if="isGenerating || generateForm.processing" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
                                        <Zap v-else class="w-4 h-4" />
                                        {{ isGenerating || generateForm.processing ? 'Memproses...' : 'Generate' }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Modal Hari Aktif Sekolah -->
        <Transition name="modal">
            <div
                v-if="showHariAktifModal"
                class="fixed inset-0 z-50 overflow-y-auto"
                @click.self="showHariAktifModal = false"
            >
                <div class="flex min-h-screen items-start justify-center px-4 pt-10 pb-20 sm:pt-16 sm:pb-24">
                    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showHariAktifModal = false" />

                    <div class="modal-card relative bg-white w-full max-w-xl rounded-3xl shadow-2xl overflow-hidden">
                        <!-- Header -->
                        <div class="relative bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 px-6 py-5">
                            <div class="relative flex items-center gap-4">
                                <div class="w-12 h-12 bg-white/10 backdrop-blur-sm rounded-2xl flex items-center justify-center ring-1 ring-white/20 shrink-0">
                                    <CalendarDays class="w-6 h-6 text-indigo-400" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h2 class="text-lg font-black text-white tracking-tight">Kelola Hari Aktif Sekolah</h2>
                                    <p class="text-sm text-slate-300 font-medium truncate">Pilih hari belajar mengajar aktif di sekolah</p>
                                </div>
                                <button type="button" @click="showHariAktifModal = false"
                                    class="p-2 text-white/60 hover:text-white bg-white/10 hover:bg-white/20 rounded-xl transition-all shrink-0"
                                >
                                    <X class="w-5 h-5" />
                                </button>
                            </div>
                        </div>

                        <form @submit.prevent="submitHariAktif">
                            <div class="p-6 space-y-6">
                                <!-- Presets -->
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Preset Pilihan Cepat</label>
                                    <div class="grid grid-cols-3 gap-2">
                                        <button
                                            type="button"
                                            @click="setPresetHari('5-hari')"
                                            class="px-3 py-2 text-xs font-bold rounded-xl border transition-all text-center"
                                            :class="hariAktifForm.hari.length === 5 && !hariAktifForm.hari.includes('Sabtu')
                                                ? 'bg-indigo-50 border-indigo-300 text-indigo-700 ring-2 ring-indigo-500/20'
                                                : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'"
                                        >
                                            5 Hari (Sen-Jum)
                                        </button>
                                        <button
                                            type="button"
                                            @click="setPresetHari('6-hari')"
                                            class="px-3 py-2 text-xs font-bold rounded-xl border transition-all text-center"
                                            :class="hariAktifForm.hari.length === 6 && hariAktifForm.hari.includes('Sabtu')
                                                ? 'bg-indigo-50 border-indigo-300 text-indigo-700 ring-2 ring-indigo-500/20'
                                                : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'"
                                        >
                                            6 Hari (Sen-Sab)
                                        </button>
                                        <button
                                            type="button"
                                            @click="setPresetHari('7-hari')"
                                            class="px-3 py-2 text-xs font-bold rounded-xl border transition-all text-center"
                                            :class="hariAktifForm.hari.length === 7
                                                ? 'bg-indigo-50 border-indigo-300 text-indigo-700 ring-2 ring-indigo-500/20'
                                                : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'"
                                        >
                                            7 Hari (Penuh)
                                        </button>
                                    </div>
                                </div>

                                <!-- Checkbox List Hari -->
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Daftar Hari Aktif</label>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                        <div
                                            v-for="hari in (semuaHariList || ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'])"
                                            :key="hari"
                                            @click="toggleHariAktif(hari)"
                                            class="flex items-center justify-between p-3.5 rounded-2xl border cursor-pointer transition-all select-none"
                                            :class="hariAktifForm.hari.includes(hari)
                                                ? 'bg-gradient-to-r from-indigo-50/80 to-purple-50/80 border-indigo-200 shadow-sm'
                                                : 'bg-slate-50/50 border-slate-200 opacity-60 hover:opacity-100'"
                                        >
                                            <div class="flex items-center gap-3">
                                                <span class="w-3 h-3 rounded-full shrink-0" :class="getDayBgColor(hari)" />
                                                <span class="text-sm font-bold" :class="getDayTextColor(hari)">
                                                    {{ hari }}
                                                </span>
                                            </div>
                                            <div
                                                class="w-6 h-6 rounded-lg flex items-center justify-center transition-all"
                                                :class="hariAktifForm.hari.includes(hari)
                                                    ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-200'
                                                    : 'border border-slate-300 bg-white'"
                                            >
                                                <Check v-if="hariAktifForm.hari.includes(hari)" class="w-4 h-4" />
                                            </div>
                                        </div>
                                    </div>
                                    <p v-if="hariAktifForm.errors.hari" class="text-rose-600 text-xs font-bold mt-2">
                                        {{ hariAktifForm.errors.hari }}
                                    </p>
                                </div>

                                <!-- Info Note -->
                                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-start gap-3">
                                    <AlertCircle class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" />
                                    <div class="text-xs text-amber-800 space-y-1">
                                        <p class="font-bold">Informasi Hari Aktif:</p>
                                        <p>Hari yang dinonaktifkan tidak akan muncul pada tabel jadwal pelajaran dan pilihan hari pada form penambahan jam pelajaran. Fitur generate otomatis juga hanya akan mengisi slot pada hari aktif.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                                <button
                                    type="button"
                                    @click="showHariAktifModal = false"
                                    class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-white hover:bg-slate-100 border border-slate-200 rounded-xl transition-colors"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    :disabled="hariAktifForm.processing"
                                    class="px-6 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl flex items-center gap-2 shadow-lg shadow-indigo-200 disabled:opacity-60 transition-all"
                                >
                                    <Check class="w-4 h-4" />
                                    {{ hariAktifForm.processing ? 'Menyimpan...' : 'Simpan Hari Aktif' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Modal Add/Edit -->
        <div
            v-if="showModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm"
            @click.self="showModal = false"
        >
            <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between p-6 border-b border-slate-100">
                    <h2 class="text-xl font-black text-slate-900">
                        {{ editTarget ? 'Edit Jadwal' : 'Tambah Jadwal Baru' }}
                    </h2>
                    <button @click="showModal = false" class="p-2 text-slate-400 hover:text-slate-600 bg-slate-50 rounded-xl transition-colors">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="p-6 space-y-4">
                    <div v-if="!editTarget">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Rombongan Belajar</label>
                        <select v-model="form.rombel_id" required
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600"
                        >
                            <option value="">-- Pilih Rombel --</option>
                            <option v-for="r in formRombels" :key="r.id" :value="r.id">{{ r.nama }}</option>
                        </select>
                        <p v-if="form.errors.rombel_id" class="text-xs text-rose-500 mt-1 font-bold">{{ form.errors.rombel_id }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Mata Pelajaran</label>
                        <select v-model="form.mata_pelajaran_id" required
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600"
                        >
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            <option v-for="m in mapelList" :key="m.id" :value="m.id">
                                {{ m.nama }} ({{ m.kode }})
                            </option>
                        </select>
                        <p v-if="form.errors.mata_pelajaran_id" class="text-xs text-rose-500 mt-1 font-bold">{{ form.errors.mata_pelajaran_id }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Guru Pengampu</label>
                        <select v-model="form.guru_id" required
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600"
                        >
                            <option value="">-- Pilih Guru --</option>
                            <option v-for="g in guruList" :key="g.id" :value="g.id">{{ g.nama }}</option>
                        </select>
                        <p v-if="form.errors.guru_id" class="text-xs text-rose-500 mt-1 font-bold">{{ form.errors.guru_id }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Hari</label>
                            <select v-model="form.hari" required
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600"
                            >
                                <option value="">-- Hari --</option>
                                <option v-for="h in hariList" :key="h" :value="h">{{ h }}</option>
                            </select>
                            <p v-if="form.errors.hari" class="text-xs text-rose-500 mt-1 font-bold">{{ form.errors.hari }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Jam Ke-</label>
                            <select v-model="form.jam_ke" required
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600"
                            >
                                <option value="">-- Jam --</option>
                                <option v-for="j in maxJam" :key="j" :value="j">{{ j }}</option>
                            </select>
                            <p v-if="form.errors.jam_ke" class="text-xs text-rose-500 mt-1 font-bold">{{ form.errors.jam_ke }}</p>
                        </div>
                    </div>

                    <div v-if="!editTarget">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Tahun Ajaran</label>
                        <select v-model="form.tahun_ajaran_id" required
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600"
                        >
                            <option value="">-- Pilih Tahun Ajaran --</option>
                            <option v-for="t in tahunAjaranList" :key="t.id" :value="t.id">
                                {{ t.nama }} {{ t.is_active ? '(Aktif)' : '' }}
                            </option>
                        </select>
                    </div>

                    <!-- Conflict Warning Banner -->
                    <div v-if="conflictWarning" class="p-4 bg-rose-50 border-2 border-rose-200 rounded-2xl flex items-start gap-3 text-rose-900 animate-pulse">
                        <AlertCircle class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" />
                        <div>
                            <h4 class="font-black text-xs uppercase tracking-wider text-rose-700">{{ conflictWarning.title }}</h4>
                            <p class="text-xs font-semibold mt-0.5 text-rose-800 leading-snug">{{ conflictWarning.message }}</p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showModal = false"
                            class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors"
                        >
                            Batal
                        </button>
                        <button type="submit" :disabled="form.processing || !!conflictWarning"
                            class="px-5 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors flex items-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed"
                        >
                            <Check class="w-4 h-4" />
                            {{ form.processing ? 'Menyimpan...' : (editTarget ? 'Perbarui' : 'Simpan') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Konfirmasi Hapus Profesional -->
        <Transition name="modal">
            <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" @click.self="showDeleteModal = false">
                <div class="modal-card bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden p-6 space-y-5">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 ring-4 ring-rose-50">
                            <Trash2 class="w-6 h-6" />
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-900">Hapus Jam Jadwal?</h3>
                            <p class="text-xs font-medium text-slate-500 mt-0.5">Tindakan ini akan menghapus entri pelajaran dari jadwal.</p>
                        </div>
                    </div>

                    <div v-if="deleteTarget" class="bg-slate-50 border border-slate-200 rounded-2xl p-4 space-y-2 text-xs">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500 font-bold uppercase tracking-wider text-[10px]">Mata Pelajaran:</span>
                            <span class="font-extrabold text-slate-900">{{ deleteTarget.mata_pelajaran?.nama || '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500 font-bold uppercase tracking-wider text-[10px]">Guru Pengampu:</span>
                            <span class="font-bold text-slate-700">{{ deleteTarget.guru?.nama || '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500 font-bold uppercase tracking-wider text-[10px]">Hari & Jam Ke:</span>
                            <span class="font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-md border border-indigo-100">
                                {{ deleteTarget.hari }}, Jam Ke-{{ deleteTarget.jam_ke }}
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="showDeleteModal = false"
                            class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors"
                        >
                            Batal
                        </button>
                        <button type="button" @click="confirmHapus" :disabled="isDeleting"
                            class="px-5 py-2.5 text-sm font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition-colors flex items-center gap-2 shadow-lg shadow-rose-200 disabled:opacity-60"
                        >
                            <span v-if="isDeleting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
                            <Trash2 v-else class="w-4 h-4" />
                            {{ isDeleting ? 'Menghapus...' : 'Ya, Hapus' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </AuthenticatedLayout>
</template>

<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.2s ease;
}
.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

/* Card entrance animation */
.modal-enter-active .modal-card {
    animation: cardIn 0.25s ease-out;
}
.modal-leave-active .modal-card {
    animation: cardOut 0.15s ease-in forwards;
}

@keyframes cardIn {
    from { opacity: 0; transform: scale(0.95) translateY(10px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}
@keyframes cardOut {
    from { opacity: 1; transform: scale(1) translateY(0); }
    to { opacity: 0; transform: scale(0.95) translateY(10px); }
}

/* Custom scrollbar for modal body */
.form-scroll::-webkit-scrollbar {
    width: 4px;
}
.form-scroll::-webkit-scrollbar-track {
    background: transparent;
}
.form-scroll::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}
.form-scroll::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
