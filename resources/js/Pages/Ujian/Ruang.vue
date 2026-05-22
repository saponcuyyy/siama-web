<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed, watch, nextTick } from 'vue';
import axios from 'axios';

const props = defineProps({
    sesi: Object,
    peserta: Object,
    soal_list: Array,
    sisa_waktu: Number,
});

const currentIdx = ref(0);
const sisaDetik = ref(props.sisa_waktu);
const timer = ref(null);
const autoSave = ref('saved');
const showNav = ref(false);
const showConfirm = ref(false);
const showViolation = ref(false);
const showDQ = ref(false);
const violation = ref({ tipe: '', count: 0 });
const submitting = ref(false);
const isSubmittingOrConfirming = ref(false);
const isFullscreen = ref(false);

const answers = ref({});

const triggerMathJax = () => {
    nextTick(() => {
        if (window.MathJax?.typesetPromise) window.MathJax.typesetPromise();
    });
};

onMounted(() => {
    props.soal_list.forEach(s => { answers.value[s.id] = s.jawaban_siswa; });
    startTimer();
    document.addEventListener('visibilitychange', onVisibility);
    window.addEventListener('blur', onBlur);
    document.addEventListener('fullscreenchange', onFSChange);
    if (props.sesi.wajib_fullscreen) requestFS();
    triggerMathJax();
});

watch(currentIdx, () => triggerMathJax());

onUnmounted(() => {
    clearInterval(timer.value);
    document.removeEventListener('visibilitychange', onVisibility);
    window.removeEventListener('blur', onBlur);
    document.removeEventListener('fullscreenchange', onFSChange);
});

// Timer
function startTimer() {
    timer.value = setInterval(() => {
        if (sisaDetik.value > 0) sisaDetik.value--;
        else { clearInterval(timer.value); submitUjian('waktu_habis'); }
    }, 1000);
}

