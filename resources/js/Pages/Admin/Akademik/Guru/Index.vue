<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { UserSquare, Plus, Search, Pencil, Trash2, X, Check, Users, Info, Mail, CalendarDays, Briefcase } from 'lucide-vue-next';
import dayjs from 'dayjs';

const props = defineProps({
    guruList: Object,
    filters: Object,
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

const search   = ref(props.filters.search || '');
const showModal = ref(false);
const editTarget = ref(null);

const form = useForm({
    nama:          '',
    nip:           '',
    jabatan:       'Guru',
    email:         '',
    tanggal_lahir: '',
});

const openCreate = () => {
    editTarget.value = null;
    form.reset();
    form.jabatan = 'Guru';
    showModal.value = true;
};

const openEdit = (guru) => {
    editTarget.value = guru;
    form.nama          = guru.nama;
    form.nip           = guru.nip;
    form.jabatan       = guru.jabatan || 'Guru';
    form.email         = guru.user?.email || '';
    form.tanggal_lahir = guru.tanggal_lahir || '';
    showModal.value = true;
};

const handleSearch = () => {
    router.get(route('admin.web.guru.index'), {
        search: search.value,
    }, { preserveState: true, replace: true });
};

const submitForm = () => {
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

const formatDate = (date) => {
    if (!date) return '-';
    return dayjs(date).format('DD MMM YYYY');
};

const hapus = (guru) => {
    if (confirm(`Hapus data guru "${guru.nama}"? Akun login guru ini juga akan dihapus secara permanen.`)) {
        router.delete(route('admin.web.guru.destroy', guru.hashid));
    }
};

const getInitials = (name) => {
    return name.split(' ').slice(0, 2).map(n => n[0]).join('').toUpperCase();
};

const avatarColors = ['from-indigo-500 to-purple-600', 'from-emerald-500 to-teal-600', 'from-amber-500 to-orange-600', 'from-rose-500 to-pink-600', 'from-cyan-500 to-blue-600'];
const getColor = (id) => avatarColors[id % avatarColors.length];
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
                <div class="flex gap-3">
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
                    Saat menambahkan guru baru, sistem akan <strong>otomatis membuat akun login</strong> dengan role <strong>Guru</strong>. Username: <code class="bg-white px-1.5 py-0.5 rounded font-mono text-xs">{email}</code> dan password default: <code class="bg-white px-1.5 py-0.5 rounded font-mono text-xs">guru123</code>.
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
                            <tr class="border-b border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider">
                                <th class="p-4 pl-6">Guru</th>
                                <th class="p-4">NIP / NIK</th>
                                <th class="p-4">Jabatan</th>
                                <th class="p-4">Tanggal Lahir</th>
                                <th class="p-4">Email</th>
                                <th class="p-4 pr-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <tr v-for="guru in guruList.data" :key="guru.id"
                                class="hover:bg-slate-50/50 transition-colors">
                                <td class="p-4 pl-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-black text-sm bg-gradient-to-br shrink-0"
                                            :class="getColor(guru.id)">
                                            {{ getInitials(guru.nama) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900">{{ guru.nama }}</p>
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
                                        <button @click="hapus(guru)" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-colors">
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="guruList.data.length === 0">
                                <td colspan="6" class="p-12 text-center">
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

                <!-- Pagination -->
                <div v-if="guruList.links && guruList.links.length > 3" class="p-4 border-t border-slate-100 flex justify-center">
                    <div class="flex flex-wrap gap-1">
                        <template v-for="(link, k) in guruList.links" :key="k">
                            <Link v-if="link.url" :href="link.url"
                                class="px-3 py-1 rounded-lg text-sm font-medium transition-colors"
                                :class="link.active ? 'bg-indigo-600 text-white' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200'"
                                v-html="link.label" />
                            <span v-else class="px-3 py-1 text-sm text-slate-400" v-html="link.label" />
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Tambah / Edit Guru -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between p-6 border-b border-slate-100">
                    <h2 class="text-xl font-black text-slate-900">{{ editTarget ? 'Edit Data Guru' : 'Tambah Guru Baru' }}</h2>
                    <button @click="showModal = false" class="p-2 text-slate-400 hover:text-slate-600 bg-slate-50 rounded-xl transition-colors">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Nama Lengkap</label>
                        <input type="text" v-model="form.nama" required
                            placeholder="Nama lengkap guru"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600" />
                        <p v-if="form.errors.nama" class="text-xs text-rose-500 mt-1 font-bold">{{ form.errors.nama }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">NIP / NIK</label>
                        <input type="text" v-model="form.nip" required
                            placeholder="Nomor Induk Pegawai / NIK"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600 font-mono" />
                        <p v-if="form.errors.nip" class="text-xs text-rose-500 mt-1 font-bold">{{ form.errors.nip }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Jabatan</label>
                        <select v-model="form.jabatan"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600 text-sm">
                            <option v-for="j in JABATAN_OPTIONS" :key="j" :value="j">{{ j }}</option>
                        </select>
                        <p v-if="form.errors.jabatan" class="text-xs text-rose-500 mt-1 font-bold">{{ form.errors.jabatan }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Tanggal Lahir</label>
                        <input type="date" v-model="form.tanggal_lahir"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600" />
                        <p v-if="form.errors.tanggal_lahir" class="text-xs text-rose-500 mt-1 font-bold">{{ form.errors.tanggal_lahir }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Email</label>
                        <input type="email" v-model="form.email" required
                            placeholder="email@sekolah.com"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600" />
                        <p v-if="form.errors.email" class="text-xs text-rose-500 mt-1 font-bold">{{ form.errors.email }}</p>
                        <p v-if="!editTarget" class="text-xs text-slate-400 mt-1">Username login: <span class="font-mono font-bold">{{ form.email || 'email' }}</span></p>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showModal = false"
                            class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                            Batal
                        </button>
                        <button type="submit" :disabled="form.processing"
                            class="px-5 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors flex items-center gap-2 disabled:opacity-60">
                            <Check class="w-4 h-4" /> {{ form.processing ? 'Menyimpan...' : (editTarget ? 'Perbarui Data' : 'Simpan Guru') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
