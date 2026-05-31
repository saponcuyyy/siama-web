<script setup>
import { ref, computed, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const flash = computed(() => page.props.flash);
const showFlash = ref(true);

watch(flash, () => {
    showFlash.value = true;
    setTimeout(() => { showFlash.value = false; }, 5000);
}, { deep: true });
import {
    LayoutDashboard,
    Users,
    UserSquare,
    Calendar,
    FileSpreadsheet,
    Library,
    FileText,
    History,
    Settings,
    Globe,
    ChevronLeft,
    ChevronRight,
    ChevronDown,
    Bell,
    LogOut,
    GraduationCap,
    UserCheck,
    Menu,
    X,
    Database,
    ClipboardList,
    Video,
    MonitorPlay,
    BookOpen,
    BarChart3,
    Shield,
    Printer,
} from 'lucide-vue-next';

const icons = {
    LayoutDashboard, Users, UserSquare, Calendar, FileSpreadsheet,
    Library, FileText, History, Settings, Globe, ChevronLeft,
    ChevronRight, ChevronDown, Bell, LogOut, GraduationCap, UserCheck,
    Menu, X, Database, ClipboardList, Video, MonitorPlay, BookOpen,
    BarChart3, Shield, Printer,
};

const page = usePage();
const user = page.props.auth.user;

const isSidebarOpen = ref(true);
const isUserMenuOpen = ref(false);
const isMobileMenuOpen = ref(false);

function avatarUrl(name) {
    const initials = name.split(' ').slice(0, 2).map(s => s[0]).join('').toUpperCase() || 'U';
    const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32"><rect width="32" height="32" fill="#6366f1" rx="50%"/><text x="16" y="16" text-anchor="middle" dominant-baseline="central" fill="white" font-family="system-ui" font-weight="700" font-size="14">${initials}</text></svg>`;
    return `data:image/svg+xml;base64,${btoa(svg)}`;
}

// Track which group menus are open
const openGroups = ref({});

const toggleGroup = (name) => {
    openGroups.value[name] = !openGroups.value[name];
};

const isGroupOpen = (name) => !!openGroups.value[name];

const getUjianHref = () => {
    if (user.roles.includes('siswa')) {
        return route('ujian.index');
    }
    return route('admin.ujian.sesi.index');
};

// Navigation: items can have `children` for submenu
const navigation = [
    { 
        name: 'Dashboard', 
        href: route('dashboard'), 
        icon: 'LayoutDashboard', 
        permission: 'dashboard.view' 
    },
    { 
        name: 'Web Management', 
        href: route('admin.web.dashboard'), 
        icon: 'Globe', 
        permission: 'dashboard.view' 
    },
    // ─── Master Akademik ─────────────────────────────
    { 
        name: 'Data Siswa', 
        href: route('admin.web.siswa.index'), 
        icon: 'UserCheck', 
        permission: 'siswa.view' 
    },
    { 
        name: 'Data Rombel', 
        href: route('admin.web.rombel.index'), 
        icon: 'GraduationCap', 
        permission: 'siswa.view' 
    },
    { 
        name: 'Kartu Ujian', 
        href: route('admin.web.kartu-ujian.index'), 
        icon: 'Printer', 
        permission: 'siswa.view' 
    },
    { 
        name: 'Guru', 
        href: route('admin.web.guru.index'), 
        icon: 'UserSquare', 
        permission: 'guru.view' 
    },
    { 
        name: 'Jadwal', 
        href: '#', 
        icon: 'Calendar', 
        permission: 'jadwal.view' 
    },
            { 
                name: 'Nilai', 
                href: route('admin.ujian.nilai.index'), 
                icon: 'FileSpreadsheet', 
                permission: 'nilai.view' 
            },
    { 
        name: 'Perpustakaan', 
        href: '#', 
        icon: 'Library', 
        permission: 'perpustakaan.view' 
    },
    // ─── Ujian (CBT) ─────────────────────────────────
    // For students: single link to their exam list
    { 
        name: 'Ujian (CBT)', 
        href: route('ujian.index'), 
        icon: 'FileText', 
        permission: 'ujian.view',
        showOnly: 'siswa',
    },
    // For admin/guru: group with submenu
    {
        name: 'Ujian (CBT)',
        icon: 'FileText',
        permission: 'ujian.view',
        hideFor: 'siswa',
        group: 'ujian',
        prefix: '/admin/ujian',
        children: [
            { 
                name: 'Mata Pelajaran', 
                href: route('admin.ujian.mata-pelajaran.index'), 
                icon: 'BookOpen', 
                permission: 'ujian.view' 
            },
            { 
                name: 'Bank Soal', 
                href: route('admin.ujian.bank-soal.index'), 
                icon: 'Database', 
                permission: 'ujian.bank-soal.manage' 
            },
            { 
                name: 'Paket Ujian', 
                href: route('admin.ujian.paket.index'), 
                icon: 'ClipboardList', 
                permission: 'ujian.paket.manage' 
            },
            { 
                name: 'Sesi Ujian', 
                href: route('admin.ujian.sesi.index'), 
                icon: 'MonitorPlay', 
                permission: 'ujian.sesi.manage' 
            },
            { 
                name: 'Laporan Nilai', 
                href: route('admin.ujian.laporan.index'), 
                icon: 'BarChart3', 
                permission: 'ujian.sesi.manage' 
            },
        ]
    },
    {
        name: 'Manajemen User',
        icon: 'Users',
        permission: 'users.view',
        group: 'users',
        prefix: '/admin/users',
        children: [
            { 
                name: 'Users', 
                href: route('admin.users.index'), 
                icon: 'Users', 
                permission: 'users.view' 
            },
            { 
                name: 'Roles', 
                href: route('admin.roles.index'), 
                icon: 'Shield', 
                permission: 'roles.view' 
            },
        ]
    },
    // ─── Lainnya ─────────────────────────────────────
    { 
        name: 'Audit Trail', 
        href: '#', 
        icon: 'History', 
        permission: 'audit.view' 
    },
    { 
        name: 'Pengaturan', 
        href: '#', 
        icon: 'Settings', 
        permission: 'settings.view' 
    },
];

const hasPermission = (permission) => {
    if (user.roles.includes('super_admin')) return true;
    return user.permissions.includes(permission);
};

const isSiswa = computed(() => user.roles.includes('siswa'));

const isActive = (href) => {
    if (!href || href === '#') return false;
    // Get path from URL string
    try {
        const path = new URL(href).pathname;
        return page.props.ziggy?.location 
            ? page.props.ziggy.location.includes(path) 
            : window.location.pathname.startsWith(path);
    } catch {
        return false;
    }
};

const isGroupActive = (prefix) => {
    return window.location.pathname.startsWith(prefix);
};

// Auto-open submenu group if current page is under that prefix
navigation.forEach(item => {
    if (item.group && item.prefix && isGroupActive(item.prefix)) {
        openGroups.value[item.group] = true;
    }
});
</script>

<template>
    <div class="min-h-screen bg-slate-50 flex">
        <!-- Mobile Sidebar Backdrop -->
        <div 
            v-if="isMobileMenuOpen" 
            @click="isMobileMenuOpen = false" 
            class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-20 md:hidden"
        ></div>

        <!-- Sidebar -->
        <aside 
            :class="[
                'bg-white border-r border-slate-200 transition-all duration-300 flex flex-col z-30',
                'fixed inset-y-0 left-0 md:relative md:translate-x-0',
                isMobileMenuOpen ? 'translate-x-0 w-64' : '-translate-x-full md:translate-x-0',
                isSidebarOpen ? 'md:w-64' : 'md:w-20'
            ]"
        >
            <!-- Logo -->
            <div class="h-16 flex items-center justify-between px-4 border-b border-slate-100 flex-shrink-0">
                <div class="flex items-center gap-3">
                    <img src="/images/logo.png" alt="Logo SMA N 2 Perbaungan" class="w-10 h-10 object-contain flex-shrink-0" />
                    <span v-if="isSidebarOpen || isMobileMenuOpen" class="font-bold text-slate-900 tracking-tight transition-opacity duration-300">SIAMA</span>
                </div>
                <!-- Close button for mobile -->
                <button 
                    @click="isMobileMenuOpen = false" 
                    class="p-1 text-slate-400 hover:text-slate-600 md:hidden"
                >
                    <X class="w-5 h-5" />
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-6 px-3 space-y-0.5 custom-scrollbar">
                <template v-for="item in navigation" :key="item.name + (item.group || '')">

                    <!-- Skip items that don't match role visibility -->
                    <template v-if="item.showOnly && !isSiswa && item.showOnly === 'siswa'"></template>
                    <template v-else-if="item.hideFor && isSiswa && item.hideFor === 'siswa'"></template>

                    <!-- Group/Submenu Item (admin only, e.g. Ujian CBT) -->
                    <template v-else-if="item.children && hasPermission(item.permission)">
                        <!-- Group Toggle Button -->
                        <button
                            @click="toggleGroup(item.group)"
                            :class="[
                                'w-full flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all group relative',
                                isGroupActive(item.prefix) ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'
                            ]"
                        >
                            <div :class="[
                                'w-5 h-5 flex items-center justify-center transition-colors flex-shrink-0',
                                isGroupActive(item.prefix) ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-600'
                            ]">
                                <component :is="icons[item.icon]" class="w-5 h-5" stroke-width="2" />
                            </div>
                            <span v-if="isSidebarOpen || isMobileMenuOpen" class="flex-1 text-sm font-semibold text-left transition-opacity duration-300">{{ item.name }}</span>
                            <ChevronDown 
                                v-if="isSidebarOpen || isMobileMenuOpen"
                                class="w-4 h-4 transition-transform duration-200 flex-shrink-0"
                                :class="isGroupOpen(item.group) ? 'rotate-180' : ''"
                            />
                        </button>

                        <!-- Submenu Children -->
                        <div 
                            v-if="isGroupOpen(item.group) && (isSidebarOpen || isMobileMenuOpen)" 
                            class="mt-0.5 ml-4 pl-3 border-l-2 border-indigo-100 space-y-0.5"
                        >
                            <template v-for="child in item.children" :key="child.name">
                                <Link
                                    v-if="hasPermission(child.permission)"
                                    :href="child.href"
                                    @click="isMobileMenuOpen = false"
                                    :class="[
                                        'flex items-center gap-3 px-3 py-2 rounded-xl transition-all group text-sm',
                                        isActive(child.href) ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-200' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900 font-medium'
                                    ]"
                                >
                                    <component :is="child.icon" class="w-4 h-4 flex-shrink-0" stroke-width="2" />
                                    <span>{{ child.name }}</span>
                                </Link>
                            </template>
                        </div>
                    </template>

                    <!-- Regular Single Link -->
                    <Link 
                        v-else-if="!item.children && hasPermission(item.permission)"
                        :href="item.href"
                        @click="isMobileMenuOpen = false"
                        :class="[
                            'flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all group relative',
                            isActive(item.href) ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'
                        ]"
                    >
                        <div :class="[
                            'w-5 h-5 flex items-center justify-center transition-colors flex-shrink-0',
                            isActive(item.href) ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-600'
                        ]">
                            <component :is="icons[item.icon]" class="w-5 h-5" stroke-width="2" />
                        </div>
                        <span v-if="isSidebarOpen || isMobileMenuOpen" class="text-sm font-semibold transition-opacity duration-300">{{ item.name }}</span>
                        
                        <!-- Tooltip for collapsed sidebar -->
                        <div v-if="!isSidebarOpen && !isMobileMenuOpen" class="absolute left-full ml-4 px-2 py-1 bg-slate-900 text-white text-xs rounded opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all whitespace-nowrap z-50">
                            {{ item.name }}
                        </div>
                    </Link>
                </template>
            </nav>

            <!-- Collapse Toggle (Desktop) -->
            <div class="p-4 border-t border-slate-100 hidden md:block">
                <button 
                    @click="isSidebarOpen = !isSidebarOpen"
                    class="w-full flex items-center justify-center p-2 rounded-xl bg-slate-50 text-slate-400 hover:text-slate-600 transition-colors"
                >
                    <ChevronLeft v-if="isSidebarOpen" class="w-5 h-5" />
                    <ChevronRight v-else class="w-5 h-5" />
                </button>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Navbar -->
            <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 md:px-8 flex-shrink-0 z-10">
                <div class="flex items-center gap-4">
                    <button 
                        @click="isMobileMenuOpen = !isMobileMenuOpen"
                        class="p-2 -ml-2 text-slate-500 hover:text-slate-900 md:hidden"
                    >
                        <Menu class="w-6 h-6" />
                    </button>
                    <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest hidden sm:block">
                        Academic System Portal
                    </h2>
                </div>

                <div class="flex items-center gap-6">
                    <button class="p-2 text-slate-400 hover:text-indigo-600 transition-colors relative group">
                        <Bell class="w-5 h-5" />
                        <span class="absolute top-2 right-2 w-2 h-2 bg-rose-500 rounded-full border-2 border-white"></span>
                    </button>

                    <div class="relative">
                        <button 
                            @click="isUserMenuOpen = !isUserMenuOpen"
                            class="flex items-center gap-3 p-1 rounded-full hover:bg-slate-50 transition-all border border-transparent hover:border-slate-100"
                        >
                            <div class="w-8 h-8 bg-indigo-100 rounded-full overflow-hidden border border-indigo-200">
                                <img :src="avatarUrl(user.name)" alt="Avatar" />
                            </div>
                            <div class="hidden md:block text-left">
                                <p class="text-xs font-black text-slate-900 leading-none">{{ user.name }}</p>
                                <p class="text-[9px] text-indigo-600 font-black uppercase mt-1 tracking-tighter">{{ user.roles[0] }}</p>
                            </div>
                        </button>

                        <div 
                            v-if="isUserMenuOpen"
                            class="absolute right-0 mt-3 w-56 bg-white rounded-2xl shadow-2xl shadow-slate-200/50 border border-slate-100 py-2 z-50 animate-in fade-in zoom-in duration-200"
                        >
                            <div class="px-4 py-2 border-b border-slate-50 mb-1">
                                <p class="text-xs font-bold text-slate-400 uppercase">Menu Pengguna</p>
                            </div>
                            <Link href="#" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                                Profil Saya
                            </Link>
                            <Link href="#" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                                Pengaturan Akun
                            </Link>
                            <div class="border-t border-slate-100 my-1"></div>
                            <Link :href="route('logout')" method="post" as="button" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm font-bold text-rose-600 hover:bg-rose-50 transition-colors">
                                <LogOut class="w-4 h-4" />
                                Keluar
                            </Link>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Flash Messages -->
            <div class="px-8 pt-6">
                <transition-group name="list">
                    <div v-if="flash?.success && showFlash" key="success" class="bg-green-600 text-white px-6 py-4 rounded-2xl text-sm font-bold flex items-center gap-3 shadow-lg shadow-green-200 animate-in fade-in slide-in-from-top-2">
                        {{ flash.success }}
                    </div>
                    <div v-if="flash?.error && showFlash" key="error" class="bg-rose-600 text-white px-6 py-4 rounded-2xl text-sm font-bold flex items-center gap-3 shadow-lg shadow-rose-200 animate-in fade-in slide-in-from-top-2">
                        {{ flash.error }}
                    </div>
                </transition-group>
            </div>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-8 custom-scrollbar">
                <slot />
            </main>
        </div>
    </div>
</template>

<style>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}
</style>
