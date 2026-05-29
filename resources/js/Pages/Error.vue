<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: Number,
        default: 404,
    },
});

const config = computed(() => {
    const configs = {
        404: {
            code: '404',
            title: 'Halaman Tidak Ditemukan',
            subtitle: 'Ups! Halaman yang kamu cari tidak ada atau mungkin sudah dipindahkan.',
            emoji: '🔍',
            color: 'from-indigo-500 to-purple-600',
            accent: 'indigo',
        },
        403: {
            code: '403',
            title: 'Akses Ditolak',
            subtitle: 'Kamu tidak memiliki izin untuk mengakses halaman ini. Silakan hubungi administrator.',
            emoji: '🔒',
            color: 'from-rose-500 to-pink-600',
            accent: 'rose',
        },
        500: {
            code: '500',
            title: 'Terjadi Kesalahan Server',
            subtitle: 'Kami sedang mengalami masalah teknis. Tim kami sudah diberitahu dan sedang memperbaikinya.',
            emoji: '⚙️',
            color: 'from-amber-500 to-orange-600',
            accent: 'amber',
        },
        503: {
            code: '503',
            title: 'Server Sedang Maintenance',
            subtitle: 'Sistem sedang dalam pemeliharaan. Silakan coba lagi dalam beberapa saat.',
            emoji: '🔧',
            color: 'from-teal-500 to-cyan-600',
            accent: 'teal',
        },
    };
    return configs[props.status] || configs[404];
});
</script>

<template>
    <Head :title="`Error ${status} - ${config.title}`" />

    <div class="min-h-screen bg-slate-950 flex items-center justify-center relative overflow-hidden px-6">
        <!-- Animated Background Blobs -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div :class="`absolute top-[-10%] left-[15%] w-[60vw] h-[60vw] max-w-[700px] max-h-[700px] bg-gradient-to-br ${config.color} opacity-10 blur-[120px] rounded-full animate-blob`"></div>
            <div :class="`absolute bottom-[-10%] right-[10%] w-[50vw] h-[50vw] max-w-[600px] max-h-[600px] bg-gradient-to-br ${config.color} opacity-10 blur-[100px] rounded-full animate-blob animation-delay-2000`"></div>
            <!-- Grid overlay -->
            <div class="absolute inset-0" style="background-image: radial-gradient(rgba(255,255,255,0.03) 1px, transparent 1px); background-size: 40px 40px;"></div>
        </div>

        <div class="relative z-10 max-w-xl w-full text-center space-y-8">

            <!-- Error Code -->
            <div class="relative">
                <p :class="`text-[160px] sm:text-[220px] font-black leading-none bg-gradient-to-b ${config.color} bg-clip-text text-transparent opacity-20 select-none absolute inset-0 flex items-center justify-center`">
                    {{ config.code }}
                </p>
                <!-- Emoji floating -->
                <div class="relative pt-8 pb-4 flex items-center justify-center" style="height: 180px;">
                    <div class="text-7xl sm:text-8xl animate-float select-none">{{ config.emoji }}</div>
                </div>
            </div>

            <!-- Text Content -->
            <div class="space-y-4">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/5 border border-white/10 text-xs font-bold text-slate-400 uppercase tracking-widest">
                    <span :class="`w-1.5 h-1.5 rounded-full bg-gradient-to-r ${config.color} animate-pulse`"></span>
                    Error {{ config.code }}
                </div>
                <h1 class="text-3xl sm:text-5xl font-black text-white leading-tight">
                    {{ config.title }}
                </h1>
                <p class="text-slate-400 text-base sm:text-lg leading-relaxed max-w-md mx-auto font-medium">
                    {{ config.subtitle }}
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
                <button
                    @click="() => window.history.back()"
                    class="w-full sm:w-auto px-6 py-3 bg-white/10 hover:bg-white/15 border border-white/10 text-white text-sm font-bold rounded-xl transition-all duration-200 flex items-center justify-center gap-2 group"
                >
                    <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Kembali
                </button>
                <Link
                    href="/"
                    :class="`w-full sm:w-auto px-6 py-3 bg-gradient-to-r ${config.color} text-white text-sm font-bold rounded-xl transition-all duration-200 hover:opacity-90 active:scale-95 flex items-center justify-center gap-2 shadow-xl`"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    Beranda
                </Link>
            </div>

            <!-- Footer note -->
            <p class="text-slate-600 text-xs font-bold uppercase tracking-widest pt-4">
                SMAN 2 PERBAUNGAN &mdash; SIAMA
            </p>
        </div>
    </div>
</template>

<style scoped>
@keyframes blob {
    0%, 100% { transform: translate(0, 0) scale(1); }
    33% { transform: translate(20px, -30px) scale(1.05); }
    66% { transform: translate(-15px, 15px) scale(0.95); }
}
@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-16px); }
}
.animate-blob { animation: blob 8s infinite ease-in-out; }
.animation-delay-2000 { animation-delay: 2s; }
.animate-float { animation: float 3s infinite ease-in-out; }
</style>
