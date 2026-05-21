<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed, watch, nextTick } from 'vue';
import { Clock, AlertTriangle, ChevronLeft, ChevronRight, CheckCircle2, Maximize, AlertOctagon, X } from 'lucide-vue-next';
import axios from 'axios';
import dayjs from 'dayjs';

const props = defineProps({
    sesi: Object,
    peserta: Object,
    soal_list: Array,
    sisa_waktu: Number,
});

const currentSoalIndex = ref(0);
const sisaDetik = ref(props.sisa_waktu);
const timer = ref(null);
const isFullscreen = ref(false);
const autoSaveStatus = ref('saved'); // 'saved', 'saving', 'error'
const isNavigatorOpen = ref(false);

const showConfirmSubmitModal = ref(false);
const showViolationModal = ref(false);
const violationDetails = ref({ tipe: '', pesan: '', count: 0 });
const showDisqualifiedModal = ref(false);
const isSubmitting = ref(false);

// Local state for answers to ensure reactivity
const localAnswers = ref({});

const triggerMathJax = () => {
    nextTick(() => {
        if (window.MathJax && typeof window.MathJax.typesetPromise === 'function') {
            window.MathJax.typesetPromise();
        }
    });
};

onMounted(() => {
    // Initialize local answers state from props
    props.soal_list.forEach(soal => {
        localAnswers.value[soal.id] = soal.jawaban_siswa;
    });

    startTimer();
    document.addEventListener('visibilitychange', handleVisibilityChange);
    window.addEventListener('blur', handleBlur);
    document.addEventListener('fullscreenchange', handleFullscreenChange);
    
    // Auto request fullscreen if required
    if (props.sesi.wajib_fullscreen) {
        requestFullscreen();
    }

    triggerMathJax();
});

watch(() => currentSoalIndex.value, () => {
    triggerMathJax();
});

onUnmounted(() => {
    clearInterval(timer.value);
    document.removeEventListener('visibilitychange', handleVisibilityChange);
    window.removeEventListener('blur', handleBlur);
    document.removeEventListener('fullscreenchange', handleFullscreenChange);
});

// Timer Logic
const startTimer = () => {
    timer.value = setInterval(() => {
        if (sisaDetik.value > 0) {
            sisaDetik.value--;
        } else {
            clearInterval(timer.value);
            submitUjian('waktu_habis');
        }
    }, 1000);
};

const formattedTime = computed(() => {
    const hours = Math.floor(sisaDetik.value / 3600);
    const minutes = Math.floor((sisaDetik.value % 3600) / 60);
    const seconds = sisaDetik.value % 60;
    
    if (hours > 0) {
        return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    }
    return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
});

// Anti-Cheat Logic
const isSubmittingOrConfirming = ref(false);

const handleVisibilityChange = () => {
    if (isSubmittingOrConfirming.value) return;
    if (document.visibilityState === 'hidden') {
        reportPelanggaran('pindah_tab');
    }
};

const handleBlur = () => {
    if (isSubmittingOrConfirming.value) return;
    reportPelanggaran('blur_window');
};

const handleFullscreenChange = () => {
    isFullscreen.value = !!document.fullscreenElement;
    if (isSubmittingOrConfirming.value) return;
    if (!isFullscreen.value && props.sesi.wajib_fullscreen) {
        reportPelanggaran('keluar_fullscreen');
    }
};

const requestFullscreen = async () => {
    try {
        if (document.documentElement.requestFullscreen) {
            await document.documentElement.requestFullscreen();
        }
    } catch (err) {
        console.warn("Fullscreen request failed", err);
    }
};

