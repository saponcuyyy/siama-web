<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { BookOpen, Plus, Search, Pencil, Trash2, X, Check, AlertTriangle, BookMarked, Filter } from 'lucide-vue-next';

const props = defineProps({
    mapelList: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const filterTingkat = ref(props.filters.tingkat || '');
const filterJurusan = ref(props.filters.jurusan || '');

const tingkatList = ['X', 'XI', 'XII'];
const jurusanList = ['IPA', 'IPS'];

// ─── Create Modal ──────────────────────────────
const showCreateModal = ref(false);
const createForm = useForm({ nama: '', kode: '', tingkat: 'X', jurusan: '' });

const submitCreate = () => {
    createForm.post(route('admin.ujian.mata-pelajaran.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
            createForm.tingkat = 'X';
            createForm.jurusan = '';
        },
    });
};

// ─── Edit Modal ────────────────────────────────
const showEditModal = ref(false);
const editTarget = ref(null);
const editForm = useForm({ nama: '', kode: '', tingkat: '', jurusan: '' });

const openEdit = (mapel) => {
    editTarget.value = mapel;
    editForm.nama = mapel.nama;
    editForm.kode = mapel.kode;
    editForm.tingkat = mapel.tingkat;
    editForm.jurusan = mapel.jurusan || '';
    showEditModal.value = true;
};

const submitEdit = () => {
    editForm.put(route('admin.ujian.mata-pelajaran.update', editTarget.value.hashid), {
        onSuccess: () => {
            showEditModal.value = false;
            editForm.reset();
        },
    });
};

// ─── Delete Confirm ────────────────────────────
const showDeleteModal = ref(false);
const deleteTarget = ref(null);
const isDeleting = ref(false);

const openDelete = (mapel) => {
    deleteTarget.value = mapel;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    isDeleting.value = true;
    router.delete(route('admin.ujian.mata-pelajaran.destroy', deleteTarget.value.hashid), {
        onSuccess: () => {
            showDeleteModal.value = false;
            deleteTarget.value = null;
        },
        onFinish: () => { isDeleting.value = false; },
    });
};

// ─── Search & Filter ───────────────────────────
const handleSearch = () => {
    router.get(route('admin.ujian.mata-pelajaran.index'), {
        search: search.value,
        tingkat: filterTingkat.value,
        jurusan: filterJurusan.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    search.value = '';
    filterTingkat.value = '';
    filterJurusan.value = '';
    handleSearch();
};

const hasActiveFilters = computed(() => search.value || filterTingkat.value || filterJurusan.value);
</script>

<template>
    <Head title="Mata Pelajaran" />

    <AuthenticatedLayout>
        <div class="max-w-6xl mx-auto space-y-6">

            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between gap-4 items-start sm:items-center">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                        <BookOpen class="w-7 h-7 text-indigo-600" />
                        Mata Pelajaran
                    </h1>
                    <p class="text-slate-500 font-medium mt-1">Kelola daftar mata pelajaran per kelas dan per jurusan.</p>
                </div>
                <button
                    @click="showCreateModal = true"
                    class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl flex items-center gap-2 transition-colors shadow-lg shadow-indigo-200"
                >
                    <Plus class="w-5 h-5" /> Tambah Mata Pelajaran
                </button>
            </div>

            <!-- Search & Filter -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-wrap gap-3 items-center">
                <div class="relative flex-1 min-w-[200px] max-w-sm">
                    <input
                        type="text"
                        v-model="search"
                        @keyup.enter="handleSearch"
                        placeholder="Cari nama atau kode..."
                        class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600 transition-colors text-sm font-medium"
                    >
                    <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
                </div>
                <select
                    v-model="filterTingkat"
                    @change="handleSearch"
                    class="px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:ring-indigo-600 focus:border-indigo-600"
                >
                    <option value="">Semua Kelas</option>
                    <option v-for="t in tingkatList" :key="t" :value="t">Kelas {{ t }}</option>
                </select>
                <select
                    v-model="filterJurusan"
                    @change="handleSearch"
                    class="px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:ring-indigo-600 focus:border-indigo-600"
                >
                    <option value="">Semua Jurusan</option>
                    <option v-for="j in jurusanList" :key="j" :value="j">{{ j }}</option>
                </select>
                <button @click="handleSearch" class="px-4 py-2.5 bg-slate-900 hover:bg-slate-700 text-white text-sm font-bold rounded-xl transition-colors">
                    <Search class="w-4 h-4" />
                </button>
                <button
                    v-if="hasActiveFilters"
                    @click="clearFilters"
                    class="px-3 py-2.5 text-sm font-bold text-slate-500 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors"
                >
                    <X class="w-4 h-4" />
                </button>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider">
                                <th class="p-4 pl-6 w-8">#</th>
                                <th class="p-4">Kode</th>
                                <th class="p-4">Nama Mata Pelajaran</th>
                                <th class="p-4 text-center">Kelas</th>
                                <th class="p-4 text-center">Jurusan</th>
                                <th class="p-4 text-center">Bank Soal</th>
                                <th class="p-4 text-center">Paket Ujian</th>
                                <th class="p-4 pr-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <tr
                                v-for="(mapel, i) in mapelList.data"
                                :key="mapel.id"
                                class="hover:bg-slate-50/60 transition-colors"
                            >
                                <td class="p-4 pl-6 text-slate-400 font-bold text-xs">
                                    {{ (mapelList.current_page - 1) * mapelList.per_page + i + 1 }}
                                </td>
                                <td class="p-4">
                                    <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 font-black text-xs rounded-lg border border-indigo-100 uppercase tracking-wider">
                                        {{ mapel.kode }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 bg-slate-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                            <BookMarked class="w-4 h-4 text-slate-500" />
                                        </div>
                                        <span class="font-bold text-slate-900">{{ mapel.nama }}</span>
                                    </div>
                                </td>
                                <td class="p-4 text-center">
                                    <span v-if="mapel.tingkat" class="px-2.5 py-1 text-xs font-black rounded-lg border"
                                        :class="{
                                            'bg-purple-50 text-purple-700 border-purple-100': mapel.tingkat === 'X',
                                            'bg-sky-50 text-sky-700 border-sky-100': mapel.tingkat === 'XI',
                                            'bg-emerald-50 text-emerald-700 border-emerald-100': mapel.tingkat === 'XII',
                                        }"
                                    >
                                        {{ mapel.tingkat }}
                                    </span>
                                    <span v-else class="text-slate-400 text-xs">-</span>
                                </td>
                                <td class="p-4 text-center">
                                    <span v-if="mapel.jurusan" class="px-2.5 py-1 bg-amber-50 text-amber-700 font-black text-xs rounded-lg border border-amber-100">
                                        {{ mapel.jurusan }}
                                    </span>
                                    <span v-else class="px-2.5 py-1 bg-slate-100 text-slate-500 font-bold text-xs rounded-lg">
                                        Umum
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 font-black text-xs rounded-lg border border-emerald-100">
                                        {{ mapel.bank_soal_count }}
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="px-2.5 py-1 bg-sky-50 text-sky-700 font-black text-xs rounded-lg border border-sky-100">
                                        {{ mapel.paket_ujian_count }}
                                    </span>
                                </td>
                                <td class="p-4 pr-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            @click="openEdit(mapel)"
                                            class="p-2 text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors"
                                            title="Edit"
                                        >
                                            <Pencil class="w-4 h-4" />
                                        </button>
                                        <button
                                            @click="openDelete(mapel)"
                                            class="p-2 text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg transition-colors"
                                            title="Hapus"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="mapelList.data.length === 0">
                                <td colspan="8" class="p-12 text-center">
                                    <BookOpen class="w-12 h-12 text-slate-300 mx-auto mb-3" />
                                    <p class="text-slate-500 font-bold">Tidak ada mata pelajaran ditemukan.</p>
                                    <p class="text-slate-400 text-sm mt-1">Sesuaikan filter atau tambahkan mata pelajaran baru.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="mapelList.links.length > 3" class="p-4 border-t border-slate-100 flex justify-center">
                    <div class="flex flex-wrap gap-1">
                        <template v-for="(link, k) in mapelList.links" :key="k">
                            <a
                                v-if="link.url"
                                :href="link.url"
                                class="px-3 py-1.5 rounded-lg text-sm font-bold transition-colors"
                                :class="link.active ? 'bg-indigo-600 text-white' : 'bg-slate-50 text-slate-600 hover:bg-slate-100'"
                                v-html="link.label"
                            />
                            <span v-else class="px-3 py-1.5 text-sm text-slate-400" v-html="link.label" />
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- ─── Modal: Tambah Mata Pelajaran ─────────────────────── -->
        <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between p-6 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center">
                            <Plus class="w-5 h-5" />
                        </div>
                        <h2 class="text-lg font-black text-slate-900">Tambah Mata Pelajaran</h2>
                    </div>
                    <button @click="showCreateModal = false; createForm.reset()" class="p-2 text-slate-400 hover:text-slate-600 bg-slate-50 rounded-xl transition-colors">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submitCreate" class="p-6 space-y-5">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">
                            Nama Mata Pelajaran <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            v-model="createForm.nama"
                            required
                            placeholder="contoh: Matematika"
                            class="w-full px-4 py-2.5 bg-slate-50 border rounded-xl focus:ring-indigo-600 focus:border-indigo-600 font-medium transition-colors"
                            :class="createForm.errors.nama ? 'border-rose-400 bg-rose-50' : 'border-slate-200'"
                        >
                        <p v-if="createForm.errors.nama" class="text-rose-600 text-xs font-bold mt-1.5">{{ createForm.errors.nama }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">
                            Kode Mata Pelajaran <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            v-model="createForm.kode"
                            required
                            placeholder="contoh: MTK"
                            class="w-full px-4 py-2.5 bg-slate-50 border rounded-xl focus:ring-indigo-600 focus:border-indigo-600 font-medium uppercase transition-colors"
                            :class="createForm.errors.kode ? 'border-rose-400 bg-rose-50' : 'border-slate-200'"
                        >
                        <p v-if="createForm.errors.kode" class="text-rose-600 text-xs font-bold mt-1.5">{{ createForm.errors.kode }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">
                                Kelas <span class="text-rose-500">*</span>
                            </label>
                            <select
                                v-model="createForm.tingkat"
                                required
                                class="w-full px-4 py-2.5 bg-slate-50 border rounded-xl focus:ring-indigo-600 focus:border-indigo-600 font-medium transition-colors"
                                :class="createForm.errors.tingkat ? 'border-rose-400 bg-rose-50' : 'border-slate-200'"
                            >
                                <option value="X">X</option>
                                <option value="XI">XI</option>
                                <option value="XII">XII</option>
                            </select>
                            <p v-if="createForm.errors.tingkat" class="text-rose-600 text-xs font-bold mt-1.5">{{ createForm.errors.tingkat }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">
                                Jurusan
                            </label>
                            <select
                                v-model="createForm.jurusan"
                                class="w-full px-4 py-2.5 bg-slate-50 border rounded-xl focus:ring-indigo-600 focus:border-indigo-600 font-medium transition-colors"
                                :class="createForm.errors.jurusan ? 'border-rose-400 bg-rose-50' : 'border-slate-200'"
                            >
                                <option value="">Umum</option>
                                <option value="IPA">IPA</option>
                                <option value="IPS">IPS</option>
                            </select>
                            <p v-if="createForm.errors.jurusan" class="text-rose-600 text-xs font-bold mt-1.5">{{ createForm.errors.jurusan }}</p>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button
                            type="button"
                            @click="showCreateModal = false; createForm.reset()"
                            class="flex-1 py-2.5 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="createForm.processing"
                            class="flex-1 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors flex items-center justify-center gap-2 shadow-lg shadow-indigo-100"
                        >
                            <span v-if="createForm.processing" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
                            <Check v-else class="w-4 h-4" />
                            {{ createForm.processing ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ─── Modal: Edit Mata Pelajaran ───────────────────────── -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between p-6 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center">
                            <Pencil class="w-5 h-5" />
                        </div>
                        <div>
                            <h2 class="text-lg font-black text-slate-900">Edit Mata Pelajaran</h2>
                            <p class="text-xs text-slate-500 font-medium">Mengubah data: <span class="font-bold text-slate-700">{{ editTarget?.kode }}</span></p>
                        </div>
                    </div>
                    <button @click="showEditModal = false" class="p-2 text-slate-400 hover:text-slate-600 bg-slate-50 rounded-xl transition-colors">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submitEdit" class="p-6 space-y-5">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">
                            Nama Mata Pelajaran <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            v-model="editForm.nama"
                            required
                            placeholder="contoh: Matematika"
                            class="w-full px-4 py-2.5 bg-slate-50 border rounded-xl focus:ring-indigo-600 focus:border-indigo-600 font-medium transition-colors"
                            :class="editForm.errors.nama ? 'border-rose-400 bg-rose-50' : 'border-slate-200'"
                        >
                        <p v-if="editForm.errors.nama" class="text-rose-600 text-xs font-bold mt-1.5">{{ editForm.errors.nama }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">
                            Kode Mata Pelajaran <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            v-model="editForm.kode"
                            required
                            placeholder="contoh: MTK"
                            class="w-full px-4 py-2.5 bg-slate-50 border rounded-xl focus:ring-indigo-600 focus:border-indigo-600 font-medium uppercase transition-colors"
                            :class="editForm.errors.kode ? 'border-rose-400 bg-rose-50' : 'border-slate-200'"
                        >
                        <p v-if="editForm.errors.kode" class="text-rose-600 text-xs font-bold mt-1.5">{{ editForm.errors.kode }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">
                                Kelas <span class="text-rose-500">*</span>
                            </label>
                            <select
                                v-model="editForm.tingkat"
                                required
                                class="w-full px-4 py-2.5 bg-slate-50 border rounded-xl focus:ring-indigo-600 focus:border-indigo-600 font-medium transition-colors"
                                :class="editForm.errors.tingkat ? 'border-rose-400 bg-rose-50' : 'border-slate-200'"
                            >
                                <option value="X">X</option>
                                <option value="XI">XI</option>
                                <option value="XII">XII</option>
                            </select>
                            <p v-if="editForm.errors.tingkat" class="text-rose-600 text-xs font-bold mt-1.5">{{ editForm.errors.tingkat }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">
                                Jurusan
                            </label>
                            <select
                                v-model="editForm.jurusan"
                                class="w-full px-4 py-2.5 bg-slate-50 border rounded-xl focus:ring-indigo-600 focus:border-indigo-600 font-medium transition-colors"
                                :class="editForm.errors.jurusan ? 'border-rose-400 bg-rose-50' : 'border-slate-200'"
                            >
                                <option value="">Umum</option>
                                <option value="IPA">IPA</option>
                                <option value="IPS">IPS</option>
                            </select>
                            <p v-if="editForm.errors.jurusan" class="text-rose-600 text-xs font-bold mt-1.5">{{ editForm.errors.jurusan }}</p>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button
                            type="button"
                            @click="showEditModal = false"
                            class="flex-1 py-2.5 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="editForm.processing"
                            class="flex-1 py-2.5 text-sm font-bold text-white bg-amber-500 hover:bg-amber-600 rounded-xl transition-colors flex items-center justify-center gap-2 shadow-lg shadow-amber-100"
                        >
                            <span v-if="editForm.processing" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
                            <Check v-else class="w-4 h-4" />
                            {{ editForm.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ─── Modal: Konfirmasi Hapus ──────────────────────────── -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white w-full max-w-sm rounded-3xl shadow-2xl overflow-hidden text-center">
                <div class="p-8">
                    <div class="w-14 h-14 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center mx-auto mb-5">
                        <AlertTriangle class="w-8 h-8" />
                    </div>
                    <h3 class="text-xl font-black text-slate-900 mb-2">Hapus Mata Pelajaran?</h3>
                    <p class="text-slate-500 text-sm font-medium leading-relaxed mb-2">
                        Anda akan menghapus mata pelajaran:
                    </p>
                    <div class="bg-rose-50 border border-rose-100 rounded-xl px-4 py-3 mb-6">
                        <p class="font-black text-rose-700">{{ deleteTarget?.nama }}</p>
                        <p class="text-rose-500 text-xs font-bold uppercase tracking-wider">{{ deleteTarget?.kode }}</p>
                    </div>
                    <p class="text-slate-400 text-xs mb-6">
                        Data ini tidak akan langsung dihapus permanen, namun tidak akan muncul dalam pilihan baru. Bank soal & paket ujian yang menggunakan mapel ini tidak terpengaruh.
                    </p>
                    <div class="flex gap-3">
                        <button
                            @click="showDeleteModal = false"
                            class="flex-1 py-3 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors"
                        >
                            Batal
                        </button>
                        <button
                            @click="confirmDelete"
                            :disabled="isDeleting"
                            class="flex-1 py-3 text-sm font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition-colors flex items-center justify-center gap-2"
                        >
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
