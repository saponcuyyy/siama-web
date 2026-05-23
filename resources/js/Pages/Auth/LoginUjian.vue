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
    form.post(route('login', { context: 'ujian' }), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Login Ujian" />

    <div class="h-screen bg-gradient-to-br from-indigo-950 via-slate-900 to-purple-950 flex items-center justify-center p-3 relative overflow-hidden">
        <div class="absolute inset-0">
            <div class="absolute -top-20 -left-20 w-48 h-48 md:w-96 md:h-96 bg-indigo-500/10 rounded-full blur-[128px]"></div>
            <div class="absolute -bottom-20 -right-20 w-48 h-48 md:w-96 md:h-96 bg-purple-500/10 rounded-full blur-[128px]"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[300px] h-[300px] md:w-[600px] md:h-[600px] bg-sky-500/5 rounded-full blur-[160px]"></div>
        </div>

        <div class="w-full max-w-md relative">
            <div class="text-center mb-1 md:mb-3">
                <div class="inline-flex items-center gap-2 bg-white/5 backdrop-blur-sm border border-white/10 rounded-full px-3 py-1 md:px-4 md:py-1.5 mb-1 md:mb-2">
                    <span class="w-1.5 h-1.5 md:w-2 md:h-2 bg-emerald-400 rounded-full animate-pulse"></span>
                    <span class="text-[10px] md:text-xs font-medium text-emerald-300/80">Sistem Ujian Online</span>
                </div>

                <div class="w-16 h-16 md:w-20 md:h-20 mx-auto mb-1 md:mb-2 relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-indigo-400 to-purple-500 rounded-xl md:rounded-2xl rotate-6 opacity-60"></div>
                    <div class="relative w-full h-full bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl md:rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-500/25">
                        <svg class="w-8 h-8 md:w-10 md:h-10 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.717 50.717 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                        </svg>
                    </div>
                </div>

                <h1 class="text-2xl md:text-4xl font-bold text-white tracking-tight">CBT<span class="text-indigo-400">Online</span></h1>
                <p class="text-slate-400 text-sm md:text-base mt-0.5">Computer Based Test — Masuk ke ruang ujian</p>
            </div>

            <div class="bg-white rounded-xl md:rounded-2xl shadow-2xl shadow-black/30 p-3 md:p-5 relative overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 via-purple-500 to-indigo-500"></div>

                <form @submit.prevent="submit" class="space-y-2 md:space-y-3">
                    <div>
                        <label class="block text-[10px] md:text-[11px] font-semibold text-slate-400 uppercase tracking-[0.08em] mb-1" for="nisn">NISN</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3 flex md:pl-3.5 items-center pointer-events-none">
                                <svg class="w-3.5 h-3.5 md:w-4 md:h-4 text-slate-300 group-focus-within:text-indigo-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                            </div>
                            <input
                                id="nisn"
                                type="text"
                                v-model="form.email"
                                class="w-full border border-slate-200 rounded-lg md:rounded-xl pl-9 md:pl-10 pr-3 md:pr-4 py-1.5 md:py-2 text-xs md:text-sm text-slate-900 placeholder-slate-400 bg-white focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 transition-all"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="Masukkan NISN"
                            />
                        </div>
                        <p v-if="form.errors.email" class="text-red-500 text-[10px] md:text-xs mt-0.5">{{ form.errors.email }}</p>
                    </div>

                    <div>
                        <label class="block text-[10px] md:text-[11px] font-semibold text-slate-400 uppercase tracking-[0.08em] mb-1" for="password">Tanggal Lahir</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3 flex md:pl-3.5 items-center pointer-events-none">
                                <svg class="w-3.5 h-3.5 md:w-4 md:h-4 text-slate-300 group-focus-within:text-indigo-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                </svg>
                            </div>
                            <input
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                v-model="form.password"
                                class="w-full border border-slate-200 rounded-lg md:rounded-xl pl-9 md:pl-10 pr-10 md:pr-11 py-1.5 md:py-2 text-xs md:text-sm text-slate-900 placeholder-slate-400 bg-white focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 transition-all"
                                required
                                autocomplete="current-password"
                                placeholder="DDMMYYYY*"
                            />
                            <button type="button" @click="showPassword = !showPassword"
                                class="absolute right-2 md:right-2.5 top-1/2 -translate-y-1/2 text-slate-300 hover:text-slate-500 transition-colors p-1">
                                <svg v-if="!showPassword" class="w-3.5 h-3.5 md:w-4 md:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                                <svg v-else class="w-3.5 h-3.5 md:w-4 md:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="text-red-500 text-[10px] md:text-xs mt-0.5">{{ form.errors.password }}</p>
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="relative flex items-start cursor-pointer group">
                            <div class="flex items-center h-5">
                                <input type="checkbox" v-model="form.remember"
                                    class="w-3.5 h-3.5 md:w-4 md:h-4 text-indigo-600 border-slate-300 rounded-md focus:ring-indigo-500 focus:ring-offset-0 cursor-pointer transition-colors" />
                            </div>
                            <span class="ml-1.5 md:ml-2 text-[10px] md:text-xs font-medium text-slate-500 group-hover:text-slate-700 transition-colors">Ingat Saya</span>
                        </label>
                    </div>

                    <button type="submit" :disabled="form.processing"
                        class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg md:rounded-xl py-1.5 md:py-2 text-xs md:text-sm font-semibold shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/30 hover:from-indigo-500 hover:to-purple-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                        <svg v-if="!form.processing" class="w-3.5 h-3.5 md:w-4 md:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                        </svg>
                        <span v-if="form.processing" class="w-3.5 h-3.5 md:w-4 md:h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                        {{ form.processing ? 'Memproses...' : 'Masuk ke Ruang Ujian' }}
                    </button>
                </form>

                <div class="mt-2 md:mt-4 pt-2 md:pt-3 border-t border-slate-100">
                    <div class="flex items-center justify-center gap-3 md:gap-5 text-[10px] md:text-xs text-slate-400">
                        <span class="flex items-center gap-1 md:gap-1.5">
                            <svg class="w-3 h-3 md:w-3.5 md:h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            Terjadwal
                        </span>
                        <span class="flex items-center gap-1 md:gap-1.5">
                            <svg class="w-3 h-3 md:w-3.5 md:h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            Real-time
                        </span>
                        <span class="flex items-center gap-1 md:gap-1.5">
                            <svg class="w-3 h-3 md:w-3.5 md:h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                            </svg>
                            Aman
                        </span>
                    </div>
                </div>

                <p class="text-center text-[10px] md:text-xs text-slate-300 mt-2 md:mt-3">
                    &copy; {{ new Date().getFullYear() }} SMA Negeri 2 Perbaungan
                </p>
            </div>
        </div>
    </div>
</template>
