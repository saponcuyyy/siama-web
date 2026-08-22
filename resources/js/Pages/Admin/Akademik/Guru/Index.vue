<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import {
    UserSquare, Plus, Search, Pencil, Trash2, AlertTriangle, X, Check, Users, Info,
    Mail, Briefcase, Upload, FileSpreadsheet, Download, BookOpen, Clock, ChevronDown, Save,
} from 'lucide-vue-next';
import dayjs from 'dayjs';

const props = defineProps({
    guruList: Object,
    filters: Object,
    mapelList: Array,
    rombelList: Array,
});

const JABATAN_OPTIONS = [
    'Kepala Sekolah',
    'Wakil Kepala Sekolah Bidang Kurikulum',
    'Wakil Kepala Sekolah Bidang Kesiswaan',
    'Wakil Kepala Sekolah Bidang Sarpras',
    'Wakil Kepala Sekolah Bidang Humas',
    'Bendahara',
    'Kepala Perpustakaan',
    'Kepala Laboratorium',
    'Bimbingan Konseling',
    'Tata Usaha',
    'Guru',
    'Lainnya',
];

const JABATAN_BADGE = {
    'Kepala Sekolah':                            'bg-purple-100 text-purple-700 ring-purple-200',
    'Wakil Kepala Sekolah Bidang Kurikulum':     'bg-indigo-100 text-indigo-700 ring-indigo-200',
    'Wakil Kepala Sekolah Bidang Kesiswaan':     'bg-cyan-100 text-cyan-700 ring-cyan-200',
    'Wakil Kepala Sekolah Bidang Sarpras':       'bg-amber-100 text-amber-700 ring-amber-200',
    'Wakil Kepala Sekolah Bidang Humas':         'bg-lime-100 text-lime-700 ring-lime-200',
    'Bendahara':                                 'bg-emerald-100 text-emerald-700 ring-emerald-200',
    'Kepala Perpustakaan':                       'bg-teal-100 text-teal-700 ring-teal-200',
    'Kepala Laboratorium':                       'bg-sky-100 text-sky-700 ring-sky-200',
    'Bimbingan Konseling':                       'bg-pink-100 text-pink-700 ring-pink-200',
    'Tata Usaha':                                'bg-orange-100 text-orange-700 ring-orange-200',
    'Guru':                                      'bg-slate-100 text-slate-600 ring-slate-200',
    'Lainnya':                                   'bg-gray-100 text-gray-600 ring-gray-200',
};

const getBadgeClass = (jabatan) => JABATAN_BADGE[jabatan] ?? 'bg-slate-100 text-slate-600 ring-slate-200';

const search          = ref('');
const showModal       = ref(false);
const showImportModal = ref(false);
const showDeleteModal = ref(false);
const deleteTarget    = ref(null);
const isDeleting      = ref(false);
const editTarget      = ref(null);

const form = useForm({
    nama:          '',
    nip:           '',
    jabatan:       'Guru',
    email:         '',
    tanggal_lahir: '',
});

// ── Penugasan mengajar per kelas ─────────────────────────────
// Struktur: [{ mapel_id, kelas: [{ rombel_id: number|null, jam }] }]
const assignments = ref([]);

const showMapelPicker = ref(false);
const mapelSearch     = ref('');

const mapelLabel = (mapel) => {
    if (!mapel) return '';
    const parts = [mapel.nama];
    if (mapel.tingkat) {
        parts.push(mapel.tingkat + (mapel.jurusan ? ' ' + mapel.jurusan : ''));
    } else {
        parts.push(mapel.kode);
    }
    return parts.join(' — ');
};

const mapelMeta = (mapel) => {
    if (!mapel) return '';
    if (mapel.tingkat) return `Tingkat ${mapel.tingkat}${mapel.jurusan ? ' • ' + mapel.jurusan : ''}`;
    return mapel.kode || '';
};

const mapelById = (id) => (props.mapelList || []).find(m => m.id === id);

const filteredMapelOptions = computed(() => {
    const q = mapelSearch.value.trim().toLowerCase();
    const list = props.mapelList || [];
    if (!q) return list;
    return list.filter(m =>
        m.nama.toLowerCase().includes(q) ||
        (m.kode || '').toLowerCase().includes(q)
    );
});

const isMapelSelected = (id) => assignments.value.some(a => a.mapel_id === id);

const toggleMapel = (mapel) => {
    const idx = assignments.value.findIndex(a => a.mapel_id === mapel.id);
    if (idx >= 0) {
        assignments.value.splice(idx, 1);
    } else {
        assignments.value.push({ mapel_id: mapel.id, kelas: [{ rombel_id: null, jam: 2 }] });
    }
};

const removeAssignment = (index) => assignments.value.splice(index, 1);

const addKelasRow = (assignment) => assignment.kelas.push({ rombel_id: null, jam: 2 });

const removeKelasRow = (assignment, index) => {
    assignment.kelas.splice(index, 1);
    if (!assignment.kelas.length) {
        removeAssignment(assignments.value.indexOf(assignment));
    }
};