const reportPelanggaran = async (tipe) => {
    if (isSubmittingOrConfirming.value) return;
    try {
        const response = await axios.post(route('ujian.pelanggaran', props.sesi.id), { tipe });
        
        isSubmittingOrConfirming.value = true;
        if (response.data.status === 'didiskualifikasi') {
            showDisqualifiedModal.value = true;
        } else {
            violationDetails.value = {
                tipe: tipe === 'blur_window' ? 'Focus Hilang / Klik Luar Jendela' : (tipe === 'pindah_tab' ? 'Pindah Tab Browser' : 'Keluar Mode Fullscreen'),
                pesan: 'Anda dideteksi meninggalkan layar ujian. Tetap fokus pada halaman ini!',
                count: response.data.jumlah_pelanggaran
            };
            showViolationModal.value = true;
        }
    } catch (error) {
        console.error("Gagal mencatat pelanggaran", error);
    }
};

const closeViolationModal = () => {
    showViolationModal.value = false;
    setTimeout(() => {
        isSubmittingOrConfirming.value = false;
        if (props.sesi.wajib_fullscreen) {
            requestFullscreen();
        }
    }, 500);
};

const goToResults = () => {
    window.location.href = route('ujian.hasil', props.sesi.id);
};

// Computed stats for confirmation modal
const totalSoal = computed(() => props.soal_list.length);
const answeredSoalCount = computed(() => {
    return props.soal_list.filter(soal => {
        const ans = localAnswers.value[soal.id];
        if (ans === undefined || ans === null) return false;
        if (soal.tipe === 'menjodohkan') {
            if (typeof ans !== 'object') return false;
            const keys = Object.keys(ans);
            if (keys.length === 0) return false;
            return keys.length === (soal.pilihan_kiri?.length || 0) && Object.values(ans).every(v => v && v !== '');
        }
        return ans !== '';
    }).length;
});
const unansweredSoalCount = computed(() => totalSoal.value - answeredSoalCount.value);

// Exam Logic
const currentSoal = computed(() => props.soal_list[currentSoalIndex.value]);

const saveJawaban = async (durasi = 0) => {
    autoSaveStatus.value = 'saving';
    
    const soalId = currentSoal.value.id;
    const jawaban = localAnswers.value[soalId];

    try {
        await axios.post(route('ujian.simpan', props.sesi.id), {
            soal_id: soalId,
            jawaban: jawaban,
            durasi: durasi
        });
        autoSaveStatus.value = 'saved';
        
        // Update the original prop to reflect saved state visually in the navigator
        const soal = props.soal_list.find(s => s.id === soalId);
        if (soal) soal.jawaban_siswa = jawaban;

    } catch (error) {
        autoSaveStatus.value = 'error';
        console.error("Gagal menyimpan jawaban", error);
    }
};

const nextSoal = () => {
    saveJawaban(10); // Example fixed duration assumption for auto-save on next
    if (currentSoalIndex.value < props.soal_list.length - 1) {
        currentSoalIndex.value++;
    }
};

const prevSoal = () => {
    saveJawaban(10);
    if (currentSoalIndex.value > 0) {
        currentSoalIndex.value--;
    }
};

const jumpToSoal = (index) => {
    saveJawaban(5);
    currentSoalIndex.value = index;
};

const submitUjian = (reason = 'submit_manual') => {
    if (reason === 'submit_manual') {
        isSubmittingOrConfirming.value = true;
        showConfirmSubmitModal.value = true;
    } else {
        isSubmittingOrConfirming.value = true;
        executeSubmitUjian();
    }
};

const closeConfirmSubmitModal = () => {
    showConfirmSubmitModal.value = false;
    setTimeout(() => {
        isSubmittingOrConfirming.value = false;
    }, 500);
};

const executeSubmitUjian = () => {
    isSubmitting.value = true;
    saveJawaban(0).finally(() => {
        router.post(route('ujian.selesai', props.sesi.id), {}, {
            onSuccess: () => {
                if (document.fullscreenElement) {
                    document.exitFullscreen();
                }
            },
            onError: () => {
                isSubmitting.value = false;
                isSubmittingOrConfirming.value = false;
                showConfirmSubmitModal.value = false;
            }
        });
    });
};

