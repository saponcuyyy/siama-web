<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    User, Lock, Globe, Shield, ChevronRight,
    CheckCircle, AlertCircle, Eye, EyeOff, Save,
    Mail, KeyRound, Building2, ExternalLink,
} from 'lucide-vue-next';

const props = defineProps({
    user: Object,
    systemSettings: Object,
});

const page = usePage();
const activeTab = ref('profil');
const showCurrentPw = ref(false);
const showNewPw = ref(false);
const showConfirmPw = ref(false);

const tabs = [
    { id: 'profil',   label: 'Profil Akun',      icon: User,    desc: 'Informasi identitas pengguna' },
    { id: 'keamanan', label: 'Keamanan',          icon: Lock,    desc: 'Password & keamanan akun' },
    { id: 'sistem',   label: 'Pengaturan Website', icon: Globe,   desc: 'Konfigurasi CMS & website' },
];

// ── Profil Form ──────────────────────────────────────────────────────────────
const profilForm = useForm({
    name:  props.user?.name  || '',
    email: props.user?.email || '',
});

const submitProfil = () => profilForm.put(route('admin.pengaturan.profil.update'));

// ── Password Form ────────────────────────────────────────────────────────────
const pwForm = useForm({
    current_password:      '',
    password:              '',
    password_confirmation: '',
});

const submitPassword = () => {
    pwForm.put(route('admin.pengaturan.password.update'), {
        onSuccess: () => pwForm.reset(),
    });
};

// Avatar initials
const initials = computed(() => {
    return (props.user?.name || 'U')
        .split(' ').slice(0, 2).map(s => s[0]).join('').toUpperCase();
});

