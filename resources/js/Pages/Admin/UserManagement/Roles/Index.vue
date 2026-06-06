<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Shield, Plus, Pencil, Trash2, X, Check, Lock, AlertTriangle } from 'lucide-vue-next';

const page = usePage();
const authUser = page.props.auth?.user;

const can = (perm) => {
    if (!authUser) return false;
    if (authUser.roles?.includes('super_admin')) return true;
    return authUser.permissions?.includes(perm);
};

const props = defineProps({
    roleList: Array,
    permissions: Array,
});

const showModal = ref(false);
const showDeleteModal = ref(false);
const deleteTarget = ref(null);
const editTarget = ref(null);

const form = useForm({
    name: '',
    permissions: [],
});

const groupLabels = {
    dashboard: 'Dashboard',
    users: 'Manajemen User',
    roles: 'Manajemen Role',
    siswa: 'Siswa',
    guru: 'Guru',
    nilai: 'Penilaian',
    jadwal: 'Jadwal',
    ujian: 'Ujian',
    perpustakaan: 'Perpustakaan',
    web: 'Web Management',
    settings: 'Pengaturan',
    audit: 'Audit Trail',
};

const permLabels = {
    'dashboard.view': 'Lihat Dashboard',
    'users.view': 'Lihat User',
    'users.create': 'Buat User',
    'users.edit': 'Edit User',
    'users.delete': 'Hapus User',
    'roles.view': 'Lihat Role',
    'roles.create': 'Buat Role',
    'roles.edit': 'Edit Role',
    'roles.delete': 'Hapus Role',
    'siswa.view': 'Lihat Siswa',
    'siswa.manage': 'Kelola Siswa',
    'guru.view': 'Lihat Guru',
    'guru.manage': 'Kelola Guru',
    'nilai.view': 'Lihat Nilai',
    'nilai.create': 'Input Nilai',
    'nilai.edit': 'Edit Nilai',
    'nilai.delete': 'Hapus Nilai',
    'jadwal.view': 'Lihat Jadwal',
    'jadwal.manage': 'Kelola Jadwal',
    'ujian.view': 'Lihat Ujian',
    'ujian.manage': 'Kelola Ujian',
    'ujian.participate': 'Ikut Ujian',
    'ujian.bank-soal.manage': 'Kelola Bank Soal',
    'ujian.soal.manage': 'Kelola Soal',
    'ujian.paket.manage': 'Kelola Paket',
    'ujian.sesi.manage': 'Kelola Sesi',
    'ujian.sesi.monitor': 'Monitor Sesi',
    'ujian.penilaian.essay': 'Nilai Essay',
    'ujian.laporan.view': 'Lihat Laporan',
    'ujian.laporan.export': 'Ekspor Laporan',
    'perpustakaan.view': 'Lihat Perpustakaan',
    'perpustakaan.manage': 'Kelola Perpustakaan',
    'settings.view': 'Lihat Pengaturan',
    'settings.manage': 'Kelola Pengaturan',
    'web.view': 'Akses Web Management',
    'web.dashboard.view': 'Lihat Dashboard CMS',
    'web.menu.manage': 'Kelola Menu Navigasi',
    'web.slider.manage': 'Kelola Slider / Hero',
    'web.halaman.manage': 'Kelola Halaman Statis',
    'web.fasilitas.manage': 'Kelola Fasilitas Sekolah',
    'web.kategori-berita.manage': 'Kelola Kategori Berita',
    'web.berita.manage': 'Kelola Berita & Artikel',
    'web.pengumuman.manage': 'Kelola Pengumuman',
    'web.album.manage': 'Kelola Galeri & Album',
    'web.pesan.view': 'Lihat Pesan Masuk',
    'web.kelulusan.manage': 'Kelola Data Kelulusan',
    'web.setting.manage': 'Kelola Pengaturan Web',
    'audit.view': 'Lihat Audit Trail',
};

const groupOrder = ['dashboard', 'users', 'roles', 'siswa', 'guru', 'nilai', 'jadwal', 'ujian', 'perpustakaan', 'web', 'settings', 'audit'];

const groupedPermissions = computed(() => {
    const groups = {};
    props.permissions.forEach(p => {
        const group = p.split('.')[0];
        if (!groups[group]) groups[group] = [];
        groups[group].push(p);
    });
    const sorted = {};
    groupOrder.forEach(g => { if (groups[g]) sorted[g] = groups[g]; });
    Object.keys(groups).filter(g => !groupOrder.includes(g)).forEach(g => { sorted[g] = groups[g]; });
    return sorted;
});

const openCreate = () => {
    editTarget.value = null;
    form.reset();
    form.permissions = [];
    showModal.value = true;
};

const openEdit = (role) => {
    editTarget.value = role;
    form.name = role.name;
    form.permissions = [...role.permissions];
    showModal.value = true;
};

