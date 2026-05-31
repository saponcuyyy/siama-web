<script setup>
import AdminWebLayout from '@/Layouts/AdminWebLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    siswas: Object,
    filters: Object,
    stats: Object,
});

// ─── Upload Form ───────────────────────────────────────────────────────────
const importForm = useForm({ file: null, mode: 'tambah' });
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

const submitImport = () => {
    if (!importForm.file) return;
    importForm.post(route('admin.web.kelulusan.import'), {
        onSuccess: () => {
            selectedFile.value = null;
            importForm.reset();
            if (fileInput.value) fileInput.value.value = '';
        },
    });
};

// ─── Search & Filter ──────────────────────────────────────────────────────
const search = ref(props.filters?.search || '');
const filterStatus = ref(props.filters?.status || 'semua');

const applyFilter = () => {
    router.get(route('admin.web.kelulusan.index'), {
        search: search.value,
        status: filterStatus.value,
    }, { preserveState: true, replace: true });
};

// ─── Edit Siswa ───────────────────────────────────────────────────────────
const editingId = ref(null);
const editForm = useForm({ status_lulus: '', keterangan: '' });

const startEdit = (siswa) => {
    editingId.value = siswa.id;
    editForm.status_lulus = siswa.status_lulus;
    editForm.keterangan = siswa.keterangan || '';
};

const saveEdit = (siswa) => {
    editForm.put(route('admin.web.kelulusan.update', siswa.hashid), {
        onSuccess: () => { editingId.value = null; },
    });
};

// ─── Delete ───────────────────────────────────────────────────────────────
const deleteSiswa = (siswa) => {
    if (!confirm(`Hapus data ${siswa.nama}?`)) return;
    router.delete(route('admin.web.kelulusan.destroy', siswa.hashid));
};

const deleteAll = () => {
    if (!confirm('Hapus SEMUA data siswa? Tindakan ini tidak bisa dibatalkan!')) return;
    router.delete(route('admin.web.kelulusan.destroyAll'));
};

// ─── Status helpers ───────────────────────────────────────────────────────
const statusLabel = {
    lulus: { label: 'Lulus', class: 'badge-lulus' },
    tidak_lulus: { label: 'Tidak Lulus', class: 'badge-tidak' },
    ditunda: { label: 'Ditunda', class: 'badge-ditunda' },
};

const formatDate = (d) =>
    d ? new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }) : '-';
</script>

