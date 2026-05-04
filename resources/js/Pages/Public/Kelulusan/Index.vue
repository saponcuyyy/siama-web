<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    settings: Object,
    errors: Object
});

const form = useForm({
    nisn: '',
    tanggal_lahir: ''
});

const submit = () => {
    form.post(route('public.kelulusan.cek'));
};

const namaSekolah = props.settings?.nama_sekolah || 'SMA Negeri 2 Perbaungan';
const tahunAjaran = '2025/2026';
</script>

<template>
    <Head>
        <title>Portal Cek Kelulusan - {{ namaSekolah }}</title>
    </Head>

    <div class="kelulusan-page">
        <!-- Fireworks orbs (bg decoration) -->
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>

        <div class="login-container">
            <div class="login-card">
                
                <!-- Back Link -->
                <div class="mb-6 flex justify-between items-center">
                    <Link href="/" class="back-link group">
                        <span class="group-hover:-translate-x-1 transition-transform inline-block">←</span> Kembali ke Beranda
                    </Link>
                </div>

                <div class="text-center mb-8">
                    <div class="badge-wrapper">
                        <div class="badge-ring">
                            <div class="badge-inner">
                                <span class="badge-icon">🎓</span>
                            </div>
                        </div>
                    </div>
                    <h1 class="text-3xl sm:text-4xl font-black text-white leading-tight mt-6 mb-2">Portal Kelulusan</h1>
                    <div class="inline-block bg-white/10 px-4 py-1.5 rounded-full border border-white/20 backdrop-blur-md">
                        <p class="text-sm font-bold text-amber-400">Tahun Pelajaran {{ tahunAjaran }}</p>
                    </div>
                    <p class="text-slate-300 mt-4 font-medium">{{ namaSekolah }}</p>
                </div>

                <!-- Error Messages -->
                <div v-if="errors.nisn" class="bg-red-500/20 border border-red-500/50 text-red-200 px-4 py-3 rounded-xl mb-6 text-sm font-medium backdrop-blur-sm animate-pulse-fast">
                    ⚠️ {{ errors.nisn }}
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <div class="space-y-2 text-left">
                        <label class="block text-sm font-bold text-slate-300 ml-1">NISN Siswa</label>
                        <input 
                            v-model="form.nisn" 
                            type="text" 
                            placeholder="Masukkan 10 digit NISN"
                            class="w-full bg-slate-900/50 border-slate-700 text-white rounded-xl focus:ring-amber-500 focus:border-amber-500 py-3.5 px-4 placeholder:text-slate-500 transition-all backdrop-blur-sm"
                            required
                        >
                    </div>

                    <div class="space-y-2 text-left">
                        <label class="block text-sm font-bold text-slate-300 ml-1">Tanggal Lahir</label>
                        <input 
                            v-model="form.tanggal_lahir" 
                            type="date" 
                            class="w-full bg-slate-900/50 border-slate-700 text-white rounded-xl focus:ring-amber-500 focus:border-amber-500 py-3.5 px-4 placeholder:text-slate-500 transition-all backdrop-blur-sm date-input"
                            required
                        >
                    </div>

                    <button 
                        type="submit" 
                        :disabled="form.processing"
                        class="w-full mt-4 btn-submit group relative overflow-hidden"
                    >
                        <span class="relative z-10 flex items-center justify-center gap-2">
                            {{ form.processing ? 'Memeriksa Data...' : 'Cek Status Kelulusan' }}
                            <span v-if="!form.processing" class="group-hover:translate-x-1 transition-transform">→</span>
                        </span>
                        <div class="absolute inset-0 h-full w-full bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300 ease-in-out"></div>
                    </button>
                </form>

                <div class="mt-8 text-center bg-slate-900/40 rounded-xl p-5 border border-slate-700/50 backdrop-blur-sm">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 flex items-center justify-center gap-2">
                        <span class="w-4 h-[1px] bg-slate-600"></span>
                        Pusat Bantuan
                        <span class="w-4 h-[1px] bg-slate-600"></span>
                    </h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Jika Anda mengalami kendala saat login atau data tidak ditemukan, silakan hubungi Tata Usaha sekolah.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@reference "../../../../css/app.css";

