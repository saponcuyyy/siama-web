<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { UserCheck, Plus, Search, Pencil, Trash2, X, Check, Users, Info, Upload, Download, FileSpreadsheet } from 'lucide-vue-next';
import dayjs from 'dayjs';

const props = defineProps({
    siswaList: Object,
    filters: Object,
    rombelList: Array,
});

const search = ref(props.filters.search || '');
const filterRombel = ref(props.filters.rombel_id || '');
const showModal = ref(false);
const showUploadModal = ref(false);
const editTarget = ref(null);

const form = useForm({
    nama: '',
    nisn: '',
    tanggal_lahir: '',
    agama: '',
    rombel_id: '',
});

const openCreate = () => {
    editTarget.value = null;
    form.reset();
    showModal.value = true;
};

const openEdit = (siswa) => {
    editTarget.value = siswa;
    form.nama = siswa.nama;
    form.nisn = siswa.nisn;
    form.tanggal_lahir = siswa.tanggal_lahir;
    form.agama = siswa.agama || '';
    form.rombel_id = siswa.rombel_id || '';
    showModal.value = true;
};

const handleSearch = () => {
    router.get(route('admin.web.siswa.index'), {
        search: search.value,
        rombel_id: filterRombel.value,
    }, { preserveState: true, replace: true });
};

const submitForm = () => {
    if (editTarget.value) {
        form.put(route('admin.web.siswa.update', editTarget.value.hashid), {
            onSuccess: () => { showModal.value = false; form.reset(); },
        });
    } else {
        form.post(route('admin.web.siswa.store'), {
            onSuccess: () => { showModal.value = false; form.reset(); },
        });
    }
};

const hapus = (siswa) => {
    if (confirm(`Hapus data siswa "${siswa.nama}"? Akun login siswa ini juga akan dihapus secara permanen.`)) {
        router.delete(route('admin.web.siswa.destroy', siswa.hashid));
    }
};

const formatDate = (date) => {
    if (!date) return '-';
    return dayjs(date).format('DD MMM YYYY');
};

const getInitials = (name) => {
    return name.split(' ').slice(0, 2).map(n => n[0]).join('').toUpperCase();
};

const avatarColors = ['from-indigo-500 to-purple-600', 'from-emerald-500 to-teal-600', 'from-amber-500 to-orange-600', 'from-rose-500 to-pink-600', 'from-cyan-500 to-blue-600'];
const getColor = (id) => avatarColors[id % avatarColors.length];

// ─── Upload ─────────────────────────────────────────────────────────────────
const importForm = useForm({ file: null, rombel_id: '' });
const fileInput = ref(null);
const isDragging = ref(false);
const selectedFile = ref(null);

const onFileSelect = (e) => {
    const file = e.target.files?.[0] || e.dataTransfer?.files?.[0];
    if (file) {
        selectedFile.value = file;
        importForm.file = file;
    }
};

const onDrop = (e) => {
    isDragging.value = false;
    onFileSelect(e);
};

const openUploadModal = () => {
    selectedFile.value = null;
    importForm.reset();
    if (fileInput.value) fileInput.value.value = '';
    showUploadModal.value = true;
};

const submitImport = () => {
    if (!importForm.file || !importForm.rombel_id) return;
    importForm.post(route('admin.web.siswa.import'), {
        preserveState: true,
        onSuccess: () => {
            selectedFile.value = null;
            importForm.reset();
            if (fileInput.value) fileInput.value.value = '';
            showUploadModal.value = false;
        },
    });
};


</script>

