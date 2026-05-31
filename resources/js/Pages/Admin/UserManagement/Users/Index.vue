<script setup>
import { ref } from 'vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Users, Plus, Search, Pencil, Trash2, X, Check, Mail, Shield, CalendarDays } from 'lucide-vue-next';
import dayjs from 'dayjs';

const page = usePage();
const authUser = page.props.auth?.user;

const can = (perm) => {
    if (!authUser) return false;
    if (authUser.roles?.includes('super_admin')) return true;
    return authUser.permissions?.includes(perm);
};

const props = defineProps({
    userList: Object,
    filters: Object,
    roleList: Array,
});

const search = ref(props.filters.search || '');
const filterRole = ref(props.filters.role || '');
const showModal = ref(false);
const editTarget = ref(null);

const form = useForm({
    name: '',
    email: '',
    password: '',
    role: '',
});

const openCreate = () => {
    editTarget.value = null;
    form.reset();
    showModal.value = true;
};

const openEdit = (user) => {
    editTarget.value = user;
    form.name = user.name;
    form.email = user.email;
    form.password = '';
    form.role = user.roles?.[0] || '';
    showModal.value = true;
};

const handleSearch = () => {
    router.get(route('admin.users.index'), {
        search: search.value,
        role: filterRole.value,
    }, { preserveState: true, replace: true });
};

const submitForm = () => {
    if (editTarget.value) {
        form.put(route('admin.users.update', editTarget.value.id), {
            onSuccess: () => { showModal.value = false; form.reset(); },
        });
    } else {
        form.post(route('admin.users.store'), {
            onSuccess: () => { showModal.value = false; form.reset(); },
        });
    }
};

const hapus = (user) => {
    if (confirm(`Hapus user "${user.name}"? Tindakan ini tidak dapat dibatalkan.`)) {
        router.delete(route('admin.users.destroy', user.id));
    }
};

const formatDate = (date) => {
    if (!date) return '-';
    return dayjs(date).format('DD MMM YYYY');
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
    <Head title="Manajemen User" />
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between gap-4 items-start sm:items-center">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                        <Users class="w-7 h-7 text-indigo-600" />
                        Manajemen User
                    </h1>
                    <p class="text-slate-500 font-medium mt-1">Kelola seluruh akun pengguna aplikasi dan role akses.</p>
                </div>
                <button v-if="can('users.create')" @click="openCreate" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl flex items-center gap-2 transition-colors shadow-lg shadow-indigo-200">
                    <Plus class="w-5 h-5" /> Tambah User
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

            <!-- Search & Filter -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row gap-4 items-center">
                <div class="relative w-full sm:w-72">
                    <input type="text" v-model="search" @keyup.enter="handleSearch"
                        placeholder="Cari nama atau email..."
                        class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600 text-sm" />
                    <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
                </div>
                <select v-model="filterRole" @change="handleSearch"
                    class="w-full sm:w-56 px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600 text-sm font-medium">
                    <option value="">Semua Role</option>
                    <option v-for="r in roleList" :key="r" :value="r">{{ r }}</option>
                </select>
                <button @click="handleSearch" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl transition-colors shrink-0">
                    Cari
                </button>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 bg-slate-50/50 border-b border-slate-200 flex items-center justify-between">
                    <p class="text-sm font-bold text-slate-600">
                        Total: <span class="text-indigo-600">{{ userList.total }}</span> user
                    </p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider">
                                <th class="p-4 pl-6">User</th>
                                <th class="p-4">Email</th>
                                <th class="p-4">Role</th>
                                <th class="p-4">Tanggal dibuat</th>
                                <th class="p-4 pr-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <tr v-for="user in userList.data" :key="user.id"
                                class="hover:bg-slate-50/50 transition-colors">
                                <td class="p-4 pl-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-black text-sm shrink-0">
                                            {{ user.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <span class="font-bold text-slate-900">{{ user.name }}</span>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span class="flex items-center gap-1.5 text-slate-600">
                                        <Mail class="w-3.5 h-3.5 text-slate-400" />
                                        {{ user.email }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <span v-for="role in user.roles" :key="role"
                                        class="inline-block px-2.5 py-1 rounded-lg text-xs font-bold border mr-1"
                                        :class="roleBadgeColor(role)">
                                        <Shield class="w-3 h-3 inline mr-1" />{{ role }}
                                    </span>
                                    <span v-if="!user.roles.length" class="text-slate-400 text-xs">Tidak ada role</span>
                                </td>
                                <td class="p-4 text-slate-600">
                                    <span class="flex items-center gap-1.5">
                                        <CalendarDays class="w-3.5 h-3.5 text-slate-400" />
                                        {{ formatDate(user.created_at) }}
                                    </span>
                                </td>
                                <td class="p-4 pr-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button v-if="can('users.edit')" @click="openEdit(user)" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-colors">
                                            <Pencil class="w-4 h-4" />
                                        </button>
                                        <button v-if="can('users.delete')" @click="hapus(user)" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-colors">
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="userList.data.length === 0">
                                <td colspan="5" class="p-12 text-center">
                                    <div class="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-3 text-slate-400">
                                        <Users class="w-6 h-6" />
                                    </div>
                                    <p class="font-bold text-slate-700">Tidak ada data user</p>
                                    <p class="text-slate-500 text-xs mt-1">Coba ubah filter atau tambahkan user baru.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <Pagination :data="userList" />
            </div>
        </div>

        <!-- Modal Tambah / Edit User -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between p-6 border-b border-slate-100">
                    <h2 class="text-xl font-black text-slate-900">{{ editTarget ? 'Edit User' : 'Tambah User Baru' }}</h2>
                    <button @click="showModal = false" class="p-2 text-slate-400 hover:text-slate-600 bg-slate-50 rounded-xl transition-colors">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Nama Lengkap</label>
                        <input type="text" v-model="form.name" required
                            placeholder="Nama lengkap user"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600" />
                        <p v-if="form.errors.name" class="text-xs text-rose-500 mt-1 font-bold">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Email</label>
                        <input type="email" v-model="form.email" required
                            placeholder="email@example.com"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600" />
                        <p v-if="form.errors.email" class="text-xs text-rose-500 mt-1 font-bold">{{ form.errors.email }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">
                            Password {{ editTarget ? '(kosongkan jika tidak diubah)' : '' }}
                        </label>
                        <input type="password" v-model="form.password"
                            :required="!editTarget"
                            placeholder="Minimal 6 karakter"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600" />
                        <p v-if="form.errors.password" class="text-xs text-rose-500 mt-1 font-bold">{{ form.errors.password }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Role</label>
                        <select v-model="form.role" required
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600 text-sm font-medium capitalize">
                            <option value="">-- Pilih Role --</option>
                            <option v-for="r in roleList" :key="r" :value="r">{{ r }}</option>
                        </select>
                        <p v-if="form.errors.role" class="text-xs text-rose-500 mt-1 font-bold">{{ form.errors.role }}</p>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showModal = false"
                            class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                            Batal
                        </button>
                        <button type="submit" :disabled="form.processing"
                            class="px-5 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors flex items-center gap-2 disabled:opacity-60">
                            <Check class="w-4 h-4" /> {{ form.processing ? 'Menyimpan...' : (editTarget ? 'Perbarui User' : 'Simpan User') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
