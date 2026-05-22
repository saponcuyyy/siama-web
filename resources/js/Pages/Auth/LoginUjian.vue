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
    <Head title="Login - Portal Ujian CBT" />

    <div class="exam-page">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>

        <div class="grid-pattern"></div>

        <div class="exam-container">
            <div class="exam-card">

                <!-- Header -->
                <div class="text-center mb-8 md:mb-10">
                    <div class="badge-wrapper">
                        <div class="badge-ring">
                            <div class="badge-inner">
                                <svg class="badge-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                    <path d="m9 12 2 2 4-4" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black text-white leading-tight mt-6 mb-1">
                        Portal Ujian CBT
                    </h1>
                    <p class="text-sm font-medium text-slate-400">
                        Masuk untuk mengakses ujian online
                    </p>
                </div>

                <!-- Login Form -->
                <form @submit.prevent="submit" class="space-y-5 md:space-y-6">
                    <div class="space-y-2">
                        <label class="input-label" for="email">Email / NISN</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 md:w-5 md:h-5 text-slate-500 group-focus-within:text-cyan-400 transition-colors"
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M22 12.75V12a10 10 0 1 0-4.5 8.43" />
                                    <circle cx="12" cy="12" r="4" />
                                    <path d="M22 13a2 2 0 0 0-4 0v4.25" />
                                </svg>
                            </div>
                            <input
                                id="email"
                                type="text"
                                v-model="form.email"
                                class="form-input"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="Masukkan Email atau NISN"
                            />
                        </div>
                        <p v-if="form.errors.email" class="error-text">{{ form.errors.email }}</p>
                    </div>

                    <div class="space-y-2">
                        <div class="flex justify-between items-center">
                            <label class="input-label" for="password">Kata Sandi</label>
                            <Link href="#" class="link-forgot">
                                Lupa sandi?
                            </Link>
                        </div>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 md:w-5 md:h-5 text-slate-500 group-focus-within:text-cyan-400 transition-colors"
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                </svg>
                            </div>
                            <input
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                v-model="form.password"
                                class="form-input"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-cyan-400 transition-colors p-1"
                            >
                                <svg v-if="!showPassword" class="w-4 h-4 md:w-5 md:h-5"
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                                <svg v-else class="w-4 h-4 md:w-5 md:h-5"
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M9.88 9.88 2 2m7.88 7.88L2 2m7.88 7.88c.45.45.62.91.62 1.62 0 1.38-1.12 2.5-2.5 2.5-.71 0-1.17-.17-1.62-.62m-1.38-1.38L2 2m1.12 1.12C4.12 4.12 6.12 5 10 5c6.12 0 10 7 10 7a18.25 18.25 0 0 1-2.12 3.12m-3.88 3.88C12.12 19.88 11.12 20 10 20c-6.12 0-10-7-10-7a18.25 18.25 0 0 1 2.12-3.12M22 22 2 2" />
                                </svg>
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="error-text">{{ form.errors.password }}</p>
                    </div>

                    <!-- Remember -->
                    <div class="flex items-center">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="relative flex items-center justify-center w-5 h-5">
                                <input type="checkbox" v-model="form.remember"
                                    class="peer absolute inset-0 opacity-0 cursor-pointer" />
                                <div
                                    class="w-5 h-5 border-2 border-slate-600/60 rounded-lg bg-white/[0.04] peer-checked:bg-cyan-500 peer-checked:border-cyan-500 transition-all group-hover:border-cyan-500/40">
                                </div>
                                <svg class="absolute w-3 h-3 text-white scale-0 peer-checked:scale-100 transition-transform pointer-events-none"
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="4" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-slate-400 group-hover:text-slate-300 transition-colors uppercase tracking-widest">
                                Ingat Saya
                            </span>
                        </label>
                    </div>

                    <button type="submit" :disabled="form.processing" class="btn-submit">
                        <span class="btn-submit-bg" />
                        <span class="btn-submit-content">
                            <template v-if="form.processing">
                                <span class="spinner" />
                                <span>Memproses...</span>
                            </template>
                            <template v-else>
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
                                    <polyline points="10 17 15 12 10 7" />
                                    <line x1="15" y1="12" x2="3" y2="12" />
                                </svg>
                                <span>Masuk ke Ujian</span>
                            </template>
                        </span>
                    </button>
                </form>

                <!-- Help -->
                <div class="help-box">
                    <p class="help-title">Akses Ujian CBT</p>
                    <p class="help-text">
                        Gunakan akun SIAMA (Email/NISN dan Password) yang telah terdaftar. Jika mengalami kendala, hubungi pengawas atau Tim IT sekolah.
                    </p>
                </div>

                <!-- Back -->
                <div class="text-center mt-6">
                    <Link href="/" class="text-xs font-bold text-slate-500 hover:text-cyan-400 transition-colors">
                        &larr; Kembali ke Beranda
                    </Link>
                </div>
            </div>

            <p class="footer-text">
                &copy; {{ new Date().getFullYear() }} SMA Negeri 2 Perbaungan
            </p>
        </div>
    </div>