<template>
    <Head title="Manajemen Data Siswa" />
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between gap-4 items-start sm:items-center">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                        <UserCheck class="w-7 h-7 text-indigo-600" />
                        Data Siswa
                    </h1>
                    <p class="text-slate-500 font-medium mt-1">Kelola data siswa aktif beserta akun akses ujian CBT.</p>
                </div>
                <div class="flex gap-3">
                    <button @click="openUploadModal"
                        class="px-5 py-2.5 bg-white hover:bg-slate-50 text-indigo-600 text-sm font-bold rounded-xl flex items-center gap-2 transition-colors border border-indigo-200 shadow-sm">
                        <Upload class="w-5 h-5" /> Import Excel
                    </button>
                    <a :href="route('admin.web.siswa.template')"
                       class="px-5 py-2.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-sm font-bold rounded-xl flex items-center gap-2 transition-colors border border-emerald-200">
                        <Download class="w-5 h-5" /> Template
                    </a>
                    <button @click="openCreate" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl flex items-center gap-2 transition-colors shadow-lg shadow-indigo-200">
                        <Plus class="w-5 h-5" /> Tambah Siswa
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
                    Saat menambahkan siswa baru, sistem akan <strong>otomatis membuat akun login</strong>. Username: <code class="bg-white px-1.5 py-0.5 rounded font-mono text-xs">{NISN}</code> dan password default menggunakan tanggal lahir dengan format <code class="bg-white px-1.5 py-0.5 rounded font-mono text-xs">ddmmyyyy*</code> (contoh Lahir 01 Dec 2010 -> password: <code class="bg-white px-1.5 py-0.5 rounded font-mono text-xs">01122010*</code>).
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row gap-4 items-center">
                <div class="relative w-full sm:w-80">
                    <input type="text" v-model="search" @keyup.enter="handleSearch"
                        placeholder="Cari nama atau NISN..."
                        class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600 text-sm" />
                    <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
                </div>
                <select v-model="filterRombel" @change="handleSearch"
                    class="w-full sm:w-64 px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600 text-sm font-medium">
                    <option value="">Semua Rombel</option>
                    <option v-for="r in rombelList" :key="r.id" :value="r.id">
                        {{ r.nama }} ({{ r.tahun_ajaran?.nama }})
                    </option>
                </select>
                <button @click="handleSearch" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl transition-colors shrink-0">
                    Cari
                </button>
            </div>



            <!-- Table -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 bg-slate-50/50 border-b border-slate-200 flex items-center justify-between">
                    <p class="text-sm font-bold text-slate-600">
                        Total: <span class="text-indigo-600">{{ siswaList.total }}</span> siswa
                    </p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider">
                                <th class="p-4 pl-6">Siswa</th>
                                <th class="p-4">NISN</th>
                                <th class="p-4">Tanggal Lahir</th>
                                <th class="p-4">Agama</th>
                                <th class="p-4">Rombel</th>
                                <th class="p-4 pr-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <tr v-for="siswa in siswaList.data" :key="siswa.id"
                                class="hover:bg-slate-50/50 transition-colors">
                                <td class="p-4 pl-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-black text-sm bg-gradient-to-br shrink-0"
                                            :class="getColor(siswa.id)">
                                            {{ getInitials(siswa.nama) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900">{{ siswa.nama }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span class="font-mono font-bold text-slate-700 bg-slate-100 px-2.5 py-1 rounded-lg text-xs">{{ siswa.nisn }}</span>
                                </td>
                                <td class="p-4 font-medium text-slate-600">
                                    {{ formatDate(siswa.tanggal_lahir) }}
                                </td>
                                <td class="p-4 font-medium text-slate-600">
                                    {{ siswa.agama || '-' }}
                                </td>
                                <td class="p-4">
                                    <span v-if="siswa.rombel" class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-indigo-50 text-indigo-700 font-bold rounded-lg text-xs border border-indigo-100">
                                        {{ siswa.rombel.nama }}
                                    </span>
                                    <span v-else class="text-slate-400 text-xs">Belum ada rombel</span>
                                </td>
                                <td class="p-4 pr-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="openEdit(siswa)" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-colors">
                                            <Pencil class="w-4 h-4" />
                                        </button>
                                        <button @click="hapus(siswa)" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-colors">
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="siswaList.data.length === 0">
                                <td colspan="6" class="p-12 text-center">
                                    <div class="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-3 text-slate-400">
                                        <Users class="w-6 h-6" />
                                    </div>
                                    <p class="font-bold text-slate-700">Tidak ada data siswa</p>
                                    <p class="text-slate-500 text-xs mt-1">Coba ubah filter atau tambahkan siswa baru.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <Pagination :data="siswaList" />
            </div>
        </div>

        <!-- Modal Tambah / Edit Siswa -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between p-6 border-b border-slate-100">
                    <h2 class="text-xl font-black text-slate-900">{{ editTarget ? 'Edit Data Siswa' : 'Tambah Siswa Baru' }}</h2>
                    <button @click="showModal = false" class="p-2 text-slate-400 hover:text-slate-600 bg-slate-50 rounded-xl transition-colors">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Nama Lengkap</label>
                        <input type="text" v-model="form.nama" required
                            placeholder="Nama lengkap siswa"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600" />
                        <p v-if="form.errors.nama" class="text-xs text-rose-500 mt-1 font-bold">{{ form.errors.nama }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">NISN</label>
                        <input type="text" v-model="form.nisn" required
                            placeholder="10 digit NISN"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600 font-mono" />
                        <p v-if="form.errors.nisn" class="text-xs text-rose-500 mt-1 font-bold">{{ form.errors.nisn }}</p>
                        <p v-if="!editTarget" class="text-xs text-slate-400 mt-1">Username login: <span class="font-mono font-bold">{{ form.nisn || 'nisn' }}</span></p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Tanggal Lahir</label>
                        <input type="date" v-model="form.tanggal_lahir" required
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600" />
                        <p v-if="form.errors.tanggal_lahir" class="text-xs text-rose-500 mt-1 font-bold">{{ form.errors.tanggal_lahir }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Agama</label>
                        <select v-model="form.agama"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600">
                            <option value="">-- Pilih Agama --</option>
                            <option value="Islam">Islam</option>
                            <option value="Kristen">Kristen</option>
                            <option value="Katolik">Katolik</option>
                            <option value="Hindu">Hindu</option>
                            <option value="Buddha">Buddha</option>
                            <option value="Konghucu">Konghucu</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                        <p v-if="form.errors.agama" class="text-xs text-rose-500 mt-1 font-bold">{{ form.errors.agama }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Rombel / Kelas</label>
                        <select v-model="form.rombel_id" required
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600">
                            <option value="">-- Pilih Rombel --</option>
                            <option v-for="r in rombelList" :key="r.id" :value="r.id">
                                {{ r.nama }} ({{ r.tahun_ajaran?.nama }})
                            </option>
                        </select>
                        <p v-if="form.errors.rombel_id" class="text-xs text-rose-500 mt-1 font-bold">{{ form.errors.rombel_id }}</p>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showModal = false"
                            class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                            Batal
                        </button>
                        <button type="submit" :disabled="form.processing"
                            class="px-5 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors flex items-center gap-2 disabled:opacity-60">
                            <Check class="w-4 h-4" /> {{ form.processing ? 'Menyimpan...' : (editTarget ? 'Perbarui Data' : 'Simpan Siswa') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <!-- Modal Import Excel -->
        <div v-if="showUploadModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between p-6 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <Upload class="w-5 h-5 text-indigo-600" />
                        <h2 class="text-xl font-black text-slate-900">Import Data Siswa</h2>
                    </div>
                    <button @click="showUploadModal = false; selectedFile = null; importForm.reset(); if(fileInput.value) fileInput.value.value = ''" class="p-2 text-slate-400 hover:text-slate-600 bg-slate-50 rounded-xl transition-colors">
                        <X class="w-5 h-5" />
                    </button>
                </div>
                <div class="p-6 space-y-5">
                    <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 text-sm text-blue-800 space-y-1">
                        <p class="font-bold">Format kolom di file Excel:</p>
                        <p class="font-mono bg-blue-100 px-3 py-1 rounded-lg inline-block text-xs">nisn | nama | tanggal_lahir | agama</p>
                        <p class="text-xs text-blue-600 mt-2">Format tanggal: <code>YYYY-MM-DD</code>. Sistem otomatis membuat akun login (Username = NISN, Password = <code>ddmmyyyy*</code>).</p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Pilih Rombel Tujuan</label>
                        <select v-model="importForm.rombel_id" required
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600 text-sm font-medium">
                            <option value="">-- Pilih Rombel --</option>
                            <option v-for="r in rombelList" :key="r.id" :value="r.id">
                                {{ r.nama }} ({{ r.tahun_ajaran?.nama }})
                            </option>
                        </select>
                    </div>

                    <div
                        class="border-2 border-dashed rounded-xl p-8 text-center cursor-pointer transition-all"
                        :class="isDragging ? 'border-indigo-400 bg-indigo-50' : 'border-slate-200 hover:border-indigo-300 hover:bg-slate-50'"
                        @dragover.prevent="isDragging = true"
                        @dragleave="isDragging = false"
                        @drop.prevent="onDrop"
                        @click="fileInput.click()"
                    >
                        <FileSpreadsheet class="w-10 h-10 text-slate-400 mx-auto mb-2" />
                        <p class="font-bold text-slate-700">
                            {{ selectedFile ? selectedFile.name : 'Klik atau drag file Excel ke sini' }}
                        </p>
                        <p class="text-xs text-slate-400 mt-1">Format: .xlsx, .xls, .csv — Max 10MB</p>
                        <input ref="fileInput" type="file" accept=".xlsx,.xls,.csv" class="hidden" @change="onFileSelect" />
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showUploadModal = false; selectedFile = null; importForm.reset(); if(fileInput.value) fileInput.value.value = ''"
                            class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                            Batal
                        </button>
                        <button
                            @click="submitImport"
                            :disabled="!selectedFile || !importForm.rombel_id || importForm.processing"
                            class="px-5 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors flex items-center gap-2 disabled:opacity-60"
                        >
                            <Upload class="w-4 h-4" />
                            {{ importForm.processing ? 'Mengimport...' : 'Import Sekarang' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