const roleLabel = computed(() => props.user?.roles?.[0] ?? 'pengguna');
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Pengaturan Akun" />

        <div class="max-w-5xl mx-auto space-y-8">

            <!-- ── Header ─────────────────────────────────────────── -->
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-600 via-indigo-700 to-violet-800 p-8 shadow-xl shadow-indigo-200">
                <div class="absolute inset-0 opacity-10"
                     style="background-image:radial-gradient(circle at 70% 30%, white 1px, transparent 1px);background-size:24px 24px;"></div>
                <div class="relative flex flex-col sm:flex-row items-center sm:items-start gap-6">
                    <!-- Avatar -->
                    <div class="w-20 h-20 rounded-2xl bg-white/20 backdrop-blur-sm border-2 border-white/30 flex items-center justify-center shadow-lg flex-shrink-0">
                        <span class="text-3xl font-black text-white">{{ initials }}</span>
                    </div>
                    <div class="text-center sm:text-left">
                        <p class="text-indigo-200 text-xs font-bold uppercase tracking-widest mb-1">Pengaturan Akun</p>
                        <h1 class="text-2xl font-black text-white">{{ user?.name }}</h1>
                        <p class="text-indigo-200 text-sm mt-1">{{ user?.email }}</p>
                        <span class="mt-3 inline-flex items-center gap-1.5 bg-white/15 text-white text-xs font-bold px-3 py-1 rounded-full capitalize border border-white/20">
                            <Shield class="w-3 h-3" />
                            {{ roleLabel }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- ── Layout Grid ─────────────────────────────────────── -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

                <!-- Sidebar Tabs -->
                <nav class="lg:col-span-3 space-y-1 lg:sticky lg:top-6">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        type="button"
                        @click="activeTab = tab.id"
                        :class="[
                            'w-full flex items-center gap-3.5 px-4 py-3.5 rounded-2xl text-left transition-all duration-200 group',
                            activeTab === tab.id
                                ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200'
                                : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50 hover:border-slate-300'
                        ]"
                    >
                        <div :class="['w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 transition-colors',
                            activeTab === tab.id ? 'bg-white/20' : 'bg-slate-100 group-hover:bg-indigo-50']">
                            <component :is="tab.icon" :class="['w-4.5 h-4.5', activeTab === tab.id ? 'text-white' : 'text-slate-500 group-hover:text-indigo-600']" stroke-width="2.5" />
                        </div>
                        <div class="min-w-0">
                            <p :class="['text-sm font-bold leading-tight', activeTab === tab.id ? 'text-white' : 'text-slate-800']">{{ tab.label }}</p>
                            <p :class="['text-[11px] leading-tight truncate mt-0.5', activeTab === tab.id ? 'text-indigo-200' : 'text-slate-400']">{{ tab.desc }}</p>
                        </div>
                    </button>
                </nav>

                <!-- Content Card -->
                <div class="lg:col-span-9 bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">

                    <!-- ── TAB: Profil ─────────────────────────────── -->
                    <div v-show="activeTab === 'profil'">
                        <div class="px-8 pt-8 pb-4 border-b border-slate-100">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center">
                                    <User class="w-5 h-5 text-indigo-600" stroke-width="2.5" />
                                </div>
                                <div>
                                    <h2 class="text-lg font-extrabold text-slate-900">Profil Akun</h2>
                                    <p class="text-xs text-slate-400">Perbarui nama dan email yang terdaftar.</p>
                                </div>
                            </div>
                        </div>

                        <form @submit.prevent="submitProfil" class="p-8 space-y-6">
                            <!-- Name -->
                            <div class="space-y-2">
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-400">Nama Lengkap</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <User class="w-4 h-4 text-slate-400" />
                                    </div>
                                    <input
                                        v-model="profilForm.name"
                                        type="text"
                                        required
                                        placeholder="Nama lengkap Anda"
                                        class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-semibold text-slate-800 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white transition-all"
                                    />
                                </div>
                                <p v-if="profilForm.errors.name" class="text-rose-500 text-xs font-semibold flex items-center gap-1">
                                    <AlertCircle class="w-3.5 h-3.5" /> {{ profilForm.errors.name }}
                                </p>
                            </div>

                            <!-- Email -->
                            <div class="space-y-2">
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-400">Alamat Email</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <Mail class="w-4 h-4 text-slate-400" />
                                    </div>
                                    <input
                                        v-model="profilForm.email"
                                        type="email"
                                        required
                                        placeholder="email@sekolah.sch.id"
                                        class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-semibold text-slate-800 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white transition-all"
                                    />
                                </div>
                                <p v-if="profilForm.errors.email" class="text-rose-500 text-xs font-semibold flex items-center gap-1">
                                    <AlertCircle class="w-3.5 h-3.5" /> {{ profilForm.errors.email }}
                                </p>
                            </div>

                            <!-- Info Card -->
                            <div class="flex items-start gap-3 p-4 bg-indigo-50 rounded-2xl border border-indigo-100">
                                <CheckCircle class="w-4.5 h-4.5 text-indigo-500 mt-0.5 flex-shrink-0" />
                                <p class="text-xs text-indigo-700 font-semibold leading-relaxed">
                                    Email digunakan untuk login. Pastikan email yang dimasukkan aktif dan dapat diakses.
                                </p>
                            </div>

                            <!-- Action -->
                            <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                                <span v-if="profilForm.isDirty" class="text-amber-600 text-xs font-bold flex items-center gap-1.5 animate-pulse">
                                    ⚠️ Ada perubahan yang belum disimpan
                                </span>
                                <span v-else class="text-slate-400 text-xs font-semibold">Profil terkini</span>
                                <button
                                    type="submit"
                                    :disabled="profilForm.processing || !profilForm.isDirty"
                                    class="flex items-center gap-2 bg-indigo-600 text-white px-6 py-2.5 rounded-xl font-bold text-sm hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed transition-all shadow-md shadow-indigo-200"
                                >
                                    <Save class="w-4 h-4" />
                                    {{ profilForm.processing ? 'Menyimpan...' : 'Simpan Profil' }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- ── TAB: Keamanan ───────────────────────────── -->
                    <div v-show="activeTab === 'keamanan'">
                        <div class="px-8 pt-8 pb-4 border-b border-slate-100">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-rose-100 rounded-xl flex items-center justify-center">
                                    <Lock class="w-5 h-5 text-rose-600" stroke-width="2.5" />
                                </div>
                                <div>
                                    <h2 class="text-lg font-extrabold text-slate-900">Keamanan Akun</h2>
                                    <p class="text-xs text-slate-400">Ganti password untuk menjaga keamanan akun Anda.</p>
                                </div>
                            </div>
                        </div>

                        <form @submit.prevent="submitPassword" class="p-8 space-y-6">
                            <!-- Current Password -->
                            <div class="space-y-2">
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-400">Password Saat Ini</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <KeyRound class="w-4 h-4 text-slate-400" />
                                    </div>
                                    <input
                                        v-model="pwForm.current_password"
                                        :type="showCurrentPw ? 'text' : 'password'"
                                        required
                                        placeholder="Masukkan password saat ini"
                                        class="w-full pl-11 pr-12 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-semibold text-slate-800 focus:ring-4 focus:ring-rose-500/10 focus:border-rose-500 focus:bg-white transition-all"
                                    />
                                    <button type="button" @click="showCurrentPw = !showCurrentPw"
                                        class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-700 transition-colors">
                                        <component :is="showCurrentPw ? EyeOff : Eye" class="w-4 h-4" />
                                    </button>
                                </div>
                                <p v-if="pwForm.errors.current_password" class="text-rose-500 text-xs font-semibold flex items-center gap-1">
                                    <AlertCircle class="w-3.5 h-3.5" /> {{ pwForm.errors.current_password }}
                                </p>
                            </div>

                            <!-- Divider -->
                            <div class="flex items-center gap-3">
                                <div class="flex-1 h-px bg-slate-100"></div>
                                <span class="text-xs text-slate-400 font-bold">Password Baru</span>
                                <div class="flex-1 h-px bg-slate-100"></div>
                            </div>

                            <!-- New Password -->
                            <div class="space-y-2">
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-400">Password Baru</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <Lock class="w-4 h-4 text-slate-400" />
                                    </div>
                                    <input
                                        v-model="pwForm.password"
                                        :type="showNewPw ? 'text' : 'password'"
                                        required
                                        placeholder="Minimal 8 karakter"
                                        class="w-full pl-11 pr-12 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-semibold text-slate-800 focus:ring-4 focus:ring-rose-500/10 focus:border-rose-500 focus:bg-white transition-all"
                                    />
                                    <button type="button" @click="showNewPw = !showNewPw"
                                        class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-700 transition-colors">
                                        <component :is="showNewPw ? EyeOff : Eye" class="w-4 h-4" />
                                    </button>
                                </div>
                                <p v-if="pwForm.errors.password" class="text-rose-500 text-xs font-semibold flex items-center gap-1">
                                    <AlertCircle class="w-3.5 h-3.5" /> {{ pwForm.errors.password }}
                                </p>
                            </div>

                            <!-- Confirm Password -->
                            <div class="space-y-2">
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-400">Konfirmasi Password Baru</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <Lock class="w-4 h-4 text-slate-400" />
                                    </div>
                                    <input
                                        v-model="pwForm.password_confirmation"
                                        :type="showConfirmPw ? 'text' : 'password'"
                                        required
                                        placeholder="Ulangi password baru"
                                        class="w-full pl-11 pr-12 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-semibold text-slate-800 focus:ring-4 focus:ring-rose-500/10 focus:border-rose-500 focus:bg-white transition-all"
                                    />
                                    <button type="button" @click="showConfirmPw = !showConfirmPw"
                                        class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-700 transition-colors">
                                        <component :is="showConfirmPw ? EyeOff : Eye" class="w-4 h-4" />
                                    </button>
                                </div>
                                <p v-if="pwForm.errors.password_confirmation" class="text-rose-500 text-xs font-semibold flex items-center gap-1">
                                    <AlertCircle class="w-3.5 h-3.5" /> {{ pwForm.errors.password_confirmation }}
                                </p>
                            </div>

                            <!-- Password rules hint -->
                            <div class="grid grid-cols-2 gap-2">
                                <div v-for="rule in ['Minimal 8 karakter', 'Huruf besar & kecil', 'Kombinasi angka', 'Karakter unik (@#$...)']"
                                     :key="rule"
                                     class="flex items-center gap-2 text-xs text-slate-500 font-semibold">
                                    <div class="w-1.5 h-1.5 rounded-full bg-slate-300 flex-shrink-0"></div>
                                    {{ rule }}
                                </div>
                            </div>

                            <!-- Action -->
                            <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                                <span class="text-slate-400 text-xs font-semibold">Gunakan password yang kuat dan unik</span>
                                <button
                                    type="submit"
                                    :disabled="pwForm.processing"
                                    class="flex items-center gap-2 bg-rose-600 text-white px-6 py-2.5 rounded-xl font-bold text-sm hover:bg-rose-700 disabled:opacity-40 transition-all shadow-md shadow-rose-200"
                                >
                                    <Lock class="w-4 h-4" />
                                    {{ pwForm.processing ? 'Mengubah...' : 'Ubah Password' }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- ── TAB: Sistem ──────────────────────────────── -->
                    <div v-show="activeTab === 'sistem'">
                        <div class="px-8 pt-8 pb-4 border-b border-slate-100">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center">
                                    <Globe class="w-5 h-5 text-emerald-600" stroke-width="2.5" />
                                </div>
                                <div>
                                    <h2 class="text-lg font-extrabold text-slate-900">Pengaturan Website</h2>
                                    <p class="text-xs text-slate-400">Konfigurasi identitas, kontak, SEO, dan lokasi sekolah.</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-8 space-y-4">
                            <!-- Quick Info Cards -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                                    <p class="text-xs font-black text-slate-400 uppercase tracking-wider">Nama Sekolah</p>
                                    <p class="text-sm font-bold text-slate-800 truncate">{{ systemSettings?.nama_sekolah || '—' }}</p>
                                </div>
                                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                                    <p class="text-xs font-black text-slate-400 uppercase tracking-wider">Tagline</p>
                                    <p class="text-sm font-bold text-slate-800 truncate">{{ systemSettings?.tagline || '—' }}</p>
                                </div>
                                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                                    <p class="text-xs font-black text-slate-400 uppercase tracking-wider">Email Resmi</p>
                                    <p class="text-sm font-bold text-slate-800 truncate">{{ systemSettings?.email || '—' }}</p>
                                </div>
                                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                                    <p class="text-xs font-black text-slate-400 uppercase tracking-wider">Akreditasi</p>
                                    <p class="text-sm font-bold text-slate-800">{{ systemSettings?.akreditasi || '—' }}</p>
                                </div>
                                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 space-y-1 sm:col-span-2">
                                    <p class="text-xs font-black text-slate-400 uppercase tracking-wider">Alamat</p>
                                    <p class="text-sm font-bold text-slate-800">{{ systemSettings?.alamat || '—' }}</p>
                                </div>
                            </div>

                            <!-- CTA -->
                            <div class="flex items-center justify-between p-5 rounded-2xl bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-100">
                                <div class="flex items-center gap-3">
                                    <Building2 class="w-5 h-5 text-emerald-600 flex-shrink-0" />
                                    <div>
                                        <p class="text-sm font-black text-slate-800">Edit Pengaturan Website Lengkap</p>
                                        <p class="text-xs text-slate-500 mt-0.5">Identitas, kontak, sosmed, peta lokasi & SEO</p>
                                    </div>
                                </div>
                                <Link
                                    :href="route('admin.web.setting')"
                                    class="flex items-center gap-2 bg-emerald-600 text-white px-5 py-2.5 rounded-xl font-bold text-sm hover:bg-emerald-700 transition-all shadow-md shadow-emerald-200 flex-shrink-0"
                                >
                                    Buka <ExternalLink class="w-4 h-4" />
                                </Link>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