</template>

<style scoped>
/* ===== BASE ===== */
.exam-page {
    min-height: 100vh;
    background: linear-gradient(135deg, #050a18 0%, #0a1628 30%, #0f1f3d 60%, #0a1628 100%);
    position: relative;
    overflow: hidden;
    font-family: 'Inter', 'Segoe UI', sans-serif;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
}

/* ===== GRID ===== */
.grid-pattern {
    position: fixed;
    inset: 0;
    background-image:
        linear-gradient(rgba(255, 255, 255, 0.02) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, 0.02) 1px, transparent 1px);
    background-size: 48px 48px;
    pointer-events: none;
    z-index: 0;
    mask-image: radial-gradient(ellipse 60% 50% at center, black 30%, transparent 70%);
    -webkit-mask-image: radial-gradient(ellipse 60% 50% at center, black 30%, transparent 70%);
}

/* ===== ORBS ===== */
.orb {
    position: fixed;
    border-radius: 50%;
    filter: blur(100px);
    pointer-events: none;
    z-index: 0;
}
.orb-1 {
    width: 600px; height: 600px;
    background: radial-gradient(circle, rgba(6, 182, 212, 0.15), transparent);
    top: -200px; right: -100px;
    animation: float1 12s ease-in-out infinite;
}
.orb-2 {
    width: 500px; height: 500px;
    background: radial-gradient(circle, rgba(59, 130, 246, 0.12), transparent);
    bottom: -150px; left: -150px;
    animation: float2 10s ease-in-out infinite;
}
.orb-3 {
    width: 400px; height: 400px;
    background: radial-gradient(circle, rgba(245, 158, 11, 0.06), transparent);
    top: 40%; left: 60%;
    animation: float3 15s ease-in-out infinite;
}

@keyframes float1 {
    0%, 100% { transform: translate(0, 0) scale(1); }
    33% { transform: translate(-40px, 30px) scale(1.05); }
    66% { transform: translate(20px, -20px) scale(0.95); }
}
@keyframes float2 {
    0%, 100% { transform: translate(0, 0); }
    50% { transform: translate(30px, -40px); }
}
@keyframes float3 {
    0%, 100% { transform: translate(0, 0) scale(1); }
    50% { transform: translate(-20px, 20px) scale(1.1); }
}

/* ===== CONTAINER & CARD ===== */
.exam-container {
    position: relative;
    z-index: 10;
    width: 100%;
    max-width: 440px;
    animation: fadeUp 0.7s cubic-bezier(0.22, 1, 0.36, 1) forwards;
}

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(24px); }
    to { opacity: 1; transform: translateY(0); }
}

.exam-card {
    background: rgba(15, 23, 42, 0.75);
    border: 1px solid rgba(255, 255, 255, 0.06);
    border-radius: 28px;
    padding: 2.25rem 1.75rem;
    box-shadow:
        0 25px 60px -12px rgba(0, 0, 0, 0.6),
        inset 0 1px 0 rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
}

