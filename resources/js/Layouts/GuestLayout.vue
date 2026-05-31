<script setup>
import { Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    menus: Array,
    settings: Object
});

const isScrolled = ref(false);
const mobileMenuOpen = ref(false);

const handleScroll = () => {
    isScrolled.value = window.scrollY > 50;
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <div class="min-h-screen bg-white font-sans text-slate-900 selection:bg-blue-100 selection:text-blue-700">
        <!-- Navbar -->
        <nav 
            :class="[
                'fixed top-0 inset-x-0 z-50 transition-all duration-500 py-4',
                isScrolled ? 'bg-white/80 backdrop-blur-xl shadow-sm py-2' : 'bg-transparent'
            ]"
        >
            <div class="max-w-7xl mx-auto px-6 flex items-center justify-between">
                <!-- Logo -->
                <Link href="/" class="flex items-center gap-3 group">
                    <img 
                        src="/images/logo-small.png" 
                        alt="Logo SMA Negeri 2 Perbaungan"
                        class="w-10 h-10 object-contain group-hover:scale-110 transition-transform duration-300 drop-shadow-md"
                    />
                    <div>
                        <h1 :class="['font-black leading-tight transition-colors', isScrolled ? 'text-slate-900' : 'text-slate-900']">
                            {{ settings?.nama_sekolah || 'SMA NEGERI 2' }}
                        </h1>
                        <p class="text-[10px] uppercase tracking-widest font-bold text-blue-600">Perbaungan</p>
                    </div>
                </Link>

                <!-- Desktop Menu -->
                <div class="hidden lg:flex items-center gap-8">
                    <div v-for="menu in menus" :key="menu.id" class="relative group">
                        <Link 
                            :href="menu.url" 
                            class="text-sm font-bold text-slate-700 hover:text-blue-600 transition-colors py-2 block"
                        >
                            {{ menu.label }}
                        </Link>
                        <!-- Dropdown -->
                        <div v-if="menu.children?.length" class="absolute top-full left-0 mt-2 w-48 bg-white rounded-2xl shadow-xl border border-slate-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform translate-y-2 group-hover:translate-y-0 p-2">
                            <Link 
                                v-for="child in menu.children" 
                                :key="child.id" 
                                :href="child.url"
                                class="block px-4 py-2 text-xs font-bold text-slate-600 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-all"
                            >
                                {{ child.label }}
                            </Link>
                        </div>
                    </div>
                    
                    <Link 
                        v-if="false"
                        :href="route('login')" 
                        class="bg-slate-900 text-white px-6 py-2.5 rounded-full text-sm font-bold hover:bg-blue-600 transition-all active:scale-95 shadow-xl shadow-slate-200"
                    >
                        Login Portal
                    </Link>
                </div>

                <!-- Mobile Toggle -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden w-10 h-10 flex flex-col items-center justify-center gap-1.5">
                    <div :class="['w-6 h-0.5 bg-slate-900 rounded-full transition-all', mobileMenuOpen ? 'rotate-45 translate-y-2' : '']"></div>
                    <div :class="['w-6 h-0.5 bg-slate-900 rounded-full transition-all', mobileMenuOpen ? 'opacity-0' : '']"></div>
                    <div :class="['w-6 h-0.5 bg-slate-900 rounded-full transition-all', mobileMenuOpen ? '-rotate-45 -translate-y-2' : '']"></div>
                </button>
            </div>
        </nav>

        <!-- Mobile Menu Overlay -->
        <transition name="mobile-menu">
            <div v-if="mobileMenuOpen" class="fixed inset-0 z-40 bg-white lg:hidden pt-24 px-6">
                <div class="flex flex-col gap-6">
                    <div v-for="menu in menus" :key="menu.id" class="space-y-4">
                        <Link :href="menu.url" @click="mobileMenuOpen = false" class="text-2xl font-black text-slate-900 block">
                            {{ menu.label }}
                        </Link>
                        <div v-if="menu.children?.length" class="pl-4 border-l-2 border-blue-100 flex flex-col gap-3">
                            <Link 
                                v-for="child in menu.children" 
                                :key="child.id" 
                                :href="child.url"
                                @click="mobileMenuOpen = false"
                                class="text-lg font-bold text-slate-500"
                            >
                                {{ child.label }}
                            </Link>
                        </div>
                    </div>

                    <Link 
                        v-if="false"
                        :href="route('login')" 
                        class="bg-blue-600 text-white w-full py-4 rounded-2xl text-center font-black text-xl shadow-xl shadow-blue-100 mt-2"
                    >
                        Login Portal
                    </Link>
                </div>
            </div>
        </transition>

        <!-- Page Content -->
        <main>
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-slate-900 text-white pt-20 pb-10">
            <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12">
                <div class="space-y-6">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center shadow-md flex-shrink-0 p-1">
                            <img 
                                src="/images/logo-small.png" 
                                alt="Logo SMA Negeri 2 Perbaungan"
                                class="w-full h-full object-contain"
                            />
                        </div>
                        <h2 class="font-black text-xl leading-tight uppercase">{{ settings?.nama_sekolah || 'SMA N 2 Perbaungan' }}</h2>
                    </div>
                    <p class="text-slate-400 text-sm leading-relaxed">{{ settings?.tagline || 'Mencetak generasi unggul, berkarakter, dan berdaya saing global.' }}</p>
                </div>
                <div>
                    <h3 class="font-black text-lg mb-6 uppercase tracking-wider text-blue-400">Navigasi</h3>
                    <ul class="space-y-4">
                        <li v-for="menu in menus" :key="menu.id">
                            <Link :href="menu.url" class="text-slate-400 hover:text-white transition-colors font-bold text-sm">{{ menu.label }}</Link>
                        </li>
                        <li class="pt-2">
                            <Link 
                                :href="route('public.kelulusan')" 
                                class="inline-flex items-center gap-2 bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 px-4 py-2 rounded-xl font-black text-sm hover:bg-emerald-500/20 hover:text-emerald-300 transition-all"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                                </svg>
                                Pengumuman Kelulusan
                            </Link>
                        </li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-black text-lg mb-6 uppercase tracking-wider text-blue-400">Kontak Kami</h3>
                    <ul class="space-y-4 text-sm font-bold text-slate-400">
                        <li class="flex items-start gap-3">
                            <span class="text-blue-400">📍</span>
                            <span>{{ settings?.alamat || 'Jl. Dusun Duku Desa Melati II, Serdang Bedagai' }}</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="text-blue-400">📞</span>
                            <span>{{ settings?.telepon || '(061) 1234567' }}</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="text-blue-400">✉️</span>
                            <span>{{ settings?.email || 'info@sman2perbaungan.sch.id' }}</span>
                        </li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-black text-lg mb-6 uppercase tracking-wider text-blue-400">Media Sosial</h3>
                    <div class="flex flex-wrap gap-4">
                        <a v-if="settings?.website" :href="settings.website" target="_blank" class="w-10 h-10 bg-slate-800 rounded-xl flex items-center justify-center hover:bg-emerald-600 transition-all font-black text-xs" title="Website Resmi">WWW</a>
                        <a v-if="settings?.facebook" :href="settings.facebook" target="_blank" class="w-10 h-10 bg-slate-800 rounded-xl flex items-center justify-center hover:bg-blue-600 transition-all font-black text-xs" title="Facebook">FB</a>
                        <a v-if="settings?.instagram" :href="settings.instagram" target="_blank" class="w-10 h-10 bg-slate-800 rounded-xl flex items-center justify-center hover:bg-pink-600 transition-all font-black text-xs" title="Instagram">IG</a>
                        <a v-if="settings?.youtube" :href="settings.youtube" target="_blank" class="w-10 h-10 bg-slate-800 rounded-xl flex items-center justify-center hover:bg-red-600 transition-all font-black text-xs" title="YouTube">YT</a>
                        <a v-if="settings?.tiktok" :href="settings.tiktok" target="_blank" class="w-10 h-10 bg-slate-800 rounded-xl flex items-center justify-center hover:bg-black hover:text-white transition-all font-black text-xs" title="TikTok">TK</a>
                    </div>
                </div>
            </div>
            <div class="max-w-7xl mx-auto px-6 border-t border-slate-800 mt-20 pt-10 flex flex-col md:flex-row justify-between items-center gap-4 text-xs font-bold text-slate-500 uppercase tracking-widest">
                <p>&copy; {{ new Date().getFullYear() }} {{ settings?.nama_sekolah || 'SMA Negeri 2 Perbaungan' }}. All rights reserved.</p>
                <p>Designed with ❤️ by SIAMA Team</p>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.mobile-menu-enter-active, .mobile-menu-leave-active { transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); }
.mobile-menu-enter-from, .mobile-menu-leave-to { opacity: 0; transform: translateY(-20px); }
</style>
