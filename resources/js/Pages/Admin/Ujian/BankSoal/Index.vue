<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import { 
    Database, Plus, Search, Eye, FileText, Check, X, BookOpen, Trash2, AlertTriangle
} from 'lucide-vue-next';

const props = defineProps({
    bankSoalList: Object,
    filters: Object,
    mapelList: Array,
});

const mapelOptions = computed(() =>
    props.mapelList.map(m => ({
        value: m.id,
        label: `${m.nama} (${m.kode}) — Kelas ${m.tingkat} ${m.jurusan || 'Umum'}`,
        searchText: `${m.nama} ${m.kode} ${m.tingkat} ${m.jurusan || 'Umum'}`,
    }))
);

const search = ref(props.filters.search || '');
const showModal = ref(false);

const form = useForm({
    mata_pelajaran_id: '',
    tingkat: '',
    judul: '',
    deskripsi: '',
});

const handleSearch = () => {
    router.get(route('admin.ujian.bank-soal.index'), { search: search.value }, { preserveState: true, replace: true });
};

const submitForm = () => {
    form.post(route('admin.ujian.bank-soal.store'), {
        onSuccess: () => {
            showModal.value = false;
            form.reset();
        }
    });
};

// ─── Delete Confirm ────────────────────────────
const showDeleteModal = ref(false);
const deleteTarget = ref(null);
const isDeleting = ref(false);

const openDelete = (bank) => {
    deleteTarget.value = bank;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    isDeleting.value = true;
    router.delete(route('admin.ujian.bank-soal.destroy', deleteTarget.value.hashid), {
        onSuccess: () => {
            showDeleteModal.value = false;
            deleteTarget.value = null;
        },
        onFinish: () => { isDeleting.value = false; },
    });
};
</script>

<template>
    <Head title="Manajemen Bank Soal" />
    
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between gap-4 items-start sm:items-center">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                        <Database class="w-7 h-7 text-indigo-600" />
                        Bank Soal
                    </h1>
                    <p class="text-slate-500 font-medium mt-1">Kelola repositori soal untuk digunakan pada ujian.</p>
                </div>
                <button @click="showModal = true" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl flex items-center gap-2 transition-colors shadow-lg shadow-indigo-200">
                    <Plus class="w-5 h-5" /> Buat Bank Soal
                </button>
            </div>

            <!-- Filters -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row gap-4 items-center justify-between">
                <div class="relative w-full sm:w-96">
                    <input 
                        type="text" 
                        v-model="search" 
                        @keyup.enter="handleSearch"
                        placeholder="Cari judul atau mata pelajaran..." 
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
                                <th class="p-4 pl-6">Tingkat & Judul</th>
                                <th class="p-4">Mata Pelajaran</th>
                                <th class="p-4 text-center">Jml Soal</th>
                                <th class="p-4">Guru Pengampu</th>
                                <th class="p-4 pr-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <tr v-for="bank in bankSoalList.data" :key="bank.id" class="hover:bg-slate-50/50 transition-colors">
                                <td class="p-4 pl-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-16 h-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center font-bold text-xs shrink-0 border border-indigo-100">
                                            Kelas {{ bank.tingkat }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900">{{ bank.judul }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-50 text-slate-700 font-bold rounded-lg text-xs border border-slate-200">
                                        <BookOpen class="w-3.5 h-3.5 text-indigo-500" />
                                        {{ bank.mata_pelajaran?.nama }}
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 font-black rounded-lg border border-emerald-200">
                                        {{ bank.soal_count }} Soal
                                    </span>
                                </td>
                                <td class="p-4 text-slate-600 font-medium">
                                    {{ bank.guru?.nama || '-' }}
                                </td>
                                <td class="p-4 pr-6 text-right space-x-2">
                                    <Link :href="route('admin.ujian.bank-soal.show', bank.hashid)" class="inline-flex p-2 text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors" title="Kelola Soal">
                                        <Eye class="w-4 h-4" />
                                    </Link>
                                    <button @click="openDelete(bank)" class="p-2 text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg transition-colors" title="Hapus">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="bankSoalList.data.length === 0">
                                <td colspan="5" class="p-8 text-center text-slate-500 font-medium">
                                    Tidak ada data bank soal.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div v-if="bankSoalList.links.length > 3" class="p-4 border-t border-slate-100 flex justify-center">
                    <div class="flex flex-wrap gap-1">
                        <template v-for="(link, k) in bankSoalList.links" :key="k">
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

        <!-- Modal Create Bank Soal -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between p-6 border-b border-slate-100">
                    <h2 class="text-xl font-black text-slate-900">Buat Bank Soal Baru</h2>
                    <button @click="showModal = false" class="p-2 text-slate-400 hover:text-slate-600 bg-slate-50 rounded-xl transition-colors">
                        <X class="w-5 h-5" />
                    </button>
                </div>
                
                <form @submit.prevent="submitForm" class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Mata Pelajaran</label>
                            <SearchableSelect
                                v-model="form.mata_pelajaran_id"
                                :options="mapelOptions"
                                placeholder="-- Cari & Pilih Mata Pelajaran --"
                                search-keys="searchText"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Tingkat Kelas</label>
                            <select v-model="form.tingkat" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600 font-bold">
                                <option value="">-- Pilih Tingkat Kelas --</option>
                                <option value="X">Kelas X</option>
                                <option value="XI">Kelas XI</option>
                                <option value="XII">Kelas XII</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Judul Bank Soal</label>
                            <input type="text" v-model="form.judul" required placeholder="Ex: Soal Latihan Aljabar Linear" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600 font-medium">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Deskripsi (Opsional)</label>
                            <textarea v-model="form.deskripsi" rows="2" placeholder="Tuliskan keterangan detail..." class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600"></textarea>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end gap-3">
                        <button type="button" @click="showModal = false" class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                            Batal
                        </button>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors flex items-center gap-2">
                            <Check class="w-4 h-4" /> {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <!-- Confirm Delete Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white w-full max-w-sm rounded-3xl shadow-2xl overflow-hidden text-center">
                <div class="p-8">
                    <div class="w-14 h-14 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center mx-auto mb-5">
                        <AlertTriangle class="w-8 h-8" />
                    </div>
                    <h3 class="text-xl font-black text-slate-900 mb-2">Hapus Bank Soal?</h3>
                    <p class="text-slate-500 text-sm font-medium leading-relaxed mb-2">
                        Bank soal <span class="font-bold text-slate-900">{{ deleteTarget?.judul }}</span> akan dihapus beserta seluruh soal di dalamnya.
                    </p>
                    <p class="text-xs text-slate-400 font-medium mb-7">Tindakan ini tidak dapat dibatalkan.</p>
                    <div class="flex gap-3">
                        <button @click="showDeleteModal = false" :disabled="isDeleting"
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
