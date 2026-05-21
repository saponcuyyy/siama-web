<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { Shield, Key, AlertTriangle, LogIn } from 'lucide-vue-next';
import { ref, onMounted } from 'vue';

const props = defineProps({
    sesi: Object,
    peserta: Object,
});

const token = ref('');

const form = useForm({
    token: '',
    device_token: '',
    browser_info: navigator.userAgent,
});

// Generate or retrieve persistent device token
onMounted(() => {
    let devToken = localStorage.getItem('cbt_device_token');
    if (!devToken) {
        devToken = Array.from(crypto.getRandomValues(new Uint8Array(32)))
            .map(b => b.toString(16).padStart(2, '0')).join('');
        localStorage.setItem('cbt_device_token', devToken);
    }
    form.device_token = devToken;
});

const submitToken = () => {
    form.post(route('ujian.mulai', props.sesi.id));
};
</script>

<template>
    <Head title="Masuk Ruang Ujian" />
    
    <div class="min-h-screen bg-slate-50 flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <!-- App Logo/Header -->
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-xl shadow-indigo-200">
                    <Shield class="w-8 h-8 text-white" />
                </div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Portal Ujian CBT</h1>
                <p class="text-slate-500 font-medium mt-1">{{ sesi.nama_sesi }}</p>
            </div>

            <!-- Main Card -->
            <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
                <div class="p-8">
                    <div class="mb-8">
                        <h2 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-2">Informasi Ujian</h2>
                        <div class="space-y-2">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-slate-500 font-medium">Mata Pelajaran</span>
                                <span class="font-bold text-slate-900">{{ sesi.paket_ujian.mata_pelajaran?.nama || 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-slate-500 font-medium">Durasi</span>
                                <span class="font-bold text-slate-900">{{ sesi.paket_ujian.durasi_menit }} Menit</span>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-slate-500 font-medium">Sifat Ujian</span>
                                <span class="inline-flex items-center gap-1 text-rose-600 font-bold bg-rose-50 px-2 py-0.5 rounded-md text-xs">
                                    <AlertTriangle class="w-3 h-3" /> Wajib Fullscreen
                                </span>
                            </div>
                        </div>
                    </div>

                    <form @submit.prevent="submitToken">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Token Ujian</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <Key class="w-5 h-5 text-slate-400" />
                                    </div>
                                    <input 
                                        type="text" 
                                        v-model="form.token" 
                                        required
                                        placeholder="Masukkan 8 digit token"
                                        class="block w-full pl-11 pr-4 py-3 bg-slate-50 border-slate-200 rounded-xl text-slate-900 font-bold uppercase tracking-widest focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 transition-all"
                                        :class="{'border-rose-500 focus:ring-rose-500 focus:border-rose-500': form.errors.token}"
                                    >
                                </div>
                                <p v-if="form.errors.token" class="mt-2 text-sm font-bold text-rose-500">
                                    {{ form.errors.token }}
                                </p>
                            </div>

                            <button 
                                type="submit" 
                                :disabled="form.processing"
                                class="w-full flex items-center justify-center gap-2 px-4 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition-colors disabled:opacity-50"
                            >
                                <LogIn class="w-5 h-5" />
                                {{ form.processing ? 'Memproses...' : 'Mulai Kerjakan' }}
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Footer Warning -->
                <div class="bg-slate-50 p-4 border-t border-slate-100 text-center">
                    <p class="text-xs font-semibold text-slate-500 leading-relaxed">
                        Dengan menekan tombol mulai, Anda menyetujui peraturan ujian. Pelanggaran (seperti pindah tab) akan dicatat dan dapat membatalkan ujian Anda.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