/* ===== BASE ===== */
.kelulusan-page {
    min-height: 100vh;
    background: linear-gradient(135deg, #0f0c29 0%, #302b63 40%, #24243e 100%);
    position: relative;
    overflow: hidden;
    font-family: 'Inter', 'Segoe UI', sans-serif;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
}

/* ===== ORBS ===== */
.orb {
    position: fixed;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.3;
    pointer-events: none;
    z-index: 0;
}
.orb-1 {
    width: 500px; height: 500px;
    background: radial-gradient(circle, #FFD700, transparent);
    top: -150px; left: -100px;
    animation: float1 8s ease-in-out infinite;
}
.orb-2 {
    width: 400px; height: 400px;
    background: radial-gradient(circle, #FF6B9D, transparent);
    bottom: -100px; right: -100px;
    animation: float2 10s ease-in-out infinite;
}
.orb-3 {
    width: 300px; height: 300px;
    background: radial-gradient(circle, #4ECDC4, transparent);
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    animation: float3 12s ease-in-out infinite;
}

@keyframes float1 { 0%,100% { transform: translate(0,0) scale(1); } 50% { transform: translate(30px, 20px) scale(1.1); } }
@keyframes float2 { 0%,100% { transform: translate(0,0); } 50% { transform: translate(-20px, -30px); } }
@keyframes float3 { 0%,100% { transform: translate(-50%,-50%) scale(1); } 50% { transform: translate(-50%,-50%) scale(1.2); } }

/* ===== CONTAINER & CARD ===== */
.login-container {
    position: relative;
    z-index: 10;
    width: 100%;
    max-width: 440px;
    animation: fadeUp 0.8s cubic-bezier(0.22, 1, 0.36, 1) forwards;
}

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

.login-card {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 24px;
    padding: 2.5rem 2rem;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(255,255,255,0.1);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
}

/* ===== BADGE ===== */
.badge-wrapper {
    display: flex;
    justify-content: center;
}
.badge-ring {
    width: 90px; height: 90px;
    border-radius: 50%;
    background: linear-gradient(135deg, #FFD700, #FF8C00, #FFD700);
    padding: 3px;
    animation: spinGlow 4s linear infinite;
    box-shadow: 0 0 30px rgba(255,215,0,0.3);
}
.badge-inner {
    width: 100%; height: 100%;
    border-radius: 50%;
    background: linear-gradient(135deg, #1a1a2e, #16213e);
    display: flex; align-items: center; justify-content: center;
}
.badge-icon { font-size: 2.5rem; }

@keyframes spinGlow {
    0% { box-shadow: 0 0 20px rgba(255,215,0,0.2), 0 0 40px rgba(255,165,0,0.1); }
    50% { box-shadow: 0 0 30px rgba(255,215,0,0.5), 0 0 60px rgba(255,165,0,0.3); }
    100% { box-shadow: 0 0 20px rgba(255,215,0,0.2), 0 0 40px rgba(255,165,0,0.1); }
}

/* ===== LINKS & BUTTONS ===== */
.back-link {
    color: rgba(255,255,255,0.6);
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.2s;
}
.back-link:hover {
    color: #FFD700;
}

.btn-submit {
    background: linear-gradient(135deg, #FFD700, #FF8C00);
    color: #1a1a2e;
    padding: 0.9rem 1.5rem;
    border-radius: 12px;
    font-weight: 900;
    font-size: 1rem;
    box-shadow: 0 8px 25px rgba(255, 140, 0, 0.4);
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
}
.btn-submit:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 12px 30px rgba(255, 140, 0, 0.6);
}
.btn-submit:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    background: #64748b;
    box-shadow: none;
    color: white;
}

/* Customize Date Input Icon Color */
.date-input::-webkit-calendar-picker-indicator {
    filter: invert(1);
    opacity: 0.6;
    cursor: pointer;
}
.date-input::-webkit-calendar-picker-indicator:hover {
    opacity: 1;
}

@media (max-width: 480px) {
    .login-card { padding: 2rem 1.5rem; }
    .badge-ring { width: 75px; height: 75px; }
    .badge-icon { font-size: 2rem; }
}
</style>
