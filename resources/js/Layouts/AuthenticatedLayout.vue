<script setup>
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = page.props.auth.user;

const isSidebarOpen = ref(true);
const isUserMenuOpen = ref(false);

const navigation = [
    { name: 'Dashboard', href: route('dashboard'), icon: 'LayoutDashboard', permission: 'dashboard.view' },
    { name: 'Siswa', href: '#', icon: 'Users', permission: 'siswa.view' },
    { name: 'Guru', href: '#', icon: 'UserSquare', permission: 'guru.view' },
    { name: 'Jadwal', href: '#', icon: 'Calendar', permission: 'jadwal.view' },
    { name: 'Nilai', href: '#', icon: 'FileSpreadsheet', permission: 'nilai.view' },
    { name: 'Perpustakaan', href: '#', icon: 'Library', permission: 'perpustakaan.view' },
    { name: 'Ujian (CBT)', href: '#', icon: 'FileText', permission: 'ujian.view' },
    { name: 'Audit Trail', href: '#', icon: 'History', permission: 'audit.view' },
    { name: 'Pengaturan', href: '#', icon: 'Settings', permission: 'settings.view' },
];

const hasPermission = (permission) => {
    return user.permissions.includes(permission);
};
</script>

<template>
    <div class="min-h-screen bg-slate-50 flex">
        <!-- Sidebar -->
        <aside 
            :class="[
                'bg-white border-r border-slate-200 transition-all duration-300 flex flex-col',
                isSidebarOpen ? 'w-64' : 'w-20'
            ]"
        >
            <div class="h-16 flex items-center px-6 gap-3 border-b border-slate-100">
                <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center flex-shrink-0 shadow-lg shadow-indigo-100">
                    <span class="text-white font-bold">S</span>
                </div>
                <span v-if="isSidebarOpen" class="font-bold text-slate-900 tracking-tight transition-opacity duration-300">SIAMA</span>
            </div>

            <nav class="flex-1 overflow-y-auto py-6 px-3 space-y-1">
                <template v-for="item in navigation" :key="item.name">
                    <Link 
                        v-if="hasPermission(item.permission)"
                        :href="item.href"
                        :class="[
                            'flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all group',
                            $page.url === item.href ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'
                        ]"
                    >
                        <div :class="[
                            'w-5 h-5 flex items-center justify-center',
                            $page.url === item.href ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-600'
                        ]">
                            <!-- Icon Placeholder -->
                            <component :is="item.icon" class="w-5 h-5" />
                        </div>
                        <span v-if="isSidebarOpen" class="text-sm font-medium transition-opacity duration-300">{{ item.name }}</span>
                    </Link>
                </template>
            </nav>

            <div class="p-4 border-t border-slate-100">
                <button 
                    @click="isSidebarOpen = !isSidebarOpen"
                    class="w-full flex items-center justify-center p-2 rounded-xl bg-slate-50 text-slate-400 hover:text-slate-600 transition-colors"
                >
                    <svg v-if="isSidebarOpen" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                </button>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Navbar -->
            <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-8 flex-shrink-0 z-10">
                <div class="flex items-center gap-4">
                    <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wider">
                        Sistem Informasi Akademik SMA
                    </h2>
                </div>

                <div class="flex items-center gap-4">
                    <button class="p-2 text-slate-400 hover:text-slate-600 transition-colors relative">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                        <span class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full border-2 border-white"></span>
                    </button>

                    <div class="relative">
                        <button 
                            @click="isUserMenuOpen = !isUserMenuOpen"
                            class="flex items-center gap-2 p-1 rounded-full hover:bg-slate-50 transition-colors"
                        >
                            <div class="w-8 h-8 bg-slate-200 rounded-full overflow-hidden border border-slate-300">
                                <img src="https://ui-avatars.com/api/?name=Super+Admin&background=6366f1&color=fff" alt="Avatar" />
                            </div>
                            <div class="hidden md:block text-left">
                                <p class="text-sm font-bold text-slate-900 leading-none">{{ user.name }}</p>
                                <p class="text-[10px] text-slate-500 font-medium uppercase mt-0.5">{{ user.roles[0] }}</p>
                            </div>
                        </button>

                        <div 
                            v-if="isUserMenuOpen"
                            class="absolute right-0 mt-2 w-48 bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 py-2 z-50 animate-in fade-in zoom-in duration-200"
                        >
                            <Link href="#" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors">Profil Saya</Link>
                            <Link href="#" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors">Pengaturan Akun</Link>
                            <div class="border-t border-slate-100 my-1"></div>
                            <Link :href="route('logout')" method="post" as="button" class="w-full text-left block px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                Keluar
                            </Link>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-8">
                <slot />
            </main>
        </div>
    </div>
</template>