const togglePermission = (perm) => {
    const idx = form.permissions.indexOf(perm);
    if (idx === -1) {
        form.permissions.push(perm);
    } else {
        form.permissions.splice(idx, 1);
    }
};

const toggleGroup = (perms) => {
    const allChecked = perms.every(p => form.permissions.includes(p));
    if (allChecked) {
        form.permissions = form.permissions.filter(p => !perms.includes(p));
    } else {
        perms.forEach(p => {
            if (!form.permissions.includes(p)) form.permissions.push(p);
        });
    }
};

const isGroupChecked = (perms) => perms.every(p => form.permissions.includes(p));
const isGroupPartial = (perms) => perms.some(p => form.permissions.includes(p)) && !isGroupChecked(perms);

const submitForm = () => {
    if (editTarget.value) {
        form.put(route('admin.roles.update', editTarget.value.id), {
            onSuccess: () => { showModal.value = false; form.reset(); },
        });
    } else {
        form.post(route('admin.roles.store'), {
            onSuccess: () => { showModal.value = false; form.reset(); },
        });
    }
};

const confirmDelete = (role) => {
    deleteTarget.value = role;
    showDeleteModal.value = true;
};

const executeDelete = () => {
    router.delete(route('admin.roles.destroy', deleteTarget.value.id), {
        onSuccess: () => { showDeleteModal.value = false; deleteTarget.value = null; },
    });
};

const roleBadgeColor = (role) => {
    const colors = {
        'super_admin': 'bg-rose-100 text-rose-700 border-rose-200',
        'kepala_sekolah': 'bg-purple-100 text-purple-700 border-purple-200',
        'wakil_kepala': 'bg-indigo-100 text-indigo-700 border-indigo-200',
        'tata_usaha': 'bg-amber-100 text-amber-700 border-amber-200',
        'guru': 'bg-emerald-100 text-emerald-700 border-emerald-200',
        'wali_kelas': 'bg-cyan-100 text-cyan-700 border-cyan-200',
        'siswa': 'bg-slate-100 text-slate-700 border-slate-200',
        'pustakawan': 'bg-teal-100 text-teal-700 border-teal-200',
    };
    return colors[role] || 'bg-slate-100 text-slate-700 border-slate-200';
};
</script>

