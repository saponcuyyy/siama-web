<script setup>
import { Head, useForm } from '@inertiajs/vue3';
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

    <div class="h-[100dvh] w-screen flex flex-col items-center justify-center bg-slate-950 relative overflow-hidden select-none px-4 sm:px-6">
        <!-- Abstract Background Effects -->
        <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none">
            <div class="absolute top-[-10%] left-[20%] w-[50vw] h-[50vw] max-w-[600px] max-h-[600px] bg-indigo-600/20 blur-[100px] rounded-full"></div>
            <div class="absolute bottom-[-10%] right-[10%] w-[60vw] h-[60vw] max-w-[700px] max-h-[700px] bg-purple-800/20 blur-[120px] rounded-full"></div>
            <!-- Subtle Grid -->
            <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCI+PHBhdGggZD0iTTAgMGg0MHY0MEgweiIgZmlsbD0ibm9uZSIvPjxwYXRoIGQ9Ik0wIDAuNWg0ME0wIDQwLjVoNDBWMHptMzkuNSAwaDFWMHoiIGZpbGw9Im5vbmUiIHN0cm9rZT0icmdiYSgyNTUsMjU1LDI1NSwwLjAyKSIvPjwvc3ZnPg==')]"></div>
        </div>

        <!-- Main Container -->
        <div class="w-full max-w-[340px] relative z-10 flex flex-col">
            
            <!-- Branding Header -->
            <div class="flex flex-col items-center mb-6">
                <div class="w-16 h-16 bg-white/5 rounded-2xl p-3 backdrop-blur-md border border-white/10 shadow-2xl mb-4">
                    <img src="/images/logo.png" alt="Logo SMAN 2 Perbaungan" class="w-full h-full object-contain drop-shadow-lg" />
                </div>
                <h1 class="text-2xl font-black text-white tracking-tight flex items-center gap-2">
                    CBT <span class="text-indigo-400">Siama</span>
                </h1>
                <div class="inline-flex items-center gap-1.5 mt-2 bg-indigo-500/10 border border-indigo-500/20 rounded-full px-2.5 py-0.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-pulse"></span>
                    <span class="text-[10px] font-bold text-indigo-300 uppercase tracking-widest">Ruang Ujian</span>
                </div>
            </div>

            <!-- Form Card -->
            <form @submit.prevent="submit" class="bg-slate-900/60 backdrop-blur-xl border border-white/10 rounded-[1.5rem] p-5 sm:p-6 shadow-2xl shadow-black/50">
                <div class="space-y-4">
                    <!-- NISN Input -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1" for="email">NISN</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 group-focus-within:text-indigo-400 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                            </div>
                            <input
                                id="email"
                                type="text"
                                v-model="form.email"
                                class="w-full bg-black/20 border border-white/10 rounded-xl pl-10 pr-4 py-3 text-sm text-white placeholder:text-slate-600 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all font-medium"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="Nomor Induk Siswa"
                            />
                        </div>
                        <p v-if="form.errors.email" class="text-rose-400 text-[10px] font-bold mt-1.5 ml-1">{{ form.errors.email }}</p>
                    </div>

                    <!-- Password/DOB Input -->
                    <div>
                        <div class="flex justify-between items-center mb-1.5 ml-1 mr-1">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest" for="password">Tanggal Lahir</label>
                            <span class="text-[9px] text-slate-500 font-bold tracking-widest">DDMMYYYY</span>
                        </div>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 group-focus-within:text-indigo-400 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                </svg>
                            </div>
                            <input
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                v-model="form.password"
                                class="w-full bg-black/20 border border-white/10 rounded-xl pl-10 pr-10 py-3 text-sm text-white placeholder:text-slate-600 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all tracking-widest font-medium"
                                required
                                autocomplete="current-password"
                                placeholder="12012010"
                                maxlength="8"
                            />
                            <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-white transition-colors">
                                <svg v-if="!showPassword" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                                <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="text-rose-400 text-[10px] font-bold mt-1.5 ml-1">{{ form.errors.password }}</p>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center pt-1.5 pb-1">
                        <label class="flex items-center gap-2 cursor-pointer group w-fit">
                            <div class="relative flex items-center justify-center w-4 h-4">
                                <input type="checkbox" v-model="form.remember" class="peer absolute inset-0 opacity-0 cursor-pointer" />
                                <div class="w-4 h-4 border border-slate-600 rounded bg-black/20 peer-checked:bg-indigo-500 peer-checked:border-indigo-500 transition-colors group-hover:border-indigo-400"></div>
                                <svg class="absolute w-2.5 h-2.5 text-white scale-0 peer-checked:scale-100 transition-transform pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </div>
                            <span class="text-[11px] font-bold text-slate-400 group-hover:text-slate-200 transition-colors uppercase tracking-widest">Ingat Saya</span>
                        </label>
                    </div>

                    <!-- Action Button -->
                    <button type="submit" :disabled="form.processing"
                        class="w-full bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white rounded-xl py-3.5 text-xs font-black uppercase tracking-[0.15em] shadow-lg shadow-indigo-600/30 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 mt-2 border border-indigo-500/50">
                        <svg v-if="!form.processing" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                        </svg>
                        <span v-if="form.processing" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                        {{ form.processing ? 'Memproses...' : 'Masuk Ujian' }}
                    </button>
                </div>
            </form>

            <p class="text-center text-[10px] text-slate-500 mt-6 font-bold uppercase tracking-widest">
                &copy; {{ new Date().getFullYear() }} SMAN 2 Perbaungan
            </p>
        </div>
    </div>
</template>
