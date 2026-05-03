<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Log in" />

    <div class="min-h-screen bg-white flex overflow-hidden font-sans">
        <!-- Left Side: Visual/Branding (Hidden on mobile) -->
        <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 30%, #3730a3 60%, #1d4ed8 100%);">
            <!-- CSS-only Decorative Background -->
            <div class="absolute inset-0 z-0 overflow-hidden">
                <!-- Grid pattern -->
                <svg class="absolute inset-0 w-full h-full opacity-10" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                            <path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="0.5"/>
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#grid)" />
                </svg>
                <!-- Floating orbs -->
                <div class="absolute top-1/4 left-1/4 w-64 h-64 rounded-full" style="background: radial-gradient(circle, rgba(99,102,241,0.5) 0%, transparent 70%);"></div>
                <div class="absolute bottom-1/3 right-1/4 w-96 h-96 rounded-full" style="background: radial-gradient(circle, rgba(59,130,246,0.4) 0%, transparent 70%);"></div>
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] rounded-full" style="background: radial-gradient(circle, rgba(139,92,246,0.2) 0%, transparent 70%);"></div>
                <!-- Decorative circles -->
                <div class="absolute -top-10 -right-10 w-72 h-72 border border-white/10 rounded-full"></div>
                <div class="absolute -top-16 -right-16 w-96 h-96 border border-white/5 rounded-full"></div>
                <div class="absolute bottom-0 left-0 w-64 h-64 border border-white/10 rounded-full -translate-x-1/2 translate-y-1/2"></div>
            </div>
            
            <!-- Animated Shapes -->
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-indigo-500 rounded-full mix-blend-screen filter blur-3xl opacity-20 animate-pulse"></div>
            <div class="absolute -bottom-24 -right-24 w-[30rem] h-[30rem] bg-blue-400 rounded-full mix-blend-screen filter blur-3xl opacity-20 animate-pulse delay-1000"></div>

            <div class="relative z-10 w-full flex flex-col justify-between p-16">
                <!-- Logo/Brand -->
                <div class="flex items-center gap-4 animate-in fade-in slide-in-from-top-4 duration-700">
                    <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center shadow-2xl">
                        <span class="text-indigo-600 font-black text-2xl">S</span>
                    </div>
                    <div class="text-white">
                        <h1 class="text-xl font-black tracking-tight leading-none uppercase">Siama</h1>
                        <p class="text-[10px] font-bold tracking-[0.3em] uppercase opacity-60">System Akademik</p>
                    </div>
                </div>

                <!-- Featured Text -->
                <div class="max-w-md space-y-6 animate-in fade-in slide-in-from-left-8 duration-1000 delay-300">
                    <h2 class="text-5xl font-black text-white leading-tight">
                        Wujudkan <span class="text-blue-400">Pendidikan</span> Berkualitas Untuk Masa Depan.
                    </h2>
                    <p class="text-indigo-100 text-lg font-medium opacity-80 leading-relaxed">
                        Kelola data akademik, kehadiran, dan laporan perkembangan siswa dengan sistem terpadu yang modern dan aman.
                    </p>
                </div>

                <!-- Footer Info -->
                <div class="text-white/40 text-xs font-bold uppercase tracking-widest animate-in fade-in duration-1000 delay-1000">
                    &copy; 2024 SMA Negeri 2 Perbaungan • Versi 2.0.0
                </div>
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 sm:p-12 bg-slate-50 relative">
            <!-- Mobile Logo (Shown only on small screens) -->
            <div class="lg:hidden absolute top-12 left-1/2 -translate-x-1/2 flex flex-col items-center gap-3">
                <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center shadow-xl shadow-indigo-100 mb-2">
                    <span class="text-white font-black text-3xl">S</span>
                </div>
            </div>

            <div class="w-full max-w-[420px] bg-white p-8 sm:p-10 rounded-[2.5rem] shadow-2xl shadow-slate-200/60 border border-white animate-in fade-in zoom-in-95 duration-700 delay-200">
                <div class="mb-10 text-center lg:text-left">
                    <h3 class="text-3xl font-black text-slate-900 tracking-tight">Selamat Datang</h3>
                    <p class="text-slate-500 font-medium mt-2">Silakan masuk untuk melanjutkan ke dashboard.</p>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="space-y-2 group">
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest px-1 group-focus-within:text-indigo-600 transition-colors" for="email">Alamat Email</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12.75V12a10 10 0 1 0-4.5 8.43"/><circle cx="12" cy="12" r="4"/><path d="M22 13a2 2 0 0 0-4 0v4.25"/></svg>
                            </span>
                            <input
                                id="email"
                                type="email"
                                v-model="form.email"
                                class="w-full bg-slate-50 border-none ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-500 rounded-2xl py-3.5 pl-12 pr-4 transition-all placeholder:text-slate-300 font-medium text-slate-900"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="nama@siama.sch.id"
                            />
                        </div>
                        <p v-if="form.errors.email" class="text-red-500 text-[10px] font-bold px-1 mt-1 animate-in fade-in slide-in-from-top-1">{{ form.errors.email }}</p>
                    </div>

                    <div class="space-y-2 group">
                        <div class="flex justify-between items-center px-1">
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest group-focus-within:text-indigo-600 transition-colors" for="password">Kata Sandi</label>
                            <Link href="#" class="text-[10px] font-black text-indigo-600 uppercase tracking-widest hover:text-indigo-500 transition-colors">
                                Lupa sandi?
                            </Link>
                        </div>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </span>
                            <input
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                v-model="form.password"
                                class="w-full bg-slate-50 border-none ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-500 rounded-2xl py-3.5 pl-12 pr-12 transition-all placeholder:text-slate-300 font-medium text-slate-900"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••"
                            />
                            <button 
                                type="button" 
                                @click="showPassword = !showPassword"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-indigo-600 transition-colors"
                            >
                                <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88 2 2m7.88 7.88L2 2m7.88 7.88c.45.45.62.91.62 1.62 0 1.38-1.12 2.5-2.5 2.5-.71 0-1.17-.17-1.62-.62m-1.38-1.38L2 2m1.12 1.12C4.12 4.12 6.12 5 10 5c6.12 0 10 7 10 7a18.25 18.25 0 0 1-2.12 3.12m-3.88 3.88C12.12 19.88 11.12 20 10 20c-6.12 0-10-7-10-7a18.25 18.25 0 0 1 2.12-3.12M22 22 2 2"/></svg>
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="text-red-500 text-[10px] font-bold px-1 mt-1 animate-in fade-in slide-in-from-top-1">{{ form.errors.password }}</p>
                    </div>

                    <div class="flex items-center justify-between py-2">
                        <label class="flex items-center group cursor-pointer">
                            <div class="relative flex items-center justify-center w-5 h-5">
                                <input type="checkbox" v-model="form.remember" class="peer absolute inset-0 opacity-0 cursor-pointer" />
                                <div class="w-5 h-5 border-2 border-slate-200 rounded-lg bg-white peer-checked:bg-indigo-600 peer-checked:border-indigo-600 transition-all group-hover:border-indigo-300"></div>
                                <svg class="absolute w-3 h-3 text-white scale-0 peer-checked:scale-100 transition-transform pointer-events-none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </div>
                            <span class="ms-3 text-xs font-bold text-slate-500 group-hover:text-slate-900 transition-colors uppercase tracking-widest">Ingat Saya</span>
                        </label>
                    </div>

                    <button
                        class="w-full bg-indigo-600 text-white rounded-2xl py-4 px-6 font-black text-xs uppercase tracking-[0.2em] shadow-xl shadow-indigo-100 hover:bg-indigo-700 hover:shadow-indigo-200 active:scale-[0.97] transition-all focus:outline-none focus:ring-4 focus:ring-indigo-100 disabled:opacity-70 disabled:cursor-not-allowed group"
                        :disabled="form.processing"
                    >
                        <div class="flex items-center justify-center gap-2">
                            <span v-if="form.processing" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                            <span>{{ form.processing ? 'Memproses...' : 'Masuk Sekarang' }}</span>
                            <svg v-if="!form.processing" class="w-4 h-4 group-hover:translate-x-1 transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                        </div>
                    </button>
                </form>

                <div class="mt-10 pt-8 border-t border-slate-50 text-center">
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest leading-relaxed">
                        Butuh bantuan? Silakan hubungi <a href="#" class="text-indigo-600 hover:underline">Tim IT Sekolah</a>
                    </p>
                </div>
            </div>
            
            <!-- Mobile Footer -->
            <div class="lg:hidden absolute bottom-8 text-[10px] text-slate-400 font-bold uppercase tracking-widest">
                &copy; 2024 SMA Negeri 2 Perbaungan
            </div>
        </div>
    </div>
</template>

<style>
/* Smooth typography */
body {
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

/* Custom transitions */
.animate-in {
    animation-duration: 0.7s;
    animation-fill-mode: both;
}
</style>