<template>
    <Head title="Manajemen Role & Permission" />
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between gap-4 items-start sm:items-center">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                        <Shield class="w-7 h-7 text-indigo-600" />
                        Role & Permission
                    </h1>
                    <p class="text-slate-500 font-medium mt-1">Atur role dan izin akses untuk setiap pengguna aplikasi.</p>
                </div>
                <button v-if="can('roles.create')" @click="openCreate" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl flex items-center gap-2 transition-colors shadow-lg shadow-indigo-200">
                    <Plus class="w-5 h-5" /> Tambah Role
                </button>
            </div>

            <!-- Flash -->
            <div v-if="$page.props.flash?.success" class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 font-medium rounded-xl text-sm flex items-start gap-2">
                <Check class="w-4 h-4 mt-0.5 shrink-0" />
                <span>{{ $page.props.flash.success }}</span>
            </div>
            <div v-if="$page.props.flash?.error" class="p-4 bg-rose-50 border border-rose-200 text-rose-800 font-medium rounded-xl text-sm">
                ✗ {{ $page.props.flash.error }}
            </div>

            <!-- Info -->
            <div class="p-4 bg-indigo-50 border border-indigo-100 text-indigo-700 font-medium rounded-2xl text-sm flex items-start gap-3">
                <AlertTriangle class="w-5 h-5 shrink-0 mt-0.5" />
                <div>
                    Role <strong>super_admin</strong> tidak dapat diedit atau dihapus. Hapus role hanya bisa dilakukan jika tidak ada user yang menggunakan role tersebut.
                </div>
            </div>

            <!-- Role Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="role in roleList" :key="role.id"
                    class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                    <div class="p-5 border-b border-slate-100">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-black text-sm bg-gradient-to-br shrink-0"
                                    :class="role.name === 'super_admin' ? 'from-rose-500 to-pink-600' : 'from-indigo-500 to-purple-600'">
                                    {{ role.name.charAt(0).toUpperCase() }}
                                </div>
                                <div>
                                    <h3 class="font-bold text-slate-900 capitalize">{{ role.name.replace('_', ' ') }}</h3>
                                    <p class="text-xs text-slate-400">{{ role.users_count }} user</p>
                                </div>
                            </div>
                            <div class="flex gap-1">
                                <button v-if="role.name !== 'super_admin' && can('roles.edit')" @click="openEdit(role)"
                                    class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-colors">
                                    <Pencil class="w-4 h-4" />
                                </button>
                                <button v-if="role.name !== 'super_admin' && can('roles.delete')" @click="confirmDelete(role)"
                                    class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-colors">
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 space-y-1 max-h-48 overflow-y-auto custom-scrollbar">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Permissions</p>
                        <span v-for="perm in role.permissions" :key="perm"
                            class="inline-block px-2 py-0.5 bg-slate-50 text-slate-600 text-xs font-medium rounded-md border border-slate-200 mr-1 mb-1">
                            {{ permLabels[perm] || perm }}
                        </span>
                        <p v-if="!role.permissions.length" class="text-xs text-slate-400 italic">Tidak ada permission</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Tambah / Edit Role -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm overflow-y-auto">
            <div class="bg-white w-full max-w-2xl my-8 rounded-3xl shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between p-6 border-b border-slate-100 sticky top-0 bg-white z-10">
                    <h2 class="text-xl font-black text-slate-900">{{ editTarget ? 'Edit Role' : 'Tambah Role Baru' }}</h2>
                    <button @click="showModal = false; form.reset()" class="p-2 text-slate-400 hover:text-slate-600 bg-slate-50 rounded-xl transition-colors">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="p-6 space-y-5">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Nama Role</label>
                        <input type="text" v-model="form.name" required
                            placeholder="contoh: wakil_kepala"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600" />
                        <p v-if="form.errors.name" class="text-xs text-rose-500 mt-1 font-bold">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <label class="text-sm font-bold text-slate-700">Permissions</label>
                            <button type="button" @click="form.permissions = (form.permissions.length === props.permissions.length) ? [] : [...props.permissions]"
                                class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition-colors">
                                {{ form.permissions.length === props.permissions.length ? 'Uncheck All' : 'Check All' }}
                            </button>
                        </div>

                        <div class="space-y-3 max-h-96 overflow-y-auto custom-scrollbar pr-2">
                            <div v-for="(perms, group) in groupedPermissions" :key="group"
                                class="bg-slate-50 rounded-2xl p-4 border border-slate-200">
                                <div class="flex items-center justify-between mb-2">
                                    <label class="text-sm font-black text-slate-700 flex items-center gap-2">
                                        <Lock class="w-4 h-4 text-slate-400" />
                                        {{ groupLabels[group] || group }}
                                    </label>
                                    <button type="button" @click="toggleGroup(perms)"
                                        class="text-xs font-bold"
                                        :class="isGroupChecked(perms) ? 'text-indigo-600' : 'text-slate-400 hover:text-indigo-600'">
                                        {{ isGroupChecked(perms) ? 'Uncheck All' : 'Check All' }}
                                    </button>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <label v-for="perm in perms" :key="perm"
                                        class="flex items-center gap-2 px-3 py-1.5 rounded-xl cursor-pointer transition-colors"
                                        :class="form.permissions.includes(perm) ? 'bg-indigo-100 text-indigo-700 border border-indigo-200' : 'bg-white text-slate-600 border border-slate-200 hover:border-indigo-200'">
                                        <input type="checkbox" :checked="form.permissions.includes(perm)"
                                            @change="togglePermission(perm)" class="sr-only" />
                                        <Check v-if="form.permissions.includes(perm)" class="w-3.5 h-3.5 shrink-0" />
                                        <div v-else class="w-3.5 h-3.5 shrink-0 border-2 border-slate-300 rounded" />
                                        <span class="text-xs font-medium">{{ permLabels[perm] || perm }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <p v-if="form.errors.permissions" class="text-xs text-rose-500 mt-1 font-bold">{{ form.errors.permissions }}</p>
                    </div>

                    <div class="flex justify-end gap-3 pt-2 border-t border-slate-100">
                        <button type="button" @click="showModal = false; form.reset()"
                            class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                            Batal
                        </button>
                        <button type="submit" :disabled="form.processing"
                            class="px-5 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors flex items-center gap-2 disabled:opacity-60">
                            <Check class="w-4 h-4" /> {{ form.processing ? 'Menyimpan...' : (editTarget ? 'Perbarui Role' : 'Simpan Role') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Konfirmasi Hapus -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl p-6 text-center">
                <div class="w-14 h-14 bg-rose-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <Trash2 class="w-6 h-6 text-rose-500" />
                </div>
                <h3 class="text-lg font-black text-slate-900 mb-2">Hapus Role</h3>
                <p class="text-sm text-slate-500 mb-6">
                    Yakin ingin menghapus role <strong class="text-slate-700 capitalize">{{ deleteTarget?.name?.replace('_', ' ') }}</strong>? Tindakan ini tidak dapat dibatalkan.
                </p>
                <div class="flex gap-3 justify-center">
                    <button @click="showDeleteModal = false; deleteTarget = null"
                        class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                        Batal
                    </button>
                    <button @click="executeDelete"
                        class="px-5 py-2.5 text-sm font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition-colors flex items-center gap-2">
                        <Trash2 class="w-4 h-4" /> Hapus Role
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