/* ===== BADGE ===== */
.badge-wrapper {
    display: flex;
    justify-content: center;
}
.badge-ring {
    width: 80px; height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(6, 182, 212, 0.5), rgba(6, 182, 212, 0.1), rgba(6, 182, 212, 0.4));
    padding: 2px;
    animation: badgePulse 3s ease-in-out infinite;
    box-shadow: 0 0 30px rgba(6, 182, 212, 0.15);
}
.badge-inner {
    width: 100%; height: 100%;
    border-radius: 50%;
    background: linear-gradient(135deg, #0f172a, #1e293b);
    display: flex; align-items: center; justify-content: center;
}
.badge-icon {
    width: 36px; height: 36px;
    color: #22d3ee;
}

@keyframes badgePulse {
    0%, 100% { box-shadow: 0 0 20px rgba(6, 182, 212, 0.1), 0 0 40px rgba(6, 182, 212, 0.05); }
    50% { box-shadow: 0 0 30px rgba(6, 182, 212, 0.25), 0 0 60px rgba(6, 182, 212, 0.1); }
}

/* ===== FORM ===== */
.input-label {
    font-size: 0.6875rem;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.15em;
}

.form-input {
    display: block;
    width: 100%;
    padding: 0.875rem 1rem 0.875rem 2.75rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: #f1f5f9;
    background: rgba(255, 255, 255, 0.04);
    border: 1.5px solid rgba(255, 255, 255, 0.08);
    border-radius: 12px;
    outline: none;
    transition: all 0.2s ease;
    caret-color: #22d3ee;
}
.form-input::placeholder {
    color: #475569;
}
.form-input:focus {
    border-color: rgba(6, 182, 212, 0.5);
    background: rgba(6, 182, 212, 0.06);
    box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.15);
}

.error-text {
    margin-top: 0.375rem;
    font-size: 0.6875rem;
    font-weight: 700;
    color: #fb7185;
}

.link-forgot {
    font-size: 0.625rem;
    font-weight: 800;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    transition: color 0.2s;
}
.link-forgot:hover {
    color: #22d3ee;
}

/* ===== SUBMIT BUTTON ===== */
.btn-submit {
    position: relative;
    width: 100%;
    border: none;
    border-radius: 14px;
    padding: 0;
    cursor: pointer;
    overflow: hidden;
    transition: all 0.3s ease;
    background: linear-gradient(135deg, #0891b2, #06b6d4);
    box-shadow: 0 8px 24px rgba(6, 182, 212, 0.25);
}
.btn-submit:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 12px 32px rgba(6, 182, 212, 0.35);
}
.btn-submit:active:not(:disabled) {
    transform: translateY(0);
}
.btn-submit:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    background: #334155;
    box-shadow: none;
}
.btn-submit:focus-visible {
    outline: 2px solid #22d3ee;
    outline-offset: 2px;
}

.btn-submit-bg {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0), rgba(255,255,255,0.08));
    transition: opacity 0.3s;
}
.btn-submit:hover:not(:disabled) .btn-submit-bg {
    opacity: 0;
}

.btn-submit-content {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.625rem;
    padding: 0.9rem 1.5rem;
    font-size: 0.9375rem;
    font-weight: 800;
    color: white;
    letter-spacing: 0.02em;
}

.spinner {
    width: 1.125rem;
    height: 1.125rem;
    border: 2.5px solid rgba(255, 255, 255, 0.25);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* ===== HELP ===== */
.help-box {
    margin-top: 1.5rem;
    text-align: center;
    padding: 1rem 1.25rem;
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(255, 255, 255, 0.04);
    border-radius: 14px;
}

.help-title {
    font-size: 0.5625rem;
    font-weight: 800;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.15em;
    margin-bottom: 0.375rem;
}

.help-text {
    font-size: 0.6875rem;
    color: #64748b;
    font-weight: 500;
    line-height: 1.6;
}

/* ===== FOOTER ===== */
.footer-text {
    text-align: center;
    margin-top: 1.75rem;
    font-size: 0.5625rem;
    font-weight: 700;
    color: #334155;
    text-transform: uppercase;
    letter-spacing: 0.2em;
}

/* ===== RESPONSIVE ===== */
@media (min-width: 640px) {
    .exam-card {
        padding: 2.5rem 2.25rem;
        border-radius: 32px;
    }
    .badge-ring {
        width: 88px; height: 88px;
    }
    .badge-icon {
        width: 40px; height: 40px;
    }
}

@media (min-width: 768px) {
    .exam-container {
        max-width: 480px;
    }
    .exam-card {
        padding: 2.75rem 2.5rem;
    }
    .form-input {
        padding: 1rem 1rem 1rem 3rem;
    }
}

@media (max-width: 380px) {
    .exam-card {
        padding: 1.5rem 1.25rem;
        border-radius: 20px;
    }
    .badge-ring {
        width: 68px; height: 68px;
    }
    .badge-icon {
        width: 30px; height: 30px;
    }
}
</style>
