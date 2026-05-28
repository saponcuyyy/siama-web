<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Users, Plus, Search, Pencil, Trash2, X, Check, GraduationCap, ChevronRight, Printer } from 'lucide-vue-next';

const props = defineProps({
    rombelList: Object,
    filters: Object,
    guruList: Array,
    tahunAjaranList: Array,
});

const search = ref(props.filters.search || '');
const filterTahun = ref(props.filters.tahun_ajaran_id || '');
const showModal = ref(false);
const editTarget = ref(null);

const form = useForm({
    nama: '',
    tingkat: '',
    tahun_ajaran_id: '',
    guru_id: '',
});

const openCreate = () => {
    editTarget.value = null;
    form.reset();
    // Default ke tahun ajaran aktif
    const aktif = props.tahunAjaranList.find(t => t.is_active);
    if (aktif) form.tahun_ajaran_id = aktif.id;
    showModal.value = true;
};

const openEdit = (rombel) => {
    editTarget.value = rombel;
    form.nama = rombel.nama;
    form.tingkat = rombel.tingkat;
    form.tahun_ajaran_id = rombel.tahun_ajaran_id;
    form.guru_id = rombel.guru_id || '';
    showModal.value = true;
};

const handleSearch = () => {
    router.get(route('admin.web.rombel.index'), {
        search: search.value,
        tahun_ajaran_id: filterTahun.value,
    }, { preserveState: true, replace: true });
};

const submitForm = () => {
    if (editTarget.value) {
        form.put(route('admin.web.rombel.update', editTarget.value.hashid), {
            onSuccess: () => { showModal.value = false; form.reset(); },
        });
    } else {
        form.post(route('admin.web.rombel.store'), {
            onSuccess: () => { showModal.value = false; form.reset(); },
        });
    }
};

const hapus = (rombel) => {
    if (confirm(`Hapus rombel "${rombel.nama}"? Rombel yang masih memiliki siswa tidak dapat dihapus.`)) {
        router.delete(route('admin.web.rombel.destroy', rombel.hashid));
    }
};

const tingkatOptions = ['X', 'XI', 'XII'];
</script>

