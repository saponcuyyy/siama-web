<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { 
    LayoutDashboard, 
    Globe, 
    FileText, 
    Newspaper, 
    Megaphone, 
    Image as ImageIcon, 
    LayoutList, 
    Mail, 
    Settings, 
    ExternalLink,
    ChevronLeft,
    ChevronRight,
    LogOut,
    Building2,
    ListTree,
    FolderTree,
    GraduationCap
} from 'lucide-vue-next';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const flash = computed(() => page.props.flash);
const sidebarOpen = ref(true);
const showFlash = ref(true);

setTimeout(() => showFlash.value = false, 5000);

const navGroups = [
    {
        label: 'Utama',
        items: [
            { name: 'Dashboard Akademik', href: route('dashboard'), icon: LayoutDashboard },
            { name: 'Dashboard CMS', href: route('admin.web.dashboard'), icon: Globe },
        ],
    },
    {
        label: 'Konten Website',
        items: [
            { name: 'Menu Navigasi', href: route('admin.web.menu.index'), icon: ListTree },
            { name: 'Slider / Hero', href: route('admin.web.slider.index'), icon: ImageIcon },
            { name: 'Halaman Statis', href: route('admin.web.halaman.index'), icon: FileText },
            { name: 'Fasilitas Sekolah', href: route('admin.web.fasilitas.index'), icon: Building2 },
        ],
    },
    {
        label: 'Publikasi',
        items: [
            { name: 'Kategori Berita', href: route('admin.web.kategori-berita.index'), icon: FolderTree },
            { name: 'Berita & Artikel', href: route('admin.web.berita.index'), icon: Newspaper },
            { name: 'Pengumuman', href: route('admin.web.pengumuman.index'), icon: Megaphone },
            { name: 'Galeri & Album', href: route('admin.web.album.index'), icon: ImageIcon },
        ],
    },
    {
        label: 'Interaksi & Sistem',
        items: [
            { name: 'Pesan Masuk', href: route('admin.web.pesan.index'), icon: Mail },
            { name: 'Data Kelulusan', href: route('admin.web.kelulusan.index'), icon: GraduationCap },
            { name: 'Pengaturan Web', href: route('admin.web.setting'), icon: Settings },
        ],
    },
];

const isActive = (href) => {
    try {
        const url = new URL(href);
        return page.url === url.pathname || page.url.startsWith(url.pathname + '/');
    } catch (e) {
        return page.url === href || page.url.startsWith(href + '/');
    }
};
</script>

<template>
    <div class="min-h-screen bg-slate-50 flex font-sans">
        <!-- Sidebar -->
        <aside :class="['flex flex-col bg-slate-900 transition-all duration-300 flex-shrink-0 z-20', sidebarOpen ? 'w-64' : 'w-20']">
            <!-- Logo -->
            <div class="h-16 flex items-center gap-3 px-6 border-b border-white/5">
                <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center flex-shrink-0 font-black text-white text-sm shadow-lg shadow-blue-500/20">S</div>
                <div v-if="sidebarOpen" class="overflow-hidden whitespace-nowrap">
                    <p class="text-white font-black text-sm leading-tight">SIAMA CMS</p>
                    <p class="text-slate-500 font-bold text-[9px] uppercase tracking-tighter">Web Management</p>
                </div>
            </div>

            <!-- Nav -->
            <nav class="flex-1 overflow-y-auto py-6 px-3 space-y-6 custom-scrollbar">
                <div v-for="group in navGroups" :key="group.label">
                    <p v-if="sidebarOpen" class="text-slate-500 text-[10px] font-black uppercase tracking-[0.2em] px-3 mb-3">{{ group.label }}</p>
                    <div class="space-y-1">
                        <Link
                            v-for="item in group.items"
                            :key="item.name"
                            :href="item.href"
                            :class="['flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all relative group',
                                isActive(item.href)
                                    ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20'
                                    : 'text-slate-400 hover:bg-white/5 hover:text-white'
                            ]"
                        >
                            <component :is="item.icon" class="w-5 h-5 flex-shrink-0" stroke-width="2.5" />
                            <span v-if="sidebarOpen" class="truncate">{{ item.name }}</span>
                            
                            <!-- Tooltip -->
                            <div v-if="!sidebarOpen" class="absolute left-full ml-4 px-2 py-1 bg-white text-slate-900 text-[10px] font-black rounded opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all whitespace-nowrap z-50 shadow-xl uppercase tracking-tighter">
                                {{ item.name }}
                            </div>
                        </Link>
                    </div>
                </div>
            </nav>

            <!-- Collapse toggle -->
            <div class="p-4 border-t border-white/5">
                <button @click="sidebarOpen = !sidebarOpen" class="w-full flex items-center justify-center p-2 rounded-xl bg-white/5 text-slate-400 hover:text-white transition-colors">
                    <ChevronLeft v-if="sidebarOpen" class="w-5 h-5" />
                    <ChevronRight v-else class="w-5 h-5" />
                </button>
            </div>
        </aside>

        <!-- Main -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Topbar -->
            <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-8 flex-shrink-0 shadow-sm z-10">
                <div class="flex items-center gap-4">
                    <a :href="route('home')" target="_blank" class="flex items-center gap-2 text-xs font-bold text-slate-400 hover:text-blue-600 transition-colors uppercase tracking-widest group">
                        <ExternalLink class="w-4 h-4" />
                        Lihat Website Utama
                    </a>
                </div>
                <div class="flex items-center gap-6">
                    <div class="text-right hidden sm:block">
                        <p class="text-xs font-black text-slate-900 leading-none">{{ user?.name }}</p>
                        <p class="text-[9px] text-blue-600 font-black mt-1 uppercase tracking-tighter">{{ user?.roles?.[0] }}</p>
                    </div>
                    <Link :href="route('logout')" method="post" as="button"
                        class="text-xs font-black text-rose-600 hover:bg-rose-50 border border-rose-100 px-4 py-2 rounded-xl transition-all active:scale-95 flex items-center gap-2">
                        <LogOut class="w-4 h-4" />
                        Keluar
                    </Link>
                </div>
            </header>

            <!-- Flash Messages -->
            <div class="px-8 pt-6">
                <transition-group name="list">
                    <div v-if="flash?.success && showFlash" key="success" class="bg-green-600 text-white px-6 py-4 rounded-2xl text-sm font-bold flex items-center gap-3 shadow-lg shadow-green-200 animate-in fade-in slide-in-from-top-2">
                        <span class="text-xl">✨</span>
                        {{ flash.success }}
                    </div>
                    <div v-if="flash?.error && showFlash" key="error" class="bg-rose-600 text-white px-6 py-4 rounded-2xl text-sm font-bold flex items-center gap-3 shadow-lg shadow-rose-200 animate-in fade-in slide-in-from-top-2">
                        <span class="text-xl">❌</span>
                        {{ flash.error }}
                    </div>
                </transition-group>
            </div>

            <!-- Content -->
            <main class="flex-1 overflow-y-auto p-8 custom-scrollbar">
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
.list-enter-active, .list-leave-active { transition: all 0.5s ease; }
.list-enter-from, .list-leave-to { opacity: 0; transform: translateY(-30px); }

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