<template>
    <AdminWebLayout>
        <Head title="Manajemen Data Kelulusan" />

        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Page Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl font-black text-slate-900">🎓 Manajemen Data Kelulusan</h1>
                    <p class="text-slate-500 text-sm">Upload dan kelola data kelulusan siswa Tahun Pelajaran 2025/2026</p>
                </div>
                <div class="flex gap-3">
                    <a :href="route('admin.web.kelulusan.template')"
                       class="inline-flex items-center gap-2 bg-emerald-50 text-emerald-700 border border-emerald-200 px-4 py-2 rounded-xl text-sm font-bold hover:bg-emerald-100 transition-all">
                        📥 Download Template
                    </a>
                    <a href="/kelulusan" target="_blank"
                       class="inline-flex items-center gap-2 bg-blue-50 text-blue-700 border border-blue-200 px-4 py-2 rounded-xl text-sm font-bold hover:bg-blue-100 transition-all">
                        🌐 Lihat Halaman Publik
                    </a>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm text-center">
                    <p class="text-3xl font-black text-slate-900">{{ stats.total }}</p>
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400 mt-1">Total Siswa</p>
                </div>
                <div class="bg-emerald-50 rounded-2xl border border-emerald-100 p-5 shadow-sm text-center">
                    <p class="text-3xl font-black text-emerald-600">{{ stats.lulus }}</p>
                    <p class="text-xs font-bold uppercase tracking-widest text-emerald-500 mt-1">Lulus</p>
                </div>
                <div class="bg-red-50 rounded-2xl border border-red-100 p-5 shadow-sm text-center">
                    <p class="text-3xl font-black text-red-600">{{ stats.tidak_lulus }}</p>
                    <p class="text-xs font-bold uppercase tracking-widest text-red-400 mt-1">Tidak Lulus</p>
                </div>
                <div class="bg-amber-50 rounded-2xl border border-amber-100 p-5 shadow-sm text-center">
                    <p class="text-3xl font-black text-amber-600">{{ stats.ditunda }}</p>
                    <p class="text-xs font-bold uppercase tracking-widest text-amber-500 mt-1">Ditunda</p>
                </div>
            </div>

            <!-- Upload Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-2">
                    <span class="text-base font-black text-slate-800">📤 Upload Data Siswa (Excel)</span>
                </div>
                <div class="p-6 space-y-5">
                    <!-- Format Info -->
                    <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 text-sm text-blue-800 space-y-1">
                        <p class="font-bold">📋 Format kolom yang diperlukan di file Excel:</p>
                        <p class="font-mono bg-blue-100 px-3 py-1 rounded-lg inline-block text-xs">nisn | nama | tanggal_lahir | status_lulus | keterangan</p>
                        <p class="text-xs text-blue-600 mt-2">Nilai <strong>status_lulus</strong>: <code>lulus</code>, <code>tidak_lulus</code>, atau <code>ditunda</code>. Format tanggal: <code>YYYY-MM-DD</code></p>
                    </div>

                    <!-- Drop Zone -->
                    <div
                        class="border-2 border-dashed rounded-xl p-8 text-center cursor-pointer transition-all"
                        :class="isDragging ? 'border-blue-400 bg-blue-50' : 'border-slate-200 hover:border-blue-300 hover:bg-slate-50'"
                        @dragover.prevent="isDragging = true"
                        @dragleave="isDragging = false"
                        @drop.prevent="onDrop"
                        @click="fileInput.click()"
                    >
                        <div class="text-4xl mb-2">📂</div>
                        <p class="font-bold text-slate-700">
                            {{ selectedFile ? selectedFile.name : 'Klik atau drag file Excel ke sini' }}
                        </p>
                        <p class="text-xs text-slate-400 mt-1">Format: .xlsx, .xls, .csv — Max 10MB</p>
                        <input ref="fileInput" type="file" accept=".xlsx,.xls,.csv" class="hidden" @change="onFileSelect" />
                    </div>

                    <!-- Mode & Submit -->
                    <div class="flex flex-col sm:flex-row gap-4">
                        <div class="flex-1">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Mode Import</label>
                            <div class="flex gap-3">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" v-model="importForm.mode" value="tambah" class="text-blue-600" />
                                    <span class="text-sm font-semibold text-slate-700">Tambahkan ke data yang ada</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" v-model="importForm.mode" value="ganti" class="text-red-500" />
                                    <span class="text-sm font-semibold text-slate-700">Ganti semua data</span>
                                </label>
                            </div>
                            <p v-if="importForm.mode === 'ganti'" class="text-xs text-red-500 mt-1 font-semibold">⚠️ Mode ini akan menghapus seluruh data siswa yang ada sebelum import.</p>
                        </div>
                        <div class="flex items-end">
                            <button
                                @click="submitImport"
                                :disabled="!selectedFile || importForm.processing"
                                class="bg-blue-600 text-white px-8 py-2.5 rounded-xl font-bold text-sm hover:bg-blue-700 transition-all shadow-sm disabled:opacity-40 disabled:cursor-not-allowed whitespace-nowrap"
                            >
                                {{ importForm.processing ? 'Mengimport...' : '📤 Import Sekarang' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Table Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex flex-col md:flex-row md:items-center gap-4">
                    <span class="text-base font-black text-slate-800 flex-1">📋 Daftar Siswa ({{ siswas.total }} data)</span>
                    <!-- Search & Filter -->
                    <div class="flex gap-3">
                        <input v-model="search" @keyup.enter="applyFilter" type="text" placeholder="Cari nama / NISN..." class="border-slate-200 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 py-2" />
                        <select v-model="filterStatus" @change="applyFilter" class="border-slate-200 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 py-2 font-semibold">
                            <option value="semua">Semua Status</option>
                            <option value="lulus">Lulus</option>
                            <option value="tidak_lulus">Tidak Lulus</option>
                            <option value="ditunda">Ditunda</option>
                        </select>
                        <button @click="applyFilter" class="bg-slate-800 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-slate-700 transition-all">Cari</button>
                    </div>
                    <button @click="deleteAll" class="text-red-500 text-sm font-bold hover:text-red-700 transition-colors">🗑 Hapus Semua</button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="text-left px-5 py-3 font-black text-slate-400 uppercase text-xs tracking-wider">No</th>
                                <th class="text-left px-5 py-3 font-black text-slate-400 uppercase text-xs tracking-wider">NISN</th>
                                <th class="text-left px-5 py-3 font-black text-slate-400 uppercase text-xs tracking-wider">Nama Siswa</th>
                                <th class="text-left px-5 py-3 font-black text-slate-400 uppercase text-xs tracking-wider">Tgl Lahir</th>
                                <th class="text-left px-5 py-3 font-black text-slate-400 uppercase text-xs tracking-wider">Status</th>
                                <th class="text-left px-5 py-3 font-black text-slate-400 uppercase text-xs tracking-wider">Keterangan</th>
                                <th class="text-center px-5 py-3 font-black text-slate-400 uppercase text-xs tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="siswas.data.length === 0">
                                <td colspan="7" class="text-center py-16 text-slate-400 font-semibold">
                                    Belum ada data siswa. Silakan upload file Excel.
                                </td>
                            </tr>
                            <template v-for="(siswa, index) in siswas.data" :key="siswa.id">
                                <!-- View Row -->
                                <tr v-if="editingId !== siswa.id" class="hover:bg-slate-50 transition-colors">
                                    <td class="px-5 py-3 text-slate-400 font-semibold">{{ siswas.from + index }}</td>
                                    <td class="px-5 py-3 font-mono font-bold text-slate-700">{{ siswa.nisn }}</td>
                                    <td class="px-5 py-3 font-semibold text-slate-900">{{ siswa.nama }}</td>
                                    <td class="px-5 py-3 text-slate-500">{{ formatDate(siswa.tanggal_lahir) }}</td>
                                    <td class="px-5 py-3">
                                        <span :class="['badge', statusLabel[siswa.status_lulus]?.class]">
                                            {{ statusLabel[siswa.status_lulus]?.label }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-slate-400 text-xs italic max-w-[200px] truncate">{{ siswa.keterangan || '-' }}</td>
                                    <td class="px-5 py-3">
                                        <div class="flex items-center justify-center gap-2">
                                            <button @click="startEdit(siswa)" class="text-blue-500 hover:text-blue-700 text-xs font-bold">Edit</button>
                                            <button @click="deleteSiswa(siswa)" class="text-red-400 hover:text-red-600 text-xs font-bold">Hapus</button>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Edit Row -->
                                <tr v-else class="bg-blue-50">
                                    <td class="px-5 py-3 text-slate-400">{{ siswas.from + index }}</td>
                                    <td class="px-5 py-3 font-mono font-bold">{{ siswa.nisn }}</td>
                                    <td class="px-5 py-3 font-semibold">{{ siswa.nama }}</td>
                                    <td class="px-5 py-3 text-slate-500">{{ formatDate(siswa.tanggal_lahir) }}</td>
                                    <td class="px-5 py-3">
                                        <select v-model="editForm.status_lulus" class="border-slate-300 rounded-lg text-xs py-1">
                                            <option value="lulus">Lulus</option>
                                            <option value="tidak_lulus">Tidak Lulus</option>
                                            <option value="ditunda">Ditunda</option>
                                        </select>
                                    </td>
                                    <td class="px-5 py-3">
                                        <input v-model="editForm.keterangan" type="text" placeholder="Keterangan..." class="border-slate-300 rounded-lg text-xs py-1 w-full" />
                                    </td>
                                    <td class="px-5 py-3">
                                        <div class="flex items-center justify-center gap-2">
                                            <button @click="saveEdit(siswa)" class="text-emerald-600 hover:text-emerald-800 text-xs font-bold">Simpan</button>
                                            <button @click="editingId = null" class="text-slate-400 hover:text-slate-600 text-xs font-bold">Batal</button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <Pagination :data="siswas" />
            </div>
        </div>
    </AdminWebLayout>
</template>

<style scoped>
@reference "../../../../css/app.css";

.badge {
    display: inline-flex;
    align-items: center;
    padding: 0.2rem 0.7rem;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.03em;
}
.badge-lulus   { background: #d1fae5; color: #065f46; }
.badge-tidak   { background: #fee2e2; color: #991b1b; }
.badge-ditunda { background: #fef3c7; color: #92400e; }
</style>