const subtotalJp = (assignment) =>
    assignment.kelas.reduce((sum, k) => sum + (Number(k.jam) || 0), 0);

const totalJpForm = computed(() =>
    assignments.value.reduce((sum, a) => sum + subtotalJp(a), 0)
);

const totalPenugasanKelas = computed(() =>
    assignments.value.reduce((sum, a) => sum + a.kelas.length, 0)
);

// Opsi kelas difilter sesuai tingkat & jurusan mapel; kelas yang sudah
// terpakai pada baris lain dalam mapel yang sama dinonaktifkan.
const kelasOptions = (assignment) => {
    const mapel = mapelById(assignment.mapel_id);
    let list = props.rombelList || [];
    if (mapel?.tingkat) {
        const matched = list.filter(r =>
            r.tingkat === mapel.tingkat &&
            (!mapel.jurusan || r.jurusan === mapel.jurusan)
        );
        if (matched.length) list = matched;
    }
    const ids = new Set(list.map(r => r.id));
    // Pastikan pilihan yang sudah tersimpan tetap tampil walau di luar filter.
    const extras = (props.rombelList || []).filter(r =>
        !ids.has(r.id) && assignment.kelas.some(k => Number(k.rombel_id) === r.id)
    );
    return [...list, ...extras];
};

const isRombelTaken = (assignment, rombelId, selfIndex) =>
    assignment.kelas.some((k, i) => i !== selfIndex && Number(k.rombel_id) === Number(rombelId));

const rombelLabel = (r) => {
    if (!r) return '';
    const parts = [r.nama];
    if (r.tahun_ajaran?.nama) parts.push(r.tahun_ajaran.nama);
    return parts.join(' — ');
};

// ── Tabel: kelompokkan penugasan per mapel untuk ditampilkan ──
const groupPenugasan = (guru) => {
    const groups = [];
    const byId = {};
    for (const p of (guru.penugasan || [])) {
        if (!byId[p.mata_pelajaran_id]) {
            byId[p.mata_pelajaran_id] = {
                id: p.mata_pelajaran_id,
                nama: p.mapel_nama,
                meta: p.mapel_tingkat
                    ? `T${p.mapel_tingkat}${p.mapel_jurusan ? ' • ' + p.mapel_jurusan : ''}`
                    : (p.mapel_kode || ''),
                kelas: [],
            };
            groups.push(byId[p.mata_pelajaran_id]);
        }
        byId[p.mata_pelajaran_id].kelas.push({
            nama: p.rombel_nama || 'Umum',
            jam: p.jam_per_minggu || 0,
        });
    }
    return groups;
};

const totalJpGuru = (guru) =>
    (guru.penugasan || []).reduce((sum, p) => sum + (Number(p.jam_per_minggu) || 0), 0);

// ── Import excel ─────────────────────────────────────────────
const openImport = () => {
    selectedFile.value = null;
    importForm.reset();
    showImportModal.value = true;
};

const triggerFileSelect = () => {
    fileInput.value?.click();
};

const onFileChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        selectedFile.value = file;
        importForm.file = file;
    }
};

const handleDrop = (e) => {
    dragOver.value = false;
    const file = e.dataTransfer.files[0];
    if (file) {
        selectedFile.value = file;
        importForm.file = file;
    }
};

