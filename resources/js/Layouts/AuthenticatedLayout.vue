<script setup>
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
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
    Bell,
    LogOut
} from 'lucide-vue-next';

const page = usePage();
const user = page.props.auth.user;

const isSidebarOpen = ref(true);
const isUserMenuOpen = ref(false);

const navigation = [
    { name: 'Dashboard', href: route('dashboard'), icon: LayoutDashboard, permission: 'dashboard.view' },
    { name: 'Web Management', href: route('admin.web.dashboard'), icon: Globe, permission: 'dashboard.view' }, // Accessible by admin
    { name: 'Siswa', href: '#', icon: Users, permission: 'siswa.view' },
    { name: 'Guru', href: '#', icon: UserSquare, permission: 'guru.view' },
    { name: 'Jadwal', href: '#', icon: Calendar, permission: 'jadwal.view' },
    { name: 'Nilai', href: '#', icon: FileSpreadsheet, permission: 'nilai.view' },
    { name: 'Perpustakaan', href: '#', icon: Library, permission: 'perpustakaan.view' },
    { name: 'Ujian (CBT)', href: '#', icon: FileText, permission: 'ujian.view' },
    { name: 'Audit Trail', href: '#', icon: History, permission: 'audit.view' },
    { name: 'Pengaturan', href: '#', icon: Settings, permission: 'settings.view' },
];

const hasPermission = (permission) => {
    // Super Admin has all access
    if (user.roles.includes('super_admin')) return true;
    return user.permissions.includes(permission);
};
</script>

<template>
    <div class="min-h-screen bg-slate-50 flex">
        <!-- Sidebar -->
        <aside 
            :class="[
                'bg-white border-r border-slate-200 transition-all duration-300 flex flex-col z-20',
                isSidebarOpen ? 'w-64' : 'w-20'
            ]"
        >
            <div class="h-16 flex items-center px-6 gap-3 border-b border-slate-100 flex-shrink-0">
                <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center flex-shrink-0 shadow-lg shadow-indigo-100">
                    <span class="text-white font-bold">S</span>
                </div>
                <span v-if="isSidebarOpen" class="font-bold text-slate-900 tracking-tight transition-opacity duration-300">SIAMA</span>
            </div>

            <nav class="flex-1 overflow-y-auto py-6 px-3 space-y-1 custom-scrollbar">
                <template v-for="item in navigation" :key="item.name">
                    <Link 
                        v-if="hasPermission(item.permission)"
                        :href="item.href"
                        :class="[
                            'flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all group relative',
                            $page.url === item.href || $page.url.startsWith(item.href + '/') ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'
                        ]"
                    >
                        <div :class="[
                            'w-5 h-5 flex items-center justify-center transition-colors',
                            $page.url === item.href || $page.url.startsWith(item.href + '/') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-600'
                        ]">
                            <component :is="item.icon" class="w-5 h-5" stroke-width="2" />
                        </div>
                        <span v-if="isSidebarOpen" class="text-sm font-semibold transition-opacity duration-300">{{ item.name }}</span>
                        
                        <!-- Tooltip for collapsed sidebar -->
                        <div v-if="!isSidebarOpen" class="absolute left-full ml-4 px-2 py-1 bg-slate-900 text-white text-xs rounded opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all whitespace-nowrap z-50">
                            {{ item.name }}
                        </div>
                    </Link>
                </template>
            </nav>

            <div class="p-4 border-t border-slate-100">
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
            <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-8 flex-shrink-0 z-10">
                <div class="flex items-center gap-4">
                    <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest">
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
                                <img :src="`https://ui-avatars.com/api/?name=${user.name}&background=6366f1&color=fff`" alt="Avatar" />
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