// Menjodohkan Handlers
const updateMenjodohkan = (kiri, value) => {
    const soalId = currentSoal.value.id;
    let currentJawaban = localAnswers.value[soalId] || {};
    currentJawaban[kiri] = value;
    localAnswers.value[soalId] = currentJawaban;
    saveJawaban(2);
};
</script>

<template>
    <Head title="Mengerjakan Ujian" />
    
    <div class="min-h-screen bg-slate-100 flex flex-col md:flex-row select-none">
        
        <!-- Mobile Header (hidden on desktop) -->
        <header class="md:hidden bg-slate-900 text-white h-16 px-4 flex items-center justify-between sticky top-0 z-30 shadow-md">
            <div class="min-w-0 flex-1">
                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider">Mata Pelajaran</p>
                <p class="text-xs font-black truncate pr-2">{{ sesi.paket_ujian.mata_pelajaran?.nama }}</p>
            </div>
            
            <div class="flex items-center gap-3 flex-shrink-0">
                <!-- Compact Timer -->
                <div class="flex items-center gap-1.5 bg-slate-800 px-3 py-1.5 rounded-full border border-slate-700">
                    <Clock class="w-3.5 h-3.5 text-emerald-400" :class="{'text-rose-500 animate-pulse': sisaDetik < 300}" />
                    <span class="text-sm font-black tabular-nums" :class="{'text-rose-500': sisaDetik < 300}">{{ formattedTime }}</span>
                </div>
                
                <!-- Open Navigator button -->
                <button 
                    @click="isNavigatorOpen = true"
                    class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black transition-colors"
                >
                    Soal
                </button>
            </div>
        </header>

        <!-- Sidebar: Soal Navigator (Overlay Drawer on mobile, Sidebar on desktop) -->
        <aside 
            :class="[
                'bg-white border-r border-slate-200 flex flex-col shadow-2xl transition-all duration-300 z-40',
                // Mobile layout
                'fixed inset-y-0 right-0 w-80 transform md:relative md:translate-x-0 md:w-80 md:h-screen md:sticky md:top-0',
                isNavigatorOpen ? 'translate-x-0' : 'translate-x-full md:translate-x-0'
            ]"
        >
            <div class="p-6 border-b border-slate-100 bg-slate-900 text-white flex items-center justify-between md:block flex-shrink-0">
                <div>
                    <h2 class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-1">Mata Pelajaran</h2>
                    <p class="font-black leading-tight">{{ sesi.paket_ujian.mata_pelajaran?.nama }}</p>
                </div>
                <!-- Close button for mobile -->
                <button @click="isNavigatorOpen = false" class="p-1 text-slate-400 hover:text-white md:hidden">
                    <X class="w-6 h-6" />
                </button>
            </div>

            <!-- Desktop Timer only -->
            <div class="p-6 border-b border-slate-100 bg-slate-900 text-white hidden md:block flex-shrink-0">
                <div class="flex items-center justify-center gap-3 bg-slate-800/50 rounded-2xl py-4 border border-slate-700">
                    <Clock class="w-6 h-6 text-emerald-400" :class="{'text-rose-500 animate-pulse': sisaDetik < 300}" />
                    <span class="text-3xl font-black tabular-nums tracking-tight" :class="{'text-rose-500': sisaDetik < 300}">
                        {{ formattedTime }}
                    </span>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Navigasi Soal</h3>
                    <div class="flex items-center gap-1.5 text-xs font-bold text-slate-500">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Dijawab
                    </div>
                </div>
                
                <div class="grid grid-cols-5 gap-3">
                    <button 
                        v-for="(soal, index) in soal_list" 
                        :key="soal.id"
                        @click="jumpToSoal(index); isNavigatorOpen = false"
                        class="aspect-square flex items-center justify-center rounded-xl text-sm font-black transition-all border-2"
                        :class="[
                            currentSoalIndex === index ? 'border-indigo-600 bg-indigo-50 text-indigo-700 scale-110 shadow-lg shadow-indigo-200' : 
                            (localAnswers[soal.id] && localAnswers[soal.id] !== '' && Object.keys(localAnswers[soal.id]).length !== 0) ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 
                            'border-slate-200 bg-white text-slate-500 hover:border-slate-300'
                        ]"
                    >
                        {{ index + 1 }}
                    </button>
                </div>
            </div>

            <div class="p-6 border-t border-slate-100 bg-slate-50 flex-shrink-0">
                <button 
                    @click="submitUjian('submit_manual')"
                    class="w-full flex items-center justify-center gap-2 py-4 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-2xl transition-colors shadow-xl shadow-slate-200"
                >
                    <CheckCircle2 class="w-5 h-5 text-emerald-400" />
                    Selesaikan Ujian
                </button>
            </div>
        </aside>

        <!-- Mobile Navigator Backdrop -->
        <div 
            v-if="isNavigatorOpen" 
            @click="isNavigatorOpen = false" 
            class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-30 md:hidden"
        ></div>

        <!-- Main Content: Active Soal -->
        <main class="flex-1 flex flex-col h-auto md:h-screen overflow-hidden bg-slate-50">
            <!-- Header Status -->
            <header class="h-16 bg-white border-b border-slate-200 px-8 flex items-center justify-between flex-shrink-0">
                <div class="flex items-center gap-4">
                    <span class="px-3 py-1 bg-indigo-50 text-indigo-600 font-black rounded-lg text-sm uppercase tracking-wider">
                        Soal No. {{ currentSoalIndex + 1 }}
                    </span>
                    <span class="text-slate-400 font-bold text-sm">dari {{ soal_list.length }}</span>
                </div>

                <div class="flex items-center gap-4">
                    <!-- Auto Save Indicator -->
                    <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider">
                        <template v-if="autoSaveStatus === 'saving'">
                            <span class="w-2 h-2 bg-amber-500 rounded-full animate-pulse"></span>
                            <span class="text-amber-600">Menyimpan...</span>
                        </template>
                        <template v-else-if="autoSaveStatus === 'saved'">
                            <span class="w-2 h-2 bg-emerald-500 rounded-full"></span>
                            <span class="text-emerald-600">Tersimpan</span>
                        </template>
                        <template v-else>
                            <span class="w-2 h-2 bg-rose-500 rounded-full"></span>
                            <span class="text-rose-600">Gagal Simpan</span>
                        </template>
                    </div>

                    <button v-if="!isFullscreen && sesi.wajib_fullscreen" @click="requestFullscreen" class="px-3 py-1.5 bg-rose-100 text-rose-700 hover:bg-rose-200 font-bold rounded-lg text-xs flex items-center gap-2 transition-colors">
                        <Maximize class="w-4 h-4" /> Masuk Fullscreen
                    </button>
                </div>
            </header>

            <!-- Question Area -->
            <div class="flex-1 overflow-y-auto p-4 md:p-12 custom-scrollbar">
                <div class="max-w-4xl mx-auto bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                    
                    <template v-if="currentSoal">
                        <!-- Question Content -->
                        <div class="p-8 md:p-12 border-b border-slate-100">
                            <div v-if="currentSoal.gambar_url" class="mb-8 rounded-2xl overflow-hidden border border-slate-100">
                                <img :src="currentSoal.gambar_url" alt="Gambar Soal" class="w-full h-auto object-contain max-h-96 bg-slate-50">
                            </div>
                            
                            <div class="prose prose-slate max-w-none text-lg leading-relaxed text-slate-800" v-html="currentSoal.pertanyaan"></div>
                        </div>

                    <!-- Answer Options based on Type -->
                    <div class="p-8 md:p-12 bg-slate-50/50">
                        
                        <!-- Pilihan Ganda -->
                        <div v-if="currentSoal.tipe === 'pg'" class="space-y-4">
                            <label 
                                v-for="opsi in currentSoal.pilihan" 
                                :key="opsi.kode"
                                class="flex items-start gap-4 p-5 rounded-2xl border-2 cursor-pointer transition-all hover:bg-white"
                                :class="localAnswers[currentSoal.id] === opsi.kode ? 'border-indigo-600 bg-indigo-50/50 shadow-md shadow-indigo-100' : 'border-slate-200 bg-white hover:border-indigo-300'"
                            >
                                <div class="flex items-center h-6">
                                    <input 
                                        type="radio" 
                                        :name="`soal_${currentSoal.id}`" 
                                        :value="opsi.kode"
                                        v-model="localAnswers[currentSoal.id]"
                                        @change="saveJawaban(2)"
                                        class="w-5 h-5 text-indigo-600 border-slate-300 focus:ring-indigo-600"
                                    >
                                </div>
                                <div class="flex-1">
                                    <div class="flex font-bold text-slate-900 mb-1">
                                        <span class="w-8">{{ opsi.kode }}.</span>
                                    </div>
                                    <div v-if="opsi.gambar_url" class="mb-3 rounded-xl overflow-hidden border border-slate-100 max-w-xs">
                                        <img :src="opsi.gambar_url" alt="Opsi" class="w-full h-auto object-contain">
                                    </div>
                                    <div class="prose prose-sm prose-slate text-slate-700" v-html="opsi.teks"></div>
                                </div>
                            </label>
                        </div>

                        <!-- Benar Salah -->
                        <div v-else-if="currentSoal.tipe === 'benar_salah'" class="flex gap-6">
                            <label 
                                class="flex-1 flex items-center justify-center gap-3 p-6 rounded-2xl border-2 cursor-pointer transition-all"
                                :class="localAnswers[currentSoal.id] === 'Benar' ? 'border-emerald-500 bg-emerald-50 text-emerald-700 shadow-md' : 'border-slate-200 bg-white hover:border-emerald-300 text-slate-600'"
                            >
                                <input type="radio" :value="'Benar'" v-model="localAnswers[currentSoal.id]" @change="saveJawaban(2)" class="hidden">
                                <span class="font-black text-lg uppercase tracking-wider">Benar</span>
                            </label>
                            
                            <label 
                                class="flex-1 flex items-center justify-center gap-3 p-6 rounded-2xl border-2 cursor-pointer transition-all"
                                :class="localAnswers[currentSoal.id] === 'Salah' ? 'border-rose-500 bg-rose-50 text-rose-700 shadow-md' : 'border-slate-200 bg-white hover:border-rose-300 text-slate-600'"
                            >
                                <input type="radio" :value="'Salah'" v-model="localAnswers[currentSoal.id]" @change="saveJawaban(2)" class="hidden">
                                <span class="font-black text-lg uppercase tracking-wider">Salah</span>
                            </label>
                        </div>

                        <!-- Essay -->
                        <div v-else-if="currentSoal.tipe === 'essay'">
                            <textarea 
                                v-model="localAnswers[currentSoal.id]"
                                @blur="saveJawaban(5)"
                                rows="8"
                                placeholder="Ketik jawaban Anda di sini..."
                                class="w-full p-5 bg-white border-2 border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-600/10 focus:border-indigo-600 text-slate-800 resize-y transition-all"
                            ></textarea>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-3 text-right">
                                Jawaban akan tersimpan otomatis saat Anda beralih kolom.
                            </p>
                        </div>

                        <!-- Menjodohkan -->
                        <div v-else-if="currentSoal.tipe === 'menjodohkan'" class="space-y-6">
                            <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-6">
                                <h4 class="text-sm font-bold text-indigo-900 mb-4 flex items-center gap-2">
                                    <AlertOctagon class="w-4 h-4" /> Pasangkan premis di kiri dengan jawaban di kanan:
                                </h4>
                                <div class="space-y-4">
                                    <div v-for="(kiri, i) in currentSoal.pilihan_kiri" :key="i" class="flex flex-col md:flex-row gap-4 items-start md:items-center">
                                        <div class="w-full md:w-1/2 p-4 bg-white rounded-xl border border-indigo-100 font-medium text-slate-700 shadow-sm">
                                            {{ kiri }}
                                        </div>
                                        <div class="w-full md:w-1/2">
                                            <select 
                                                :value="localAnswers[currentSoal.id]?.[kiri] || ''"
                                                @change="e => updateMenjodohkan(kiri, e.target.value)"
                                                class="w-full p-4 bg-white border border-indigo-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600 text-slate-700 font-medium shadow-sm cursor-pointer"
                                            >
                                                <option value="" disabled>-- Pilih Pasangan --</option>
                                                <option v-for="(kanan, j) in currentSoal.pilihan_kanan" :key="j" :value="kanan">
                                                    {{ kanan }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    </template>
                    <div v-else class="p-12 text-center text-slate-500">
                        <AlertTriangle class="w-12 h-12 mx-auto mb-4 text-slate-300" />
                        <h3 class="text-xl font-bold text-slate-700">Belum Ada Soal</h3>
                        <p class="mt-2">Tidak ada soal yang tersedia untuk ujian ini. Silakan hubungi proktor atau guru Anda.</p>
                    </div>
                </div>
            </div>

            <!-- Footer Navigation -->
            <footer class="h-20 bg-white border-t border-slate-200 px-8 flex items-center justify-between flex-shrink-0 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] z-10">
                <button 
                    @click="prevSoal"
                    :disabled="currentSoalIndex === 0"
                    class="flex items-center gap-2 px-6 py-3 rounded-xl font-bold transition-colors disabled:opacity-30"
                    :class="currentSoalIndex === 0 ? 'text-slate-400 bg-slate-50' : 'text-slate-700 bg-slate-100 hover:bg-slate-200'"
                >
                    <ChevronLeft class="w-5 h-5" /> Sebelumnya
                </button>
                
                <button 
                    @click="nextSoal"
                    :disabled="currentSoalIndex === soal_list.length - 1"
                    class="flex items-center gap-2 px-6 py-3 rounded-xl font-bold transition-colors disabled:opacity-30"
                    :class="currentSoalIndex === soal_list.length - 1 ? 'text-slate-400 bg-slate-50' : 'text-white bg-indigo-600 hover:bg-indigo-700 shadow-lg shadow-indigo-200'"
                >
                    Selanjutnya <ChevronRight class="w-5 h-5" />
                </button>
            </footer>
        </main>

        <!-- Custom Modal: Konfirmasi Selesai Ujian -->
        <div v-if="showConfirmSubmitModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-slate-100 overflow-hidden transform scale-100 transition-all duration-300">
                <div class="p-8 text-center">
                    <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <CheckCircle2 class="w-10 h-10" />
                    </div>
                    <h3 class="text-2xl font-black text-slate-900 mb-2">Selesaikan Ujian?</h3>
                    <p class="text-slate-500 font-medium text-sm mb-6 leading-relaxed">
                        Apakah Anda yakin ingin mengakhiri sesi ujian ini? Jawaban Anda akan langsung dikirim dan tidak dapat diubah kembali.
                    </p>

                    <!-- Summary Stats Card -->
                    <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-2xl mb-8 border border-slate-150 text-left">
                        <div class="border-r border-slate-200 pr-2">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Sudah Dijawab</span>
                            <span class="text-2xl font-black text-emerald-600 tabular-nums">{{ answeredSoalCount }}</span>
                            <span class="text-xs text-slate-500 font-medium block">soal</span>
                        </div>
                        <div class="pl-2">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Belum Dijawab</span>
                            <span class="text-2xl font-black text-rose-500 tabular-nums">{{ unansweredSoalCount }}</span>
                            <span class="text-xs text-slate-500 font-medium block">soal</span>
                        </div>
                    </div>

                    <!-- Warnings if any unanswered -->
                    <div v-if="unansweredSoalCount > 0" class="mb-6 p-4 bg-rose-50 border border-rose-100 text-rose-700 rounded-xl text-xs font-bold flex items-start gap-2 text-left animate-pulse">
                        <AlertTriangle class="w-4 h-4 flex-shrink-0 mt-0.5" />
                        <div>
                            Perhatian! Masih ada {{ unansweredSoalCount }} soal yang belum Anda jawab. Sangat disarankan untuk mengisi semua jawaban sebelum selesai.
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <button 
                            @click="closeConfirmSubmitModal" 
                            :disabled="isSubmitting"
                            class="flex-1 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition-colors text-sm"
                        >
                            Batal
                        </button>
                        <button 
                            @click="executeSubmitUjian" 
                            :disabled="isSubmitting"
                            class="flex-1 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition-colors shadow-lg shadow-indigo-100 text-sm flex items-center justify-center gap-2"
                        >
                            <span v-if="isSubmitting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                            <span>Ya, Selesai</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Custom Modal: Peringatan Pelanggaran -->
        <div v-if="showViolationModal" class="fixed inset-0 bg-slate-900/70 backdrop-blur-md flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-rose-100 overflow-hidden transform scale-100 transition-all duration-300">
                <div class="p-8 text-center">
                    <div class="w-16 h-16 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center mx-auto mb-6 animate-bounce">
                        <AlertTriangle class="w-10 h-10" />
                    </div>
                    <h3 class="text-2xl font-black text-rose-600 mb-2">Peringatan Pelanggaran!</h3>
                    <p class="text-slate-900 font-bold text-sm mb-4">
                        Sistem mendeteksi tindakan tidak sah:
                    </p>
                    <div class="bg-rose-50 border border-rose-100 text-rose-700 px-4 py-3 rounded-2xl font-black text-sm uppercase tracking-wide mb-6">
                        {{ violationDetails.tipe }}
                    </div>
                    <p class="text-slate-500 font-medium text-xs leading-relaxed mb-6">
                        Anda dilarang berpindah tab, menutup layar penuh, atau beralih fokus jendela browser selama ujian berlangsung. Pelanggaran berulang akan mengakibatkan diskualifikasi otomatis.
                    </p>

                    <!-- Limit Indicator -->
                    <div class="mb-6 py-2 px-4 bg-slate-100 rounded-xl inline-flex items-center gap-2 border border-slate-200">
                        <span class="text-xs font-bold text-slate-500">Jumlah Pelanggaran Anda:</span>
                        <span class="px-2.5 py-0.5 bg-rose-600 text-white rounded-full text-xs font-black">
                            {{ violationDetails.count }} / {{ sesi.max_pelanggaran }}
                        </span>
                    </div>

                    <button 
                        @click="closeViolationModal" 
                        class="w-full py-4 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-2xl transition-colors shadow-lg text-sm"
                    >
                        Saya Mengerti, Kembali ke Ujian
                    </button>
                </div>
            </div>
        </div>

        <!-- Custom Modal: Diskualifikasi -->
        <div v-if="showDisqualifiedModal" class="fixed inset-0 bg-rose-950 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-rose-200 overflow-hidden p-8 text-center animate-pulse">
                <div class="w-20 h-20 bg-rose-100 text-rose-600 rounded-3xl flex items-center justify-center mx-auto mb-8">
                    <AlertOctagon class="w-12 h-12" />
                </div>
                <h3 class="text-3xl font-black text-rose-600 mb-3">DISKUALIFIKASI</h3>
                <p class="text-slate-950 font-black text-md mb-6 uppercase tracking-wider">Akses Ujian Ditutup</p>
                <p class="text-slate-500 font-medium text-sm leading-relaxed mb-8">
                    Ujian Anda telah dihentikan karena Anda terdeteksi melakukan pelanggaran anti-cheat berulang kali di luar batas toleransi yang ditentukan.
                </p>

                <button 
                    @click="goToResults" 
                    class="w-full py-4 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-2xl transition-colors shadow-lg shadow-rose-200 text-sm"
                >
                    Lihat Laporan Sesi
                </button>
            </div>
        </div>

    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
