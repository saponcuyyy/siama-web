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

    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-slate-50">
        <div class="w-full sm:max-w-md mt-6 px-8 py-10 bg-white shadow-2xl shadow-slate-200/50 sm:rounded-3xl border border-slate-100 overflow-hidden">
            <div class="flex flex-col items-center mb-10">
                <div class="w-16 h-16 bg-indigo-600 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-200 mb-4">
                    <span class="text-white font-bold text-3xl">S</span>
                </div>
                <h1 class="text-2xl font-bold text-slate-900">Selamat Datang Kembali</h1>
                <p class="text-slate-500 text-sm mt-2">Masuk untuk mengakses panel akademik Anda</p>
            </div>

            <form @submit.prevent="submit">
                <div>
                    <label class="block font-medium text-sm text-slate-700 mb-1.5" for="email">Alamat Email</label>
                    <input
                        id="email"
                        type="email"
                        v-model="form.email"
                        class="w-full border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm transition-all py-2.5"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="nama@siama.sch.id"
                    />
                    <div v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</div>
                </div>

                <div class="mt-5">
                    <div class="flex justify-between items-center mb-1.5">
                        <label class="block font-medium text-sm text-slate-700" for="password">Kata Sandi</label>
                        <Link href="#" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500 transition-colors">
                            Lupa kata sandi?
                        </Link>
                    </div>
                    <div class="relative">
                        <input
                            id="password"
                            :type="showPassword ? 'text' : 'password'"
                            v-model="form.password"
                            class="w-full border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm transition-all py-2.5 pr-10"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                        />
                        <button 
                            type="button" 
                            @click="showPassword = !showPassword"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors"
                        >
                            <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88 2 2m7.88 7.88L2 2m7.88 7.88c.45.45.62.91.62 1.62 0 1.38-1.12 2.5-2.5 2.5-.71 0-1.17-.17-1.62-.62m-1.38-1.38L2 2m1.12 1.12C4.12 4.12 6.12 5 10 5c6.12 0 10 7 10 7a18.25 18.25 0 0 1-2.12 3.12m-3.88 3.88C12.12 19.88 11.12 20 10 20c-6.12 0-10-7-10-7a18.25 18.25 0 0 1 2.12-3.12M22 22 2 2"/></svg>
                        </button>
                    </div>
                    <div v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</div>
                </div>

                <div class="block mt-5">
                    <label class="flex items-center group cursor-pointer">
                        <input type="checkbox" v-model="form.remember" class="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500 transition-all cursor-pointer" />
                        <span class="ms-2 text-sm text-slate-600 group-hover:text-slate-900 transition-colors">Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <div class="mt-8">
                    <button
                        class="w-full bg-indigo-600 text-white rounded-xl py-3 px-4 font-bold text-sm shadow-lg shadow-indigo-100 hover:bg-indigo-500 hover:shadow-indigo-200 active:scale-[0.98] transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing">Memproses...</span>
                        <span v-else>Masuk ke Aplikasi</span>
                    </button>
                </div>
            </form>

            <div class="mt-8 pt-8 border-t border-slate-100 text-center">
                <p class="text-xs text-slate-400">
                    Masalah login? Hubungi administrator sekolah Anda atau <a href="#" class="text-indigo-600 font-semibold hover:underline">Pusat Bantuan</a>.
                </p>
            </div>
        </div>
        
        <div class="mt-8 text-slate-400 text-xs">
            &copy; 2024 SIAMA - Versi 1.0.0
        </div>
    </div>
</template>