<template>
    <Head title="Manajemen Rombel" />
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between gap-4 items-start sm:items-center">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                        <GraduationCap class="w-7 h-7 text-indigo-600" />
                        Rombongan Belajar
                    </h1>
                    <p class="text-slate-500 font-medium mt-1">Kelola data kelas, wali kelas, dan tahun pelajaran.</p>
                </div>
                <button @click="openCreate" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl flex items-center gap-2 transition-colors shadow-lg shadow-indigo-200">
                    <Plus class="w-5 h-5" /> Tambah Rombel
                </button>
            </div>

            <!-- Flash Messages -->
            <div v-if="$page.props.flash?.success" class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 font-medium rounded-xl text-sm">
                ✓ {{ $page.props.flash.success }}
            </div>
            <div v-if="$page.props.flash?.error" class="p-4 bg-rose-50 border border-rose-200 text-rose-800 font-medium rounded-xl text-sm">
                ✗ {{ $page.props.flash.error }}
            </div>

            <!-- Filters -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row gap-4 items-center">
                <div class="relative w-full sm:w-80">
                    <input type="text" v-model="search" @keyup.enter="handleSearch"
                        placeholder="Cari nama rombel..."
                        class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600 text-sm" />
                    <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
                </div>
                <select v-model="filterTahun" @change="handleSearch"
                    class="w-full sm:w-64 px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600 text-sm font-medium">
                    <option value="">Semua Tahun Ajaran</option>
                    <option v-for="t in tahunAjaranList" :key="t.id" :value="t.id">
                        {{ t.nama }} {{ t.is_active ? '(Aktif)' : '' }}
                    </option>
                </select>
            </div>

            <!-- Grid Rombel -->
            <div v-if="rombelList.data.length === 0" class="bg-white rounded-3xl border border-slate-200 p-12 text-center">
                <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4 text-slate-400">
                    <GraduationCap class="w-8 h-8" />
                </div>
                <h3 class="text-slate-900 font-bold mb-1">Belum Ada Data Rombel</h3>
                <p class="text-slate-500 text-sm">Tambahkan data rombongan belajar terlebih dahulu.</p>
            </div>

            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="rombel in rombelList.data" :key="rombel.id"
                    class="bg-white rounded-3xl border border-slate-200 shadow-sm hover:border-indigo-300 hover:shadow-md transition-all group p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center text-white font-black text-lg shadow-lg shadow-indigo-200">
                            {{ rombel.tingkat }}
                        </div>
                        <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <a :href="route('admin.web.rombel.kartu-ujian', rombel.hashid)" target="_blank"
                                class="p-2 text-amber-600 hover:text-amber-700 hover:bg-amber-50 rounded-xl transition-colors"
                                title="Cetak Kartu Ujian">
                                <Printer class="w-4 h-4" />
                            </a>
                            <button @click="openEdit(rombel)" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-colors">
                                <Pencil class="w-4 h-4" />
                            </button>
                            <button @click="hapus(rombel)" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-colors">
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>
                    </div>

                    <h3 class="text-lg font-black text-slate-900 mb-1">{{ rombel.nama }}</h3>
                    <p class="text-sm text-indigo-600 font-bold mb-3">{{ rombel.tahun_ajaran?.nama }}</p>

                    <div class="space-y-2 text-sm text-slate-600">
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 bg-slate-100 rounded-full flex items-center justify-center shrink-0">
                                <span class="text-[8px] font-bold text-slate-500">W</span>
                            </div>
                            <span class="font-medium">{{ rombel.wali_kelas?.nama || 'Belum ada wali kelas' }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <Users class="w-4 h-4 text-slate-400 shrink-0" />
                            <span class="font-medium">{{ rombel.siswa_count }} Siswa</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="rombelList.links.length > 3" class="flex justify-center">
                <div class="flex flex-wrap gap-1">
                    <template v-for="(link, k) in rombelList.links" :key="k">
                        <Link v-if="link.url" :href="link.url"
                            class="px-3 py-1 rounded-lg text-sm font-medium transition-colors"
                            :class="link.active ? 'bg-indigo-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                            v-html="link.label" />
                        <span v-else class="px-3 py-1 text-sm text-slate-400" v-html="link.label" />
                    </template>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between p-6 border-b border-slate-100">
                    <h2 class="text-xl font-black text-slate-900">{{ editTarget ? 'Edit Rombel' : 'Tambah Rombel Baru' }}</h2>
                    <button @click="showModal = false" class="p-2 text-slate-400 hover:text-slate-600 bg-slate-50 rounded-xl transition-colors">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Nama Rombel</label>
                        <input type="text" v-model="form.nama" required
                            placeholder="Contoh: XII IPA 1"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600" />
                        <p v-if="form.errors.nama" class="text-xs text-rose-500 mt-1 font-bold">{{ form.errors.nama }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Tingkat Kelas</label>
                        <select v-model="form.tingkat" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600">
                            <option value="">-- Pilih Tingkat --</option>
                            <option v-for="t in tingkatOptions" :key="t" :value="t">Kelas {{ t }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Tahun Pelajaran</label>
                        <select v-model="form.tahun_ajaran_id" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600">
                            <option value="">-- Pilih Tahun Pelajaran --</option>
                            <option v-for="t in tahunAjaranList" :key="t.id" :value="t.id">
                                {{ t.nama }} {{ t.is_active ? '(Aktif)' : '' }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Wali Kelas (Opsional)</label>
                        <select v-model="form.guru_id" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600">
                            <option value="">-- Belum Ditentukan --</option>
                            <option v-for="g in guruList" :key="g.id" :value="g.id">{{ g.nama }}</option>
                        </select>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showModal = false" class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Batal</button>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors flex items-center gap-2 disabled:opacity-60">
                            <Check class="w-4 h-4" /> {{ form.processing ? 'Menyimpan...' : (editTarget ? 'Perbarui' : 'Simpan') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