const timeStr = computed(() => {
    const h = Math.floor(sisaDetik.value / 3600);
    const m = Math.floor((sisaDetik.value % 3600) / 60);
    const s = sisaDetik.value % 60;
    return h > 0
        ? `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`
        : `${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
});

const timeLow = computed(() => sisaDetik.value < 300);

// Anti-cheat
function onVisibility() {
    if (isSubmittingOrConfirming.value) return;
    if (document.visibilityState === 'hidden') report('pindah_tab');
}
function onBlur() {
    if (isSubmittingOrConfirming.value) return;
    report('blur_window');
}
function onFSChange() {
    isFullscreen.value = !!document.fullscreenElement;
    if (isSubmittingOrConfirming.value) return;
    if (!isFullscreen.value && props.sesi.wajib_fullscreen) report('keluar_fullscreen');
}

async function requestFS() {
    try { await document.documentElement.requestFullscreen(); } catch {}
}

async function report(tipe) {
    if (isSubmittingOrConfirming.value) return;
    try {
        const r = await axios.post(route('ujian.pelanggaran', props.sesi.hashid), { tipe });
        isSubmittingOrConfirming.value = true;
        if (r.data.status === 'didiskualifikasi') { showDQ.value = true; }
        else {
            violation.value = { tipe, count: r.data.jumlah_pelanggaran };
            showViolation.value = true;
        }
    } catch {}
}

function closeViolation() {
    showViolation.value = false;
    setTimeout(() => {
        isSubmittingOrConfirming.value = false;
        if (props.sesi.wajib_fullscreen) requestFS();
    }, 500);
}

function goToResults() { window.location.href = route('ujian.hasil', props.sesi.hashid); }

// Stats
const total = computed(() => props.soal_list.length);
const answered = computed(() => props.soal_list.filter(s => {
    const a = answers.value[s.id];
    if (a === undefined || a === null) return false;
    if (s.tipe === 'menjodohkan') {
        if (typeof a !== 'object') return false;
        const keys = Object.keys(a);
        return keys.length > 0 && keys.length === (s.pilihan_kiri?.length || 0) && Object.values(a).every(v => v && v !== '');
    }
    return a !== '';
}).length);

const unanswered = computed(() => total.value - answered.value);

const currentSoal = computed(() => props.soal_list[currentIdx.value]);

// Save
async function saveJawaban(durasi = 0) {
    autoSave.value = 'saving';
    const soalId = currentSoal.value.id;
    const jawaban = answers.value[soalId];
    try {
        await axios.post(route('ujian.simpan', props.sesi.hashid), {
            soal_id: soalId, jawaban, durasi
        });
        autoSave.value = 'saved';
        const s = props.soal_list.find(x => x.id === soalId);
        if (s) s.jawaban_siswa = jawaban;
    } catch { autoSave.value = 'error'; }
}

function scrollToTop() {
    const main = document.querySelector('main.overflow-y-auto');
    if (main) main.scrollTop = 0;
}

function next() { if (currentIdx.value < props.soal_list.length - 1) { currentIdx.value++; scrollToTop(); } }
function prev() { if (currentIdx.value > 0) { currentIdx.value--; scrollToTop(); } }
function jump(i) { currentIdx.value = i; showNav.value = false; scrollToTop(); }

function submitUjian(reason = 'submit_manual') {
    if (reason === 'submit_manual') { isSubmittingOrConfirming.value = true; showConfirm.value = true; }
    else { isSubmittingOrConfirming.value = true; executeSubmit(); }
}

function closeConfirm() {
    showConfirm.value = false;
    setTimeout(() => { isSubmittingOrConfirming.value = false; }, 500);
}

function executeSubmit() {
    submitting.value = true;
    saveJawaban(0).finally(() => {
        router.post(route('ujian.selesai', props.sesi.hashid), {}, {
            onSuccess: () => { if (document.fullscreenElement) document.exitFullscreen(); },
            onError: () => { submitting.value = false; isSubmittingOrConfirming.value = false; showConfirm.value = false; }
        });
    });
}

// Menjodohkan
function updateMatch(kiri, val) {
    const id = currentSoal.value.id;
    const cur = answers.value[id] || {};
    cur[kiri] = val;
    answers.value[id] = { ...cur };
    saveJawaban(2);
}
</script>

<template>
    <Head title="Mengerjakan Ujian" />

    <!-- ═══ FULL DISQUALIFIED OVERLAY ═══ -->
    <div v-if="showDQ" class="fixed inset-0 z-[100] flex items-center justify-center p-4"
        style="background:radial-gradient(ellipse at center,#1a0a0a,#0a0a12)">
        <div class="bg-white/5 backdrop-blur-2xl border border-white/10 rounded-3xl max-w-sm w-full p-8 text-center">
            <div class="w-16 h-16 mx-auto mb-5 rounded-2xl flex items-center justify-center"
                style="background:linear-gradient(135deg,#e11d48,#be123c)">
                <svg class="w-8 h-8 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" /><line x1="12" y1="8" x2="12" y2="12" />
                    <line x1="12" y1="16" x2="12.01" y2="16" />
                </svg>
            </div>
            <h2 class="text-2xl font-black text-white mb-1">Diskualifikasi</h2>
            <p class="text-rose-400 text-sm font-bold mb-6 uppercase tracking-wider">Akses Ujian Ditutup</p>
            <p class="text-slate-400 text-sm leading-relaxed mb-8">
                Ujian Anda dihentikan karena pelanggaran anti-cheat berulang melebihi batas toleransi.
            </p>
            <button @click="goToResults"
                class="w-full py-3.5 rounded-xl font-bold text-white transition-all"
                style="background:linear-gradient(135deg,#e11d48,#be123c)">
                Lihat Hasil Ujian
            </button>
        </div>
    </div>

    <!-- ═══ MAIN LAYOUT ═══ -->
    <div class="h-screen flex flex-col overflow-hidden select-none bg-slate-50">

        <!-- ═══ TOP BAR ═══ -->
        <header class="h-14 md:h-16 flex items-center justify-between px-4 md:px-6 bg-white border-b border-slate-200 shrink-0 z-20">
            <div class="flex items-center gap-3 min-w-0">
                <span class="px-2.5 py-1 rounded-lg text-xs font-black uppercase tracking-wider shrink-0"
                    style="background:rgba(6,182,212,.1);color:#0891b2">
                    #{{ currentIdx + 1 }}
                </span>
                <span class="text-slate-400 text-xs font-bold shrink-0">/ {{ total }}</span>
                <span class="hidden sm:inline text-xs font-bold text-slate-500 truncate ml-1">
                    {{ sesi.paket_ujian.mata_pelajaran?.nama }}
                </span>
            </div>

            <div class="flex items-center gap-2 md:gap-3">
                <!-- Save status -->
                <span class="text-[10px] font-bold uppercase tracking-wider hidden xs:inline"
                    :class="autoSave==='saving'?'text-amber-500':autoSave==='saved'?'text-emerald-500':'text-rose-500'">
                    {{ autoSave==='saving'?'Menyimpan...':autoSave==='saved'?'' :'Error' }}
                </span>

                <!-- Timer -->
                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-black tabular-nums"
                    :class="timeLow?'bg-rose-50 text-rose-600':'bg-slate-100 text-slate-700'">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round"
                        :class="timeLow?'animate-pulse':''">
                        <circle cx="12" cy="12" r="10" /><polyline points="12 6 12 12 16 14" />
                    </svg>
                    {{ timeStr }}
                </div>

                <!-- Navigator toggle -->
                <button @click="showNav = !showNav"
                    class="h-9 px-3 rounded-xl text-xs font-bold text-white transition-all shrink-0"
                    style="background:linear-gradient(135deg,#0f172a,#1e293b)">
                    <span class="hidden sm:inline">Soal</span>
                    <svg class="sm:hidden w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="6" x2="21" y2="6" /><line x1="3" y1="12" x2="21" y2="12" />
                        <line x1="3" y1="18" x2="21" y2="18" />
                    </svg>
                </button>
            </div>
        </header>

        <!-- ═══ QUESTION AREA ═══ -->
        <main class="flex-1 overflow-y-auto overscroll-contain">
            <div class="max-w-3xl mx-auto p-4 md:p-8 lg:p-10">

                <template v-if="currentSoal">
                    <!-- Question card -->
                    <div class="bg-white rounded-2xl md:rounded-3xl shadow-sm border border-slate-200 overflow-hidden">

                        <!-- Image -->
                        <div v-if="currentSoal.gambar_url"
                            class="bg-slate-50 border-b border-slate-100">
                            <img :src="currentSoal.gambar_url" alt="Gambar Soal"
                                class="w-full h-auto max-h-80 object-contain mx-auto" />
                        </div>

                        <!-- Question text -->
                        <div class="px-5 py-6 md:px-8 md:py-8 border-b border-slate-100">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Soal No. {{ currentIdx + 1 }}</p>
                            <div class="prose prose-slate max-w-none text-base md:text-lg leading-relaxed"
                                v-html="currentSoal.pertanyaan" />
                        </div>

                        <!-- Answer area -->
                        <div class="px-5 py-6 md:px-8 md:py-8">

                            <!-- PG -->
                            <div v-if="currentSoal.tipe === 'pg'" class="space-y-3">
                                <label v-for="opsi in currentSoal.pilihan" :key="opsi.kode"
                                    class="flex items-start gap-3 md:gap-4 p-4 md:p-5 rounded-xl md:rounded-2xl border-2 cursor-pointer transition-all"
                                    :class="answers[currentSoal.id] === opsi.kode
                                        ? 'border-emerald-500 bg-emerald-50/50'
                                        : 'border-slate-200 bg-white hover:border-slate-300'">
                                    <div class="flex items-center h-5 md:h-6">
                                        <input type="radio" :name="'s'+currentSoal.id" :value="opsi.kode"
                                            v-model="answers[currentSoal.id]" @change="saveJawaban(2)"
                                            class="w-4 h-4 md:w-5 md:h-5 accent-emerald-600" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div v-if="opsi.gambar_url" class="rounded-xl overflow-hidden border border-slate-100 max-w-xs">
                                            <img :src="opsi.gambar_url" alt="Opsi" class="w-full h-auto" />
                                        </div>
                                        <div v-if="opsi.teks" class="prose prose-sm prose-slate mt-1" v-html="opsi.teks" />
                                    </div>
                                </label>
                            </div>

                            <!-- Benar/Salah -->
                            <div v-else-if="currentSoal.tipe === 'benar_salah'" class="flex gap-4">
                                <label v-for="opt in ['Benar','Salah']" :key="opt"
                                    class="flex-1 flex items-center justify-center gap-2 p-5 md:p-6 rounded-xl md:rounded-2xl border-2 cursor-pointer transition-all font-black text-sm md:text-base uppercase tracking-wider"
                                    :class="answers[currentSoal.id] === opt
                                        ? opt==='Benar'?'border-emerald-500 bg-emerald-50 text-emerald-700':'border-rose-500 bg-rose-50 text-rose-700'
                                        : 'border-slate-200 bg-white text-slate-500 hover:border-slate-300'">
                                    <input type="radio" :value="opt" v-model="answers[currentSoal.id]"
                                        @change="saveJawaban(2)" class="hidden" />
                                    {{ opt }}
                                </label>
                            </div>

                            <!-- Essay -->
                            <div v-else-if="currentSoal.tipe === 'essay'">
                                <textarea v-model="answers[currentSoal.id]" @blur="saveJawaban(5)"
                                    rows="6" placeholder="Ketik jawaban Anda di sini..."
                                    class="w-full p-4 md:p-5 bg-white border-2 border-slate-200 rounded-xl md:rounded-2xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 text-slate-800 resize-y transition-all text-sm md:text-base">
                                </textarea>
                                <p class="text-[10px] md:text-xs font-bold text-slate-400 uppercase tracking-widest mt-3 text-right">
                                    Tersimpan otomatis saat pindah soal
                                </p>
                            </div>

                            <!-- Menjodohkan -->
                            <div v-else-if="currentSoal.tipe === 'menjodohkan'" class="space-y-4">
                                <div class="p-4 md:p-5 rounded-xl md:rounded-2xl"
                                    style="background:rgba(6,182,212,.04);border:1px solid rgba(6,182,212,.1)">
                                    <p class="text-xs font-bold md:text-sm mb-4 flex items-center gap-2"
                                        style="color:#0891b2">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                                            <line x1="12" y1="9" x2="12" y2="13" /><line x1="12" y1="17" x2="12.01" y2="17" />
                                        </svg>
                                        Pasangkan dengan tepat
                                    </p>
                                    <div class="space-y-3">
                                        <div v-for="(kiri,i) in currentSoal.pilihan_kiri" :key="i"
                                            class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center">
                                            <div class="flex-1 p-3 md:p-4 bg-white rounded-xl border border-slate-200 font-medium text-slate-700 text-sm shadow-sm">
                                                {{ kiri }}
                                            </div>
                                            <select :value="answers[currentSoal.id]?.[kiri]||''"
                                                @change="e=>updateMatch(kiri,e.target.value)"
                                                class="flex-1 p-3 md:p-4 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium text-sm shadow-sm cursor-pointer focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                                <option value="" disabled>-- Pilih --</option>
                                                <option v-for="(kanan,j) in currentSoal.pilihan_kanan" :key="j" :value="kanan">
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

                <!-- No soal -->
                <div v-else class="text-center py-16 text-slate-400">
                    <svg class="w-12 h-12 mx-auto mb-4 text-slate-300" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10" /><line x1="12" y1="8" x2="12" y2="12" />
                        <line x1="12" y1="16" x2="12.01" y2="16" />
                    </svg>
                    <h3 class="text-lg font-bold text-slate-600">Belum Ada Soal</h3>
                    <p class="text-sm mt-1">Hubungi pengawas ujian Anda.</p>
                </div>
            </div>
        </main>

        <!-- ═══ BOTTOM BAR ═══ -->
        <footer class="h-16 md:h-18 bg-white border-t border-slate-200 px-3 md:px-4 flex items-center justify-between shrink-0 z-20 gap-2">
            <!-- Left: Sebelumnya -->
            <button @click="prev" :disabled="currentIdx===0"
                class="flex items-center gap-1 md:gap-1.5 px-3 md:px-4 py-2.5 md:py-3 rounded-xl text-xs md:text-sm font-bold transition-all disabled:opacity-30 shrink-0"
                :class="currentIdx===0?'text-slate-400 bg-slate-50':'text-slate-700 bg-slate-100 hover:bg-slate-200'">
                <svg class="w-4 h-4 md:w-5 md:h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6" />
                </svg>
                <span class="hidden xs:inline">Sebelumnya</span>
            </button>

            <!-- Center: Selesai -->
            <button @click="submitUjian('submit_manual')"
                class="flex items-center gap-1.5 px-5 md:px-8 py-2.5 md:py-3 rounded-xl text-xs md:text-sm font-bold text-white transition-all shrink-0"
                style="background:linear-gradient(135deg,#059669,#047857)">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12" />
                </svg>
                Selesai
            </button>

            <!-- Navigator pills (desktop) + Right: Selanjutnya -->
            <div class="flex items-center gap-2 md:gap-3">
                <!-- Navigator pills (desktop) -->
                <div class="hidden md:flex items-center gap-1">
                    <button v-for="(s,i) in soal_list" :key="s.id" @click="jump(i)"
                        class="w-8 h-8 rounded-lg text-xs font-bold transition-all border"
                        :class="currentIdx===i
                            ? 'text-white border-transparent scale-110 shadow-md'
                            : answers[s.id] && answers[s.id]!=='' && (typeof answers[s.id]!=='object' || Object.keys(answers[s.id]).length>0)
                                ? 'border-emerald-500 bg-emerald-50 text-emerald-700'
                                : 'border-slate-200 bg-white text-slate-500 hover:border-slate-300'"
                        :style="currentIdx===i?'background:linear-gradient(135deg,#0891b2,#06b6d4)':''">
                        {{ i+1 }}
                    </button>
                </div>

                <button @click="next" :disabled="currentIdx===total-1"
                    class="flex items-center gap-1 md:gap-1.5 px-3 md:px-4 py-2.5 md:py-3 rounded-xl text-xs md:text-sm font-bold transition-all disabled:opacity-30 text-white shrink-0"
                    :class="currentIdx===total-1?'bg-slate-400':'bg-slate-900 hover:bg-slate-800'"
                    style="background:currentIdx===total-1?'#94a3b8':'linear-gradient(135deg,#0891b2,#06b6d4)'">
                    <span class="hidden xs:inline">Selanjutnya</span>
                    <svg class="w-4 h-4 md:w-5 md:h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6" />
                    </svg>
                </button>
            </div>
        </footer>

        <!-- ═══ NAVIGATOR BOTTOM SHEET ═══ -->
        <teleport to="body">
            <div v-if="showNav" class="fixed inset-0 z-50 flex flex-col justify-end">
                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="showNav=false" />
                <div class="relative bg-white rounded-t-3xl shadow-2xl max-h-[70vh] flex flex-col animate-slide-up">
                    <div class="shrink-0 px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h3 class="font-black text-slate-900">Daftar Soal</h3>
                            <p class="text-xs text-slate-500 font-medium mt-0.5">
                                {{ answered }} dijawab · {{ unanswered }} belum
                            </p>
                        </div>
                        <button @click="showNav=false" class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center hover:bg-slate-200 transition-colors">
                            <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18" /><line x1="6" y1="6" x2="18" y2="18" />
                            </svg>
                        </button>
                    </div>
                    <div class="flex-1 overflow-y-auto p-5">
                        <div class="grid grid-cols-5 sm:grid-cols-6 md:grid-cols-8 gap-2">
                            <button v-for="(s,i) in soal_list" :key="s.id" @click="jump(i)"
                                class="aspect-square flex items-center justify-center rounded-xl text-xs font-bold transition-all border"
                                :class="currentIdx===i
                                    ? 'text-white border-transparent scale-110 shadow-md'
                                    : answers[s.id] && answers[s.id]!=='' && (typeof answers[s.id]!=='object' || Object.keys(answers[s.id]).length>0)
                                        ? 'border-emerald-500 bg-emerald-50 text-emerald-700'
                                        : 'border-slate-200 bg-white text-slate-500 hover:border-slate-300'"
                                :style="currentIdx===i?'background:linear-gradient(135deg,#0891b2,#06b6d4)':''">
                                {{ i+1 }}
                            </button>
                        </div>
                    </div>
                    <div class="shrink-0 px-6 py-4 border-t border-slate-100 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-3 text-xs font-bold text-slate-500">
                            <span class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-sm border-2 border-emerald-500 bg-emerald-50"></span> Dijawab
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-sm border-2 border-slate-200 bg-white"></span> Kosong
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-sm" style="background:linear-gradient(135deg,#0891b2,#06b6d4)"></span> Aktif
                            </span>
                        </div>
                        <button @click="showNav=false"
                            class="px-6 py-2.5 rounded-xl text-xs font-bold text-white transition-all"
                            style="background:linear-gradient(135deg,#0f172a,#1e293b)">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </teleport>

        <!-- ═══ CONFIRM SUBMIT MODAL ═══ -->
        <div v-if="showConfirm" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm">
            <div class="bg-white rounded-3xl max-w-sm w-full shadow-2xl border border-slate-100 overflow-hidden animate-pop">
                <div class="p-6 md:p-8 text-center">
                    <div class="w-14 h-14 mx-auto mb-5 rounded-2xl flex items-center justify-center"
                        style="background:linear-gradient(135deg,#0891b2,#06b6d4)">
                        <svg class="w-8 h-8 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                            <polyline points="22 4 12 14.01 9 11.01" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-black text-slate-900 mb-1">Selesaikan Ujian?</h3>
                    <p class="text-slate-500 text-sm font-medium mb-6">Jawaban akan dikirim dan tidak bisa diubah kembali.</p>

                    <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-2xl mb-6">
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Dijawab</p>
                            <p class="text-2xl font-black text-emerald-600 tabular-nums">{{ answered }}</p>
                            <p class="text-xs text-slate-500 font-medium">soal</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Belum</p>
                            <p class="text-2xl font-black text-rose-500 tabular-nums">{{ unanswered }}</p>
                            <p class="text-xs text-slate-500 font-medium">soal</p>
                        </div>
                    </div>

                    <div v-if="unanswered > 0" class="mb-5 p-3 bg-amber-50 border border-amber-100 rounded-xl text-xs font-bold text-amber-700 flex items-start gap-2 text-left">
                        <svg class="w-4 h-4 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                            <line x1="12" y1="9" x2="12" y2="13" /><line x1="12" y1="17" x2="12.01" y2="17" />
                        </svg>
                        Masih ada {{ unanswered }} soal belum dijawab.
                    </div>

                    <div class="flex gap-3">
                        <button @click="closeConfirm" :disabled="submitting"
                            class="flex-1 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition-colors text-sm">
                            Batal
                        </button>
                        <button @click="executeSubmit" :disabled="submitting"
                            class="flex-1 py-3 rounded-xl font-bold text-white text-sm transition-all flex items-center justify-center gap-2"
                            style="background:linear-gradient(135deg,#0891b2,#06b6d4)">
                            <span v-if="submitting" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                            <span>Ya, Kirim</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══ VIOLATION MODAL ═══ -->
        <div v-if="showViolation" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-md">
            <div class="bg-white rounded-3xl max-w-sm w-full shadow-2xl border border-rose-100 overflow-hidden animate-pop">
                <div class="p-6 md:p-8 text-center">
                    <div class="w-14 h-14 mx-auto mb-5 rounded-2xl flex items-center justify-center bg-rose-50 animate-bounce">
                        <svg class="w-8 h-8 text-rose-600" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                            <line x1="12" y1="9" x2="12" y2="13" /><line x1="12" y1="17" x2="12.01" y2="17" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-black text-rose-600 mb-1">Peringatan!</h3>
                    <p class="text-slate-900 font-bold text-sm mb-4">Sistem mendeteksi aktivitas mencurigakan:</p>
                    <div class="bg-rose-50 border border-rose-100 text-rose-700 px-4 py-3 rounded-2xl font-black text-xs uppercase tracking-wide mb-5">
                        {{ violation.tipe === 'blur_window' ? 'Focus Hilang' : violation.tipe === 'pindah_tab' ? 'Pindah Tab' : 'Keluar Fullscreen' }}
                    </div>
                    <div class="mb-5 inline-flex items-center gap-2 py-2 px-4 bg-slate-100 rounded-xl border border-slate-200">
                        <span class="text-xs font-bold text-slate-500">Pelanggaran:</span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-black text-white"
                            :style="violation.count >= props.sesi.max_pelanggaran ? 'background:#e11d48' : 'background:#f59e0b'">
                            {{ violation.count }} / {{ props.sesi.max_pelanggaran }}
                        </span>
                    </div>
                    <button @click="closeViolation"
                        class="w-full py-3.5 rounded-xl font-bold text-white text-sm transition-all"
                        style="background:linear-gradient(135deg,#0f172a,#1e293b)">
                        Kembali ke Ujian
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Custom scrollbar */
.overflow-y-auto::-webkit-scrollbar { width: 4px; }
.overflow-y-auto::-webkit-scrollbar-track { background: transparent; }
.overflow-y-auto::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

/* Animations */
.animate-slide-up {
    animation: slideUp .35s cubic-bezier(.22,1,.36,1) forwards;
}
@keyframes slideUp {
    from { transform: translateY(100%); }
    to { transform: translateY(0); }
}

.animate-pop {
    animation: pop .3s cubic-bezier(.22,1,.36,1) forwards;
}
@keyframes pop {
    from { opacity: 0; transform: scale(.92) translateY(10px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}

/* xs breakpoint helper */
@media (min-width: 420px) {
    .xs\:inline { display: inline; }
}
</style>
