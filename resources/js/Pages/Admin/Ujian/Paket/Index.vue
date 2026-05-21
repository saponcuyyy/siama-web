<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    Package, Plus, Search, Eye, FileText, Settings, X, Check, BookOpen
} from 'lucide-vue-next';

const props = defineProps({
    paketList: Object,
    filters: Object,
    mapelList: Array,
});

const search = ref(props.filters.search || '');
const showModal = ref(false);

const form = useForm({
    mata_pelajaran_id: '',
    kode: '',
    nama: '',
    deskripsi: '',
    durasi_menit: 90,
    acak_soal: true,
    acak_jawaban: true,
});

const handleSearch = () => {
    router.get(route('admin.ujian.paket.index'), { search: search.value }, { preserveState: true, replace: true });
};

const submitForm = () => {
    form.post(route('admin.ujian.paket.store'), {
        onSuccess: () => {
            showModal.value = false;
            form.reset();
        }
    });
};
</script>

<template>
    <Head title="Manajemen Paket Ujian" />
    
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between gap-4 items-start sm:items-center">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                        <Package class="w-7 h-7 text-indigo-600" />
                        Paket Ujian
                    </h1>
                    <p class="text-slate-500 font-medium mt-1">Kelola dan rangkai soal ke dalam paket ujian.</p>
                </div>
                <button @click="showModal = true" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl flex items-center gap-2 transition-colors shadow-lg shadow-indigo-200">
                    <Plus class="w-5 h-5" /> Buat Paket
                </button>
            </div>

            <!-- Filters -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row gap-4 items-center justify-between">
                <div class="relative w-full sm:w-96">
                    <input 
                        type="text" 
                        v-model="search" 
                        @keyup.enter="handleSearch"
                        placeholder="Cari kode atau nama paket..." 
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
                                <th class="p-4 pl-6">Kode & Nama</th>
                                <th class="p-4">Mata Pelajaran</th>
                                <th class="p-4 text-center">Durasi</th>
                                <th class="p-4 text-center">Jml Soal</th>
                                <th class="p-4 pr-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <tr v-for="paket in paketList.data" :key="paket.id" class="hover:bg-slate-50/50 transition-colors">
                                <td class="p-4 pl-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center font-bold text-xs">
                                            {{ paket.kode }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900">{{ paket.nama }}</p>
                                            <div class="flex gap-2 mt-1">
                                                <span v-if="paket.acak_soal" class="text-[10px] font-bold text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded">Acak Soal</span>
                                                <span v-if="paket.acak_jawaban" class="text-[10px] font-bold text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded">Acak Opsi</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-50 text-slate-700 font-bold rounded-lg text-xs border border-slate-200">
                                        <BookOpen class="w-3.5 h-3.5 text-indigo-500" />
                                        {{ paket.mata_pelajaran?.nama }}
                                    </span>
                                </td>
                                <td class="p-4 text-center font-bold text-slate-700">
                                    {{ paket.durasi_menit }}'
                                </td>
                                <td class="p-4 text-center">
                                    <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 font-black rounded-lg border border-emerald-200">
                                        {{ paket.soal_count }}
                                    </span>
                                </td>
                                <td class="p-4 pr-6 text-right space-x-2">
                                    <Link :href="route('admin.ujian.paket.show', paket.id)" class="inline-flex p-2 text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors" title="Lihat & Kelola Soal">
                                        <Eye class="w-4 h-4" />
                                    </Link>
                                    <button class="p-2 text-slate-400 hover:text-slate-600 bg-slate-50 hover:bg-slate-100 rounded-lg transition-colors" title="Pengaturan">
                                        <Settings class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="paketList.data.length === 0">
                                <td colspan="5" class="p-8 text-center text-slate-500 font-medium">
                                    Tidak ada data paket ujian.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div v-if="paketList.links.length > 3" class="p-4 border-t border-slate-100 flex justify-center">
                    <div class="flex flex-wrap gap-1">
                        <template v-for="(link, k) in paketList.links" :key="k">
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

        <!-- Modal Create Paket -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between p-6 border-b border-slate-100">
                    <h2 class="text-xl font-black text-slate-900">Buat Paket Baru</h2>
                    <button @click="showModal = false" class="p-2 text-slate-400 hover:text-slate-600 bg-slate-50 rounded-xl transition-colors">
                        <X class="w-5 h-5" />
                    </button>
                </div>
                
                <form @submit.prevent="submitForm" class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Mata Pelajaran</label>
                            <select v-model="form.mata_pelajaran_id" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600">
                                <option value="">-- Pilih Mata Pelajaran --</option>
                                <option v-for="m in mapelList" :key="m.id" :value="m.id">{{ m.nama }} ({{ m.kode }})</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Kode Paket</label>
                                <input type="text" v-model="form.kode" required placeholder="Ex: MTK-XI-1" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600 uppercase">
                                <p v-if="form.errors.kode" class="text-xs text-rose-500 mt-1 font-bold">{{ form.errors.kode }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Durasi (Menit)</label>
                                <input type="number" min="1" v-model="form.durasi_menit" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Nama Paket Ujian</label>
                            <input type="text" v-model="form.nama" required placeholder="Ujian Akhir Semester Ganjil" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Deskripsi (Opsional)</label>
                            <textarea v-model="form.deskripsi" rows="2" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600"></textarea>
                        </div>

                        <div class="flex gap-6 mt-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" v-model="form.acak_soal" class="w-5 h-5 text-indigo-600 border-slate-300 rounded focus:ring-indigo-600">
                                <span class="text-sm font-bold text-slate-700">Acak Urutan Soal</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" v-model="form.acak_jawaban" class="w-5 h-5 text-indigo-600 border-slate-300 rounded focus:ring-indigo-600">
                                <span class="text-sm font-bold text-slate-700">Acak Opsi (PG)</span>
                            </label>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end gap-3">
                        <button type="button" @click="showModal = false" class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                            Batal
                        </button>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors flex items-center gap-2">
                            <Check class="w-4 h-4" /> {{ form.processing ? 'Menyimpan...' : 'Simpan Paket' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