const removeSelectedFile = () => {
    selectedFile.value = null;
    importForm.file = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const importForm = useForm({
    file: null,
});

const selectedFile = ref(null);
const fileInput = ref(null);
const dragOver = ref(false);

const openCreate = () => {
    editTarget.value = null;
    form.reset();
    form.jabatan = 'Guru';
    assignments.value = [];
    showMapelPicker.value = false;
    mapelSearch.value = '';
    showModal.value = true;
};

const openEdit = (guru) => {
    editTarget.value = guru;
    form.nama          = guru.nama;
    form.nip           = guru.nip;
    form.jabatan       = guru.jabatan || 'Guru';
    form.email         = guru.user?.email || '';
    form.tanggal_lahir = guru.tanggal_lahir || '';

    const grouped = {};
    for (const p of (guru.penugasan || [])) {
        if (!grouped[p.mata_pelajaran_id]) {
            grouped[p.mata_pelajaran_id] = { mapel_id: p.mata_pelajaran_id, kelas: [] };
        }
        grouped[p.mata_pelajaran_id].kelas.push({
            rombel_id: p.rombel_id ?? null,
            jam: p.jam_per_minggu ?? 0,
        });
    }
    assignments.value = Object.values(grouped);
    showMapelPicker.value = false;
    mapelSearch.value = '';
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    showMapelPicker.value = false;
};

const handleSearch = () => {
    router.get(route('admin.web.guru.index'), {
        search: search.value,
    }, { preserveState: true, replace: true });
};

const submitForm = () => {
    showMapelPicker.value = false;

    form.transform((data) => ({
        ...data,
        mata_pelajaran: assignments.value.map(a => ({
            id: a.mapel_id,
            kelas: a.kelas.map(k => ({
                rombel_id: k.rombel_id ? Number(k.rombel_id) : null,
                jam: Math.max(0, Math.min(40, Number(k.jam) || 0)),
            })),
        })),
    }));

    if (editTarget.value) {
        form.put(route('admin.web.guru.update', editTarget.value.hashid), {
            onSuccess: () => { showModal.value = false; form.reset(); },
        });
    } else {
        form.post(route('admin.web.guru.store'), {
            onSuccess: () => { showModal.value = false; form.reset(); },
        });
    }
};

const submitImport = () => {
    importForm.post(route('admin.web.guru.import'), {
        onSuccess: () => {
            showImportModal.value = false;
            selectedFile.value = null;
            importForm.reset();
        },
    });
};

const formatDate = (date) => {
    if (!date) return '-';
    return dayjs(date).format('DD MMM YYYY');
};

const openDelete = (guru) => {
    deleteTarget.value = guru;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (!deleteTarget.value) return;
    isDeleting.value = true;
    router.delete(route('admin.web.guru.destroy', deleteTarget.value.hashid), {
        onSuccess: () => {
            showDeleteModal.value = false;
            deleteTarget.value = null;
        },
        onFinish: () => { isDeleting.value = false; },
    });
};

const getInitials = (name) => {
    return name.split(' ').slice(0, 2).map(n => n[0]).join('').toUpperCase();
};

const avatarColors = ['from-indigo-500 to-purple-600', 'from-emerald-500 to-teal-600', 'from-amber-500 to-orange-600', 'from-rose-500 to-pink-600', 'from-cyan-500 to-blue-600'];
const getColor = (id) => avatarColors[id % avatarColors.length];

// ── UX modal: lock scroll body + tutup dengan tombol ESC ─────
watch(showModal, (open) => {
    document.body.style.overflow = open ? 'hidden' : '';
});

const onKeydown = (e) => {
    if (e.key === 'Escape' && showModal.value) closeModal();
};

onMounted(() => window.addEventListener('keydown', onKeydown));
onUnmounted(() => window.removeEventListener('keydown', onKeydown));
</script>

<template>
    <Head title="Manajemen Data Guru" />
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between gap-4 items-start sm:items-center">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                        <UserSquare class="w-7 h-7 text-indigo-600" />
                        Data Guru
                    </h1>
                    <p class="text-slate-500 font-medium mt-1">Kelola data guru beserta jabatan dan akun akses sistem.</p>
                </div>
                <div class="flex flex-wrap gap-2.5">
                    <button @click="openImport" class="px-5 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-bold rounded-xl flex items-center gap-2 transition-all shadow-sm">
                        <Upload class="w-4 h-4 text-slate-500" /> Import Guru
                    </button>
                    <button @click="openCreate" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl flex items-center gap-2 transition-colors shadow-lg shadow-indigo-200">
                        <Plus class="w-5 h-5" /> Tambah Guru
                    </button>
                </div>
            </div>

            <!-- Flash -->
            <div v-if="$page.props.flash?.success" class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 font-medium rounded-xl text-sm flex items-start gap-2">
                <Check class="w-4 h-4 mt-0.5 shrink-0" />
                <span>{{ $page.props.flash.success }}</span>
            </div>
            <div v-if="$page.props.flash?.error" class="p-4 bg-rose-50 border border-rose-200 text-rose-800 font-medium rounded-xl text-sm">
                ✗ {{ $page.props.flash.error }}
            </div>
            <div v-if="$page.props.flash?.warning" class="p-4 bg-amber-50 border border-amber-200 text-amber-800 font-medium rounded-xl text-sm">
                ⚠ {{ $page.props.flash.warning }}
            </div>

            <!-- Info akun otomatis -->
            <div class="p-4 bg-indigo-50 border border-indigo-100 text-indigo-700 font-medium rounded-2xl text-sm flex items-start gap-3">
                <Info class="w-5 h-5 shrink-0 mt-0.5" />
                <div>
                    Saat menambahkan guru baru, sistem akan <strong>otomatis membuat akun login</strong> dengan role <strong>Guru</strong>. Username: <code class="bg-white px-1.5 py-0.5 rounded font-mono text-xs">{email}</code> dan password default: <code class="bg-white px-1.5 py-0.5 rounded font-mono text-xs">ddmmyyyy*</code> (tanggal lahir guru).
                </div>
            </div>

            <!-- Search -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row gap-4 items-center">
                <div class="relative w-full sm:w-80">
                    <input type="text" v-model="search" @keyup.enter="handleSearch"
                        placeholder="Cari nama atau NIP/NIK..."
                        class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600 text-sm" />
                    <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
                </div>
                <button @click="handleSearch" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl transition-colors shrink-0">
                    Cari
                </button>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 bg-slate-50/50 border-b border-slate-200 flex items-center justify-between">
                    <p class="text-sm font-bold text-slate-600">
                        Total: <span class="text-indigo-600">{{ guruList.total }}</span> guru
                    </p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider">
                                <th class="p-4 pl-6">Guru</th>
                                <th class="p-4">NIP / NIK</th>
                                <th class="p-4">Jabatan</th>
                                <th class="p-4">Jam Mengajar / Kelas</th>
                                <th class="p-4">Tanggal Lahir</th>
                                <th class="p-4">Email</th>
                                <th class="p-4 pr-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <tr v-for="guru in guruList.data" :key="guru.id"
                                class="hover:bg-slate-50/50 transition-colors align-top">
                                <td class="p-4 pl-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-black text-sm bg-gradient-to-br shrink-0"
                                            :class="getColor(guru.id)">
                                            {{ getInitials(guru.nama) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900">{{ guru.nama }}</p>
                                            <p v-if="totalJpGuru(guru) > 0" class="text-[11px] font-bold text-indigo-500 mt-0.5 flex items-center gap-1">
                                                <Clock class="w-3 h-3" /> {{ totalJpGuru(guru) }} JP/minggu
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span class="font-mono font-bold text-slate-700 bg-slate-100 px-2.5 py-1 rounded-lg text-xs">{{ guru.nip }}</span>
                                </td>
                                <td class="p-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold ring-1"
                                        :class="getBadgeClass(guru.jabatan)">
                                        <Briefcase class="w-3 h-3" />
                                        {{ guru.jabatan || 'Guru' }}
                                    </span>
                                </td>
                                <td class="p-4 min-w-[240px]">
                                    <div v-if="!guru.penugasan?.length" class="text-slate-400 text-xs">-</div>
                                    <div v-else class="space-y-2.5 max-w-md">
                                        <div v-for="group in groupPenugasan(guru)" :key="group.id">
                                            <p class="text-xs font-bold text-slate-800 leading-tight flex items-center gap-1.5">
                                                <BookOpen class="w-3.5 h-3.5 text-indigo-500 shrink-0" />
                                                {{ group.nama }}
                                                <span v-if="group.meta" class="text-[10px] font-bold uppercase tracking-wide text-slate-400">{{ group.meta }}</span>
                                            </p>
                                            <div class="flex flex-wrap gap-1 mt-1 pl-5">
                                                <span v-for="(k, i) in group.kelas" :key="i"
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 bg-slate-100 text-slate-600 text-[11px] font-bold rounded-lg">
                                                    {{ k.nama }}
                                                    <span class="text-indigo-600">{{ k.jam }} JP</span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4 font-medium text-slate-600">
                                    {{ formatDate(guru.tanggal_lahir) }}
                                </td>
                                <td class="p-4">
                                    <span class="flex items-center gap-1.5 text-slate-600">
                                        <Mail class="w-3.5 h-3.5 text-slate-400" />
                                        {{ guru.user?.email || '-' }}
                                    </span>
                                </td>
                                <td class="p-4 pr-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="openEdit(guru)" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-colors">
                                            <Pencil class="w-4 h-4" />
                                        </button>
                                        <button @click="openDelete(guru)" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-colors">
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="guruList.data.length === 0">
                                <td colspan="7" class="p-12 text-center">
                                    <div class="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-3 text-slate-400">
                                        <Users class="w-6 h-6" />
                                    </div>
                                    <p class="font-bold text-slate-700">Tidak ada data guru</p>
                                    <p class="text-slate-500 text-xs mt-1">Coba ubah filter atau tambahkan guru baru.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <Pagination :data="guruList" />
            </div>
        </div>

        <!-- ══════════ Modal Besar Tambah / Edit Guru ══════════ -->
        <Transition name="modal-fade">
            <div v-if="showModal"
                class="fixed inset-0 z-50 flex items-start justify-center p-0 sm:p-6 overflow-hidden bg-slate-900/60 backdrop-blur-sm"
                @click.self="closeModal">
                <Transition name="modal-panel" appear>
                    <div class="bg-white w-full max-w-5xl h-full sm:h-auto sm:max-h-[92vh] rounded-none sm:rounded-3xl shadow-2xl ring-1 ring-slate-900/5 overflow-hidden flex flex-col">

                        <!-- Header gradien -->
                        <div class="relative shrink-0 bg-gradient-to-r from-indigo-600 via-indigo-600 to-purple-600 px-6 lg:px-8 py-6 text-white overflow-hidden">
                            <div class="absolute -top-16 -right-16 w-56 h-56 rounded-full bg-white/10 blur-2xl"></div>
                            <div class="absolute -bottom-20 right-32 w-40 h-40 rounded-full bg-white/5 blur-xl"></div>
                            <div class="relative flex items-start justify-between gap-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-white/15 ring-1 ring-white/25 backdrop-blur flex items-center justify-center shrink-0">
                                        <UserSquare class="w-7 h-7" />
                                    </div>
                                    <div>
                                        <h2 class="text-xl font-black tracking-tight">{{ editTarget ? 'Edit Data Guru' : 'Tambah Guru Baru' }}</h2>
                                        <p class="text-indigo-100 text-xs font-medium mt-0.5">
                                            {{ editTarget ? 'Perbarui informasi profil dan penugasan mengajar per kelas.' : 'Lengkapi profil guru beserta penugasan mengajar per kelas.' }}
                                        </p>
                                    </div>
                                </div>
                                <button type="button" @click="closeModal"
                                    class="p-2.5 rounded-xl bg-white/10 hover:bg-white/25 ring-1 ring-white/20 transition-colors shrink-0">
                                    <X class="w-5 h-5" />
                                </button>
                            </div>
                        </div>

                        <form @submit.prevent="submitForm" class="flex flex-col flex-1 min-h-0">
                            <!-- Body dua kolom -->
                            <div class="flex-1 min-h-0 overflow-y-auto bg-slate-50/60 px-6 lg:px-8 py-6">
                                <!-- Ringkasan error -->
                                <div v-if="form.hasErrors" class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 space-y-1">
                                    <p class="flex items-center gap-1.5 text-xs font-black uppercase tracking-wider text-rose-600 mb-1">
                                        <AlertTriangle class="w-3.5 h-3.5" /> Periksa kembali isian berikut
                                    </p>
                                    <p v-for="(msg, field) in form.errors" :key="field" class="text-xs font-bold text-rose-600">• {{ msg }}</p>
                                </div>

                                <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 items-start">
                                    <!-- Kolom kiri: identitas & akun -->
                                    <div class="lg:col-span-2 space-y-6">
                                        <section class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 lg:p-6">
                                            <p class="flex items-center gap-2 text-[11px] font-black uppercase tracking-widest text-slate-400 mb-5">
                                                <span class="w-6 h-6 rounded-lg bg-indigo-50 text-indigo-600 grid place-items-center"><UserSquare class="w-3.5 h-3.5" /></span>
                                                Informasi Guru
                                            </p>

                                            <div class="space-y-4">
                                                <div>
                                                    <label class="block text-xs font-bold text-slate-600 mb-1.5">Nama Lengkap <span class="text-rose-500">*</span></label>
                                                    <input type="text" v-model="form.nama" required placeholder="Nama lengkap guru beserta gelar"
                                                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition" />
                                                    <p v-if="form.errors.nama" class="text-xs text-rose-500 mt-1 font-bold">{{ form.errors.nama }}</p>
                                                </div>

                                                <div>
                                                    <label class="block text-xs font-bold text-slate-600 mb-1.5">NIP / NIK <span class="text-rose-500">*</span></label>
                                                    <input type="text" v-model="form.nip" required placeholder="Nomor Induk Pegawai / NIK"
                                                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition" />
                                                    <p v-if="form.errors.nip" class="text-xs text-rose-500 mt-1 font-bold">{{ form.errors.nip }}</p>
                                                </div>

                                                <div>
                                                    <label class="block text-xs font-bold text-slate-600 mb-1.5">Jabatan</label>
                                                    <select v-model="form.jabatan"
                                                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                                                        <option v-for="j in JABATAN_OPTIONS" :key="j" :value="j">{{ j }}</option>
                                                    </select>
                                                    <p v-if="form.errors.jabatan" class="text-xs text-rose-500 mt-1 font-bold">{{ form.errors.jabatan }}</p>
                                                </div>

                                                <div>
                                                    <label class="block text-xs font-bold text-slate-600 mb-1.5">Tanggal Lahir</label>
                                                    <input type="date" v-model="form.tanggal_lahir"
                                                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition" />
                                                    <p v-if="form.errors.tanggal_lahir" class="text-xs text-rose-500 mt-1 font-bold">{{ form.errors.tanggal_lahir }}</p>
                                                </div>
                                            </div>
                                        </section>

                                        <section class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 lg:p-6">
                                            <p class="flex items-center gap-2 text-[11px] font-black uppercase tracking-widest text-slate-400 mb-5">
                                                <span class="w-6 h-6 rounded-lg bg-indigo-50 text-indigo-600 grid place-items-center"><Mail class="w-3.5 h-3.5" /></span>
                                                Akun Login
                                            </p>

                                            <div>
                                                <label class="block text-xs font-bold text-slate-600 mb-1.5">Email <span class="text-rose-500">*</span></label>
                                                <input type="email" v-model="form.email" required placeholder="email@sekolah.com"
                                                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition" />
                                                <p v-if="form.errors.email" class="text-xs text-rose-500 mt-1 font-bold">{{ form.errors.email }}</p>
                                                <p v-if="!editTarget" class="mt-2 p-2.5 rounded-xl bg-indigo-50/70 text-[11px] leading-relaxed text-indigo-700 font-medium">
                                                    Akun otomatis dibuat. Username: <span class="font-mono font-bold">{{ form.email || 'email' }}</span>,
                                                    password default: <span class="font-mono font-bold">ddmmyyyy*</span> (tanggal lahir).
                                                </p>
                                            </div>
                                        </section>
                                    </div>

                                    <!-- Kolom kanan: penugasan mengajar per kelas -->
                                    <div class="lg:col-span-3 space-y-4">
                                        <section class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-visible">
                                            <div class="flex items-center justify-between gap-3 px-5 lg:px-6 pt-5 pb-4">
                                                <p class="flex items-center gap-2 text-[11px] font-black uppercase tracking-widest text-slate-400">
                                                    <span class="w-6 h-6 rounded-lg bg-indigo-50 text-indigo-600 grid place-items-center"><BookOpen class="w-3.5 h-3.5" /></span>
                                                    Penugasan Mengajar
                                                </p>
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-600 text-white text-xs font-black shadow-sm shadow-indigo-200 shrink-0">
                                                    <Clock class="w-3.5 h-3.5" /> {{ totalJpForm }} JP/minggu
                                                </span>
                                            </div>

                                            <!-- Pemilih mata pelajaran -->
                                            <div class="px-5 lg:px-6 pb-5 lg:pb-6">
                                                <div class="border border-slate-200 rounded-2xl overflow-visible relative">
                                                    <button type="button" @click="showMapelPicker = !showMapelPicker"
                                                        class="w-full flex items-center justify-between gap-2 px-4 py-3 hover:bg-slate-50 transition-colors">
                                                        <span class="flex items-center gap-2 text-sm">
                                                            <span v-if="assignments.length === 0" class="text-slate-400 font-medium">Pilih mata pelajaran yang diampu...</span>
                                                            <span v-else class="font-bold text-slate-700">{{ assignments.length }} mata pelajaran dipilih</span>
                                                        </span>
                                                        <ChevronDown class="w-4 h-4 text-slate-400 transition-transform" :class="{ 'rotate-180': showMapelPicker }" />
                                                    </button>

                                                    <div v-if="showMapelPicker"
                                                        class="absolute left-0 right-0 top-full z-30 mt-2 bg-white border border-slate-200 rounded-2xl shadow-xl overflow-hidden">
                                                        <div class="p-3 border-b border-slate-100 sticky top-0 bg-white z-10">
                                                            <input type="text" v-model="mapelSearch" placeholder="Cari mata pelajaran..."
                                                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500" />
                                                        </div>
                                                        <div class="max-h-64 overflow-y-auto divide-y divide-slate-50">
                                                            <label v-for="mapel in filteredMapelOptions" :key="mapel.id"
                                                                class="flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 cursor-pointer text-sm transition-colors">
                                                                <input type="checkbox" :checked="isMapelSelected(mapel.id)" @change="toggleMapel(mapel)"
                                                                    class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 shrink-0" />
                                                                <span class="min-w-0">
                                                                    <span class="block font-bold text-slate-700 truncate">{{ mapel.nama }}</span>
                                                                    <span class="block text-[11px] text-slate-400 font-semibold">{{ mapelMeta(mapel) }}</span>
                                                                </span>
                                                            </label>
                                                            <p v-if="!filteredMapelOptions.length" class="px-4 py-8 text-center text-xs text-slate-400">
                                                                Mata pelajaran tidak ditemukan.
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <p v-if="form.errors.mata_pelajaran" class="text-xs text-rose-500 mt-2 font-bold">{{ form.errors.mata_pelajaran }}</p>

                                                <!-- Daftar penugasan per mapel -->
                                                <div v-if="assignments.length" class="mt-4 space-y-3">
                                                    <div v-for="(a, ai) in assignments" :key="a.mapel_id"
                                                        class="rounded-2xl border border-slate-200 overflow-hidden">
                                                        <div class="flex items-center justify-between gap-2 px-4 py-3 bg-slate-50 border-b border-slate-100">
                                                            <div class="min-w-0 flex items-center gap-2.5">
                                                                <span class="w-8 h-8 rounded-xl bg-white ring-1 ring-slate-200 text-indigo-600 grid place-items-center shrink-0">
                                                                    <BookOpen class="w-4 h-4" />
                                                                </span>
                                                                <div class="min-w-0">
                                                                    <p class="text-sm font-black text-slate-800 truncate">{{ mapelById(a.mapel_id)?.nama }}</p>
                                                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ mapelMeta(mapelById(a.mapel_id)) }}</p>
                                                                </div>
                                                            </div>
                                                            <div class="flex items-center gap-1.5 shrink-0">
                                                                <span class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 text-[11px] font-black">{{ subtotalJp(a) }} JP</span>
                                                                <button type="button" @click="removeAssignment(ai)"
                                                                    class="p-1.5 rounded-lg text-slate-300 hover:text-rose-600 hover:bg-rose-50 transition-colors">
                                                                    <Trash2 class="w-4 h-4" />
                                                                </button>
                                                            </div>
                                                        </div>

                                                        <div class="p-4 space-y-2.5">
                                                            <div v-for="(k, ki) in a.kelas" :key="ki" class="flex items-center gap-2">
                                                                <select v-model="k.rombel_id"
                                                                    class="flex-1 min-w-0 px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-700 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                                                                    <option :value="null">Tanpa Kelas (umum)</option>
                                                                    <option v-for="r in kelasOptions(a)" :key="r.id" :value="r.id" :disabled="isRombelTaken(a, r.id, ki)">
                                                                        {{ rombelLabel(r) }}
                                                                    </option>
                                                                </select>
                                                                <div class="relative w-24 shrink-0">
                                                                    <input type="number" min="0" max="40" v-model.number="k.jam" placeholder="0"
                                                                        class="w-full pl-3 pr-9 py-2.5 bg-white border border-indigo-200 rounded-xl text-sm text-center font-extrabold text-indigo-700 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition" />
                                                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-bold text-slate-300 pointer-events-none">JP</span>
                                                                </div>
                                                                <button type="button" @click="removeKelasRow(a, ki)" :disabled="a.kelas.length <= 1"
                                                                    class="p-2.5 rounded-xl text-slate-300 hover:text-rose-500 hover:bg-rose-50 transition-colors disabled:opacity-30 disabled:pointer-events-none shrink-0">
                                                                    <X class="w-4 h-4" />
                                                                </button>
                                                            </div>

                                                            <button type="button" @click="addKelasRow(a)"
                                                                class="w-full mt-1 py-2.5 border-2 border-dashed border-slate-200 hover:border-indigo-300 hover:bg-indigo-50/40 rounded-xl text-xs font-bold text-slate-500 hover:text-indigo-600 transition-colors flex items-center justify-center gap-1.5">
                                                                <Plus class="w-3.5 h-3.5" /> Tambah Kelas
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Kosong -->
                                                <div v-else class="mt-4 p-8 border-2 border-dashed border-slate-200 rounded-2xl text-center">
                                                    <div class="w-10 h-10 mx-auto mb-3 rounded-xl bg-slate-50 text-slate-300 grid place-items-center">
                                                        <BookOpen class="w-5 h-5" />
                                                    </div>
                                                    <p class="text-sm font-bold text-slate-500">Belum ada penugasan mengajar</p>
                                                    <p class="text-xs text-slate-400 mt-1">Centang mata pelajaran di atas, lalu tentukan kelas dan jumlah jam per minggu.</p>
                                                </div>

                                                <p class="mt-3 text-[11px] text-slate-400 leading-relaxed">
                                                    Total jam tiap mata pelajaran dihitung ulang otomatis dari seluruh guru pengampu dan menjadi dasar pembagian jam pada menu Mata Pelajaran serta Generate Jadwal.
                                                </p>
                                            </div>
                                        </section>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer aksi -->
                            <div class="shrink-0 flex flex-col sm:flex-row items-center justify-between gap-3 px-6 lg:px-8 py-4 border-t border-slate-200 bg-white">
                                <p class="flex items-center gap-2 text-sm text-slate-500 font-medium order-2 sm:order-1">
                                    <Check class="w-4 h-4 text-emerald-500 shrink-0" />
                                    Total beban mengajar:
                                    <span class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 text-xs font-black">{{ totalJpForm }} JP/minggu</span>
                                    <span class="hidden md:inline text-xs text-slate-400">• {{ assignments.length }} mapel • {{ totalPenugasanKelas }} penugasan kelas</span>
                                </p>
                                <div class="flex items-center gap-3 order-1 sm:order-2 w-full sm:w-auto">
                                    <button type="button" @click="closeModal"
                                        class="flex-1 sm:flex-none px-6 py-2.5 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                                        Batal
                                    </button>
                                    <button type="submit" :disabled="form.processing"
                                        class="flex-1 sm:flex-none px-6 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors flex items-center justify-center gap-2 disabled:opacity-60 shadow-lg shadow-indigo-200">
                                        <span v-if="form.processing" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                        <Save v-else class="w-4 h-4" />
                                        {{ form.processing ? 'Menyimpan...' : (editTarget ? 'Perbarui Data' : 'Simpan Guru') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </Transition>
            </div>
        </Transition>

        <!-- Modal Import Guru via Excel/CSV -->
        <div v-if="showImportModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden animate-fade-in">
                <div class="flex items-center justify-between p-6 border-b border-slate-100">
                    <h2 class="text-xl font-black text-slate-900 flex items-center gap-2">
                        <FileSpreadsheet class="w-6 h-6 text-emerald-600" />
                        Import Data Guru
                    </h2>
                    <button @click="showImportModal = false" class="p-2 text-slate-400 hover:text-slate-600 bg-slate-50 rounded-xl transition-colors">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <div class="p-6 space-y-5">
                    <!-- Download Template Area -->
                    <div class="p-4 bg-indigo-50/60 border border-indigo-100 rounded-2xl flex items-center justify-between gap-3">
                        <div class="space-y-0.5">
                            <h4 class="text-sm font-bold text-indigo-950">Template Import Excel</h4>
                            <p class="text-xs text-indigo-700/80">Gunakan format resmi agar data terproses dengan benar.</p>
                        </div>
                        <a :href="route('admin.web.guru.template')" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl flex items-center gap-1.5 transition-all shadow-sm shrink-0">
                            <Download class="w-3.5 h-3.5" /> Unduh
                        </a>
                    </div>

                    <!-- Drag and Drop Zone -->
                    <div
                        @dragover.prevent="dragOver = true"
                        @dragleave.prevent="dragOver = false"
                        @drop.prevent="handleDrop"
                        @click="triggerFileSelect"
                        :class="[
                            'border-2 border-dashed rounded-2xl p-8 text-center cursor-pointer transition-all duration-200 flex flex-col items-center justify-center min-h-[160px]',
                            dragOver
                                ? 'border-indigo-500 bg-indigo-50/30'
                                : 'border-slate-200 bg-slate-50/50 hover:border-indigo-400 hover:bg-indigo-50/5'
                        ]"
                    >
                        <input
                            type="file"
                            ref="fileInput"
                            @change="onFileChange"
                            accept=".xlsx, .xls, .csv"
                            class="hidden"
                        />

                        <div v-if="!selectedFile" class="space-y-2">
                            <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mx-auto text-slate-500">
                                <Upload class="w-6 h-6" />
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-800">Klik untuk unggah atau tarik file kemari</p>
                                <p class="text-xs text-slate-400 mt-1">Mendukung format .xlsx, .xls atau .csv (Maks. 10MB)</p>
                            </div>
                        </div>

                        <div v-else class="w-full flex items-center gap-3 p-3 bg-emerald-50 border border-emerald-100 rounded-xl">
                            <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                                <FileSpreadsheet class="w-5 h-5" />
                            </div>
                            <div class="text-left flex-1 min-w-0">
                                <p class="text-sm font-bold text-slate-800 truncate">{{ selectedFile.name }}</p>
                                <p class="text-xs text-slate-400">{{ (selectedFile.size / 1024 / 1024).toFixed(2) }} MB</p>
                            </div>
                            <button type="button" @click.stop="removeSelectedFile" class="p-1.5 hover:bg-emerald-100 text-emerald-700 rounded-lg transition-colors">
                                <X class="w-4 h-4" />
                            </button>
                        </div>
                    </div>

                    <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl text-[11px] text-slate-500 leading-relaxed space-y-1">
                        <p class="font-bold text-slate-700 uppercase tracking-wider text-[10px]">⚠️ Aturan Pengisian:</p>
                        <p>1. Kolom <strong>nip</strong>, <strong>nama</strong>, dan <strong>email</strong> wajib terisi.</p>
                        <p>2. Kolom <strong>email</strong> akan otomatis digunakan sebagai Username login portal guru.</p>
                        <p>3. Password akun guru default diatur sebagai: <code class="bg-slate-200 px-1 py-0.5 rounded font-mono font-bold text-slate-700">guru123</code>.</p>
                    </div>

                    <!-- Footer buttons -->
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showImportModal = false"
                            class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                            Batal
                        </button>
                        <button
                            @click="submitImport"
                            :disabled="!selectedFile || importForm.processing"
                            class="px-5 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors flex items-center gap-2 disabled:opacity-60 shadow-lg shadow-indigo-150"
                        >
                            <Check class="w-4 h-4" />
                            {{ importForm.processing ? 'Memproses...' : 'Mulai Import' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Konfirmasi Hapus Guru -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white w-full max-w-sm rounded-3xl shadow-2xl overflow-hidden text-center">
                <div class="p-8">
                    <div class="w-14 h-14 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center mx-auto mb-5">
                        <AlertTriangle class="w-8 h-8" />
                    </div>
                    <h3 class="text-xl font-black text-slate-900 mb-2">Hapus Data Guru</h3>
                    <p class="text-slate-500 text-sm font-medium leading-relaxed mb-2">
                        Yakin ingin menghapus guru <span class="font-bold text-slate-900">{{ deleteTarget?.nama }}</span>?
                    </p>
                    <div class="bg-rose-50 border border-rose-100 rounded-xl px-4 py-3 mb-6">
                        <p class="font-bold text-rose-700 text-sm">Akun login guru ini juga akan dihapus.</p>
                    </div>
                    <div class="flex gap-3">
                        <button @click="showDeleteModal = false; deleteTarget = null" :disabled="isDeleting"
                            class="flex-1 py-3 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                            Batal
                        </button>
                        <button @click="confirmDelete" :disabled="isDeleting"
                            class="flex-1 py-3 text-sm font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition-colors flex items-center justify-center gap-2">
                            <span v-if="isDeleting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
                            <Trash2 v-else class="w-4 h-4" />
                            {{ isDeleting ? 'Menghapus...' : 'Ya, Hapus' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.18s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}
.modal-panel-enter-active {
    transition: transform 0.24s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.2s ease;
}
.modal-panel-leave-active {
    transition: transform 0.15s ease-in, opacity 0.15s ease-in;
}
.modal-panel-enter-from {
    opacity: 0;
    transform: translateY(16px) scale(0.97);
}
.modal-panel-leave-to {
    opacity: 0;
    transform: translateY(8px) scale(0.98);
}
</style>
