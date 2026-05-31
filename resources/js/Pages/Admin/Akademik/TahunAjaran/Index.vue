<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Calendar, Plus, Search, Pencil, Trash2, X, Check, CheckCircle, XCircle } from 'lucide-vue-next';

const props = defineProps({
    tahunAjaranList: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const showModal = ref(false);
const editTarget = ref(null);

const form = useForm({
    nama: '',
    is_active: false,
});

const openCreate = () => {
    editTarget.value = null;
    form.reset();
    form.is_active = false;
    showModal.value = true;
};

const openEdit = (item) => {
    editTarget.value = item;
    form.nama = item.nama;
    form.is_active = item.is_active;
    showModal.value = true;
};

const handleSearch = () => {
    router.get(route('admin.web.tahun-ajaran.index'), {
        search: search.value,
    }, { preserveState: true, replace: true });
};

const submitForm = () => {
    if (editTarget.value) {
        form.put(route('admin.web.tahun-ajaran.update', editTarget.value.hashid), {
            onSuccess: () => { showModal.value = false; form.reset(); },
        });
    } else {
        form.post(route('admin.web.tahun-ajaran.store'), {
            onSuccess: () => { showModal.value = false; form.reset(); },
        });
    }
};

const hapus = (item) => {
    if (confirm(`Hapus tahun ajaran "${item.nama}"?`)) {
        router.delete(route('admin.web.tahun-ajaran.destroy', item.hashid));
    }
};
</script>

<template>
    <Head title="Tahun Ajaran" />
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto space-y-6">
            <div class="flex flex-col sm:flex-row justify-between gap-4 items-start sm:items-center">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                        <Calendar class="w-7 h-7 text-indigo-600" />
                        Tahun Ajaran
                    </h1>
                    <p class="text-slate-500 font-medium mt-1">Kelola tahun ajaran dan tentukan tahun ajaran aktif.</p>
                </div>
                <button @click="openCreate" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl flex items-center gap-2 transition-colors shadow-lg shadow-indigo-200">
                    <Plus class="w-5 h-5" /> Tambah Tahun Ajaran
                </button>
            </div>

            <div v-if="$page.props.flash?.success" class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 font-medium rounded-xl text-sm flex items-start gap-2">
                <CheckCircle class="w-4 h-4 mt-0.5 shrink-0" />
                <span>{{ $page.props.flash.success }}</span>
            </div>
            <div v-if="$page.props.flash?.error" class="p-4 bg-rose-50 border border-rose-200 text-rose-800 font-medium rounded-xl text-sm flex items-start gap-2">
                <XCircle class="w-4 h-4 mt-0.5 shrink-0" />
                <span>{{ $page.props.flash.error }}</span>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row gap-4 items-center">
                <div class="relative w-full sm:w-80">
                    <input type="text" v-model="search" @keyup.enter="handleSearch"
                        placeholder="Cari tahun ajaran..."
                        class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600 text-sm" />
                    <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
                </div>
                <button @click="handleSearch" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl transition-colors shrink-0">
                    Cari
                </button>
            </div>

            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider">
                                <th class="p-4 pl-6">Nama Tahun Ajaran</th>
                                <th class="p-4">Status</th>
                                <th class="p-4 pr-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <tr v-for="item in tahunAjaranList.data" :key="item.id" class="hover:bg-slate-50/50 transition-colors">
                                <td class="p-4 pl-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center font-bold text-xs">
                                            <Calendar class="w-5 h-5" />
                                        </div>
                                        <p class="font-bold text-slate-900">{{ item.nama }}</p>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span v-if="item.is_active" class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-700 font-bold rounded-lg text-xs border border-emerald-200">
                                        <Check class="w-3 h-3" /> Aktif
                                    </span>
                                    <span v-else class="inline-flex items-center gap-1.5 px-3 py-1 bg-slate-50 text-slate-500 font-bold rounded-lg text-xs border border-slate-200">
                                        Tidak Aktif
                                    </span>
                                </td>
                                <td class="p-4 pr-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="openEdit(item)" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-colors">
                                            <Pencil class="w-4 h-4" />
                                        </button>
                                        <button @click="hapus(item)" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-colors">
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="tahunAjaranList.data.length === 0">
                                <td colspan="3" class="p-12 text-center">
                                    <div class="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-3 text-slate-400">
                                        <Calendar class="w-6 h-6" />
                                    </div>
                                    <p class="font-bold text-slate-700">Belum ada tahun ajaran</p>
                                    <p class="text-slate-500 text-xs mt-1">Tambahkan tahun ajaran baru untuk memulai.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Pagination :data="tahunAjaranList" />
            </div>
        </div>

        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between p-6 border-b border-slate-100">
                    <h2 class="text-xl font-black text-slate-900">{{ editTarget ? 'Edit Tahun Ajaran' : 'Tambah Tahun Ajaran Baru' }}</h2>
                    <button @click="showModal = false" class="p-2 text-slate-400 hover:text-slate-600 bg-slate-50 rounded-xl transition-colors">
                        <X class="w-5 h-5" />
                    </button>
                </div>
                <form @submit.prevent="submitForm" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Nama Tahun Ajaran</label>
                        <input type="text" v-model="form.nama" required
                            placeholder="Contoh: 2025/2026"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600" />
                        <p v-if="form.errors.nama" class="text-xs text-rose-500 mt-1 font-bold">{{ form.errors.nama }}</p>
                    </div>
                    <div>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" v-model="form.is_active" class="w-5 h-5 text-indigo-600 border-slate-300 rounded focus:ring-indigo-600" />
                            <span class="text-sm font-bold text-slate-700">Jadikan tahun ajaran aktif</span>
                        </label>
                        <p v-if="form.errors.is_active" class="text-xs text-rose-500 mt-1 font-bold">{{ form.errors.is_active }}</p>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showModal = false" class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                            Batal
                        </button>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors flex items-center gap-2 disabled:opacity-60">
                            <Check class="w-4 h-4" /> {{ form.processing ? 'Menyimpan...' : (editTarget ? 'Perbarui' : 'Simpan') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
