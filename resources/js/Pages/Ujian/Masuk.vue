<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, onMounted, nextTick } from 'vue';

const props = defineProps({
    sesi: Object,
    peserta: Object,
});

const form = useForm({
    token: '',
    device_token: '',
    browser_info: navigator.userAgent,
});

const digits = ref(['', '', '', '', '', '', '', '']);
const inputRefs = ref([]);

onMounted(() => {
    let devToken = localStorage.getItem('cbt_device_token');
    if (!devToken) {
        devToken = Array.from(crypto.getRandomValues(new Uint8Array(32)))
            .map(b => b.toString(16).padStart(2, '0')).join('');
        localStorage.setItem('cbt_device_token', devToken);
    }
    form.device_token = devToken;
});

const onDigitInput = (index, event) => {
    const val = event.target.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
    digits.value[index] = val;
    form.token = digits.value.join('');

    if (val && index < 7) {
        nextTick(() => {
            inputRefs.value[index + 1]?.focus();
        });
    }
};

const onDigitKeydown = (index, event) => {
    if (event.key === 'Backspace' && !digits.value[index] && index > 0) {
        nextTick(() => inputRefs.value[index - 1]?.focus());
    }
    if (event.key === 'ArrowLeft' && index > 0) {
        nextTick(() => inputRefs.value[index - 1]?.focus());
    }
    if (event.key === 'ArrowRight' && index < 7) {
        nextTick(() => inputRefs.value[index + 1]?.focus());
    }
};

const onDigitPaste = (event) => {
    const pasted = (event.clipboardData || window.clipboardData).getData('text');
    const cleaned = pasted.replace(/[^a-zA-Z0-9]/g, '').toUpperCase().slice(0, 8);
    if (cleaned) {
        event.preventDefault();
        const chars = cleaned.split('');
        chars.forEach((ch, i) => { if (i < 8) digits.value[i] = ch; });
        form.token = digits.value.join('');
        const nextIdx = Math.min(chars.length, 7);
        inputRefs.value[nextIdx]?.focus();
    }
};

const submitToken = () => {
    form.post(route('ujian.mulai', props.sesi.hashid));
};

const waktuMulai = props.sesi.waktu_mulai
    ? new Date(props.sesi.waktu_mulai).toLocaleString('id-ID', {
        weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    })
    : null;
</script>

<template>
    <Head title="Masuk Ruang Ujian" />

    <div class="page">
        <div class="orb o1" /><div class="orb o2" /><div class="orb o3" /><div class="orb o4" />
        <div class="grid-bg" />

        <div class="container">
            <div class="card">

                <!-- HEADER -->
                <div class="text-center mb-6 sm:mb-8">
                    <div class="badge-wrap">
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
                    <h1 class="title">Portal Ujian CBT</h1>
                    <div class="sesi-badge">
                        <div class="sesi-dot" />
                        <span>{{ sesi.nama_sesi }}</span>
                    </div>
                </div>

                <!-- INFO -->
                <div class="info-grid">
                    <div class="info-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z" />
                            <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z" />
                        </svg>
                        <div class="min-w-0">
                            <p class="info-lbl">Mata Pelajaran</p>
                            <p class="info-val truncate">{{ sesi.paket_ujian.mata_pelajaran?.nama || 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="info-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                        <div class="min-w-0">
                            <p class="info-lbl">Durasi</p>
                            <p class="info-val">{{ sesi.paket_ujian.durasi_menit }} Menit</p>
                        </div>
                    </div>
                    <div class="info-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                        </svg>
                        <div class="min-w-0">
                            <p class="info-lbl">Sifat</p>
                            <span class="fullscreen-badge">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3" />
                                </svg>
                                Fullscreen
                            </span>
                        </div>
                    </div>
                </div>

                <!-- WAKTU -->
                <p v-if="waktuMulai" class="waktu-info">
                    <span class="waktu-lbl">Jadwal:</span>
                    <span class="waktu-val">{{ waktuMulai }}</span>
                </p>

                <!-- DIVIDER -->
                <div class="divider"><span class="divider-lbl">Masukkan Token 8 Karakter</span></div>

                <!-- TOKEN FORM -->
                <form @submit.prevent="submitToken" class="form-section">
                    <div>
                        <label class="token-lbl">Token Ujian</label>
                        <div class="digits-row">
                            <input
                                v-for="(d, i) in digits" :key="i"
                                :ref="el => { inputRefs[i] = el }"
                                :id="'d' + i" type="text" inputmode="text"
                                maxlength="1" autocomplete="off"
                                v-model="digits[i]"
                                @input="onDigitInput(i, $event)"
                                @keydown="onDigitKeydown(i, $event)"
                                @paste="i === 0 && onDigitPaste($event)"
                                class="digit-box"
                                :class="{ filled: digits[i], error: form.errors.token }"
                            />
                        </div>
                        <p v-if="form.errors.token" class="err-msg">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="12" y1="8" x2="12" y2="12" />
                                <line x1="12" y1="16" x2="12.01" y2="16" />
                            </svg>
                            <span>{{ form.errors.token }}</span>
                        </p>
                    </div>

                    <button type="submit" :disabled="form.processing" class="btn-primary">
                        <span class="btn-bg" />
                        <span class="btn-inner">
                            <template v-if="form.processing">
                                <span class="spinner" />
                                <span>Memproses...</span>
                            </template>
                            <template v-else>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
                                    <polyline points="10 17 15 12 10 7" />
                                    <line x1="15" y1="12" x2="3" y2="12" />
                                </svg>
                                <span>Mulai Kerjakan</span>
                            </template>
                        </span>
                    </button>
                </form>

                <!-- WARNING -->
                <div class="alert">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                        <line x1="12" y1="9" x2="12" y2="13" />
                        <line x1="12" y1="17" x2="12.01" y2="17" />
                    </svg>
                    <p class="alert-text">
                        Dengan menekan tombol mulai, Anda menyetujui peraturan ujian. Pelanggaran (seperti pindah tab)
                        akan dicatat dan dapat membatalkan ujian Anda.
                    </p>
                </div>

                <!-- HELP -->
                <div class="help-box">
                    <p class="help-title">Butuh Bantuan?</p>
                    <p class="help-text">Jika token tidak diterima atau mengalami kendala, hubungi pengawas ujian.</p>
                </div>
            </div>

            <p class="footer">&copy; {{ new Date().getFullYear() }} SMA Negeri 2 Perbaungan</p>
        </div>
    </div>
</template>

<style scoped>
/* ═══════════════════ BASE ═══════════════════ */
.page {
    min-height: 100vh;
    background: linear-gradient(135deg, #050a18 0%, #0a1628 30%, #0f1f3d 60%, #0a1628 100%);
    position: relative;
    overflow: hidden;
    font-family: 'Inter', 'Segoe UI', sans-serif;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

/* ═══════════════════ BG LAYERS ═══════════════════ */
.grid-bg {
    position: fixed; inset: 0;
    background-image: linear-gradient(rgba(255,255,255,.015) 1px, transparent 1px),
                      linear-gradient(90deg, rgba(255,255,255,.015) 1px, transparent 1px);
    background-size: 48px 48px;
    pointer-events: none; z-index: 0;
    mask-image: radial-gradient(ellipse 60% 50% at center, black 30%, transparent 70%);
    -webkit-mask-image: radial-gradient(ellipse 60% 50% at center, black 30%, transparent 70%);
}

.orb {
    position: fixed; border-radius: 50%;
    filter: blur(100px); pointer-events: none; z-index: 0;
}
.o1 { width: 500px; height: 500px; background: radial-gradient(circle, rgba(6,182,212,.12), transparent); top: -150px; right: -80px; animation: fa 12s ease-in-out infinite; }
.o2 { width: 420px; height: 420px; background: radial-gradient(circle, rgba(59,130,246,.10), transparent); bottom: -120px; left: -120px; animation: fb 10s ease-in-out infinite; }
.o3 { width: 350px; height: 350px; background: radial-gradient(circle, rgba(245,158,11,.05), transparent); top: 40%; left: 60%; animation: fc 15s ease-in-out infinite; }
.o4 { width: 300px; height: 300px; background: radial-gradient(circle, rgba(99,102,241,.06), transparent); top: 20%; left: 10%; animation: fd 14s ease-in-out infinite; }

@keyframes fa { 0%,100%{transform:translate(0,0)scale(1)} 33%{transform:translate(-30px,25px)scale(1.05)} 66%{transform:translate(15px,-15px)scale(.95)} }
@keyframes fb { 0%,100%{transform:translate(0,0)} 50%{transform:translate(25px,-30px)} }
@keyframes fc { 0%,100%{transform:translate(0,0)scale(1)} 50%{transform:translate(-15px,15px)scale(1.08)} }
@keyframes fd { 0%,100%{transform:translate(0,0)scale(1)} 33%{transform:translate(20px,10px)scale(.95)} 66%{transform:translate(-10px,-20px)scale(1.05)} }

/* ═══════════════════ CONTAINER & CARD ═══════════════════ */
.container {
    position: relative; z-index: 10;
    width: 100%; max-width: 520px;
    animation: fadeUp .7s cubic-bezier(.22,1,.36,1) forwards;
}
@keyframes fadeUp { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }

.card {
    background: rgba(15,23,42,.75);
    border: 1px solid rgba(255,255,255,.06);
    border-radius: 24px;
    padding: 1.5rem 1.25rem;
    box-shadow: 0 25px 60px -12px rgba(0,0,0,.6), inset 0 1px 0 rgba(255,255,255,.05);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
}

/* ═══════════════════ HEADER ═══════════════════ */
.badge-wrap { display: flex; justify-content: center; }
.badge-ring {
    width: 72px; height: 72px; border-radius: 50%;
    background: linear-gradient(135deg, rgba(6,182,212,.5), rgba(6,182,212,.1), rgba(6,182,212,.4));
    padding: 2px; animation: pulse 3s ease-in-out infinite;
    box-shadow: 0 0 25px rgba(6,182,212,.12);
}
.badge-inner {
    width: 100%; height: 100%; border-radius: 50%;
    background: linear-gradient(135deg, #0f172a, #1e293b);
    display: flex; align-items: center; justify-content: center;
}
.badge-icon { width: 32px; height: 32px; color: #22d3ee; }

@keyframes pulse {
    0%,100%{box-shadow:0 0 15px rgba(6,182,212,.08),0 0 30px rgba(6,182,212,.04)}
    50%{box-shadow:0 0 25px rgba(6,182,212,.2),0 0 50px rgba(6,182,212,.08)}
}

.title {
    font-size: 1.35rem; font-weight: 900; color: #fff;
    margin-top: 1.25rem; margin-bottom: .5rem;
    letter-spacing: -.02em;
}

.sesi-badge {
    display: inline-flex; align-items: center; gap: .5rem;
    background: rgba(255,255,255,.05);
    padding: .375rem 1rem; border-radius: 999px;
    border: 1px solid rgba(255,255,255,.06);
}
.sesi-dot { width: 6px; height: 6px; border-radius: 50%; background: #34d399; animation: pulse 2s infinite; }
.sesi-badge span { font-size: .8125rem; font-weight: 700; color: #cbd5e1; }

/* ═══════════════════ INFO GRID ═══════════════════ */
.info-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: .375rem;
    margin-top: 1.25rem;
}

.info-item {
    display: flex; align-items: center; gap: .625rem;
    padding: .625rem .75rem;
    border-radius: 12px;
    background: rgba(255,255,255,.025);
    border: 1px solid rgba(255,255,255,.035);
    transition: background .2s;
}
.info-item:hover { background: rgba(255,255,255,.04); }

.info-item svg { width: 18px; height: 18px; color: #22d3ee; flex-shrink: 0; }

.info-lbl {
    font-size: .625rem; font-weight: 700; color: #64748b;
    text-transform: uppercase; letter-spacing: .08em;
    margin-bottom: 1px;
}
.info-val { font-size: .8125rem; font-weight: 700; color: #f1f5f9; }

.fullscreen-badge {
    display: inline-flex; align-items: center; gap: 3px;
    font-size: .65rem; font-weight: 700; color: #fbbf24;
    background: rgba(251,191,36,.1);
    padding: 1px 6px; border-radius: 5px;
    border: 1px solid rgba(251,191,36,.12);
}
.fullscreen-badge svg { width: 11px; height: 11px; color: #fbbf24; }

/* ═══════════════════ WAKTU ═══════════════════ */
.waktu-info {
    margin-top: .75rem; text-align: center;
    font-size: .6875rem; font-weight: 500; color: #64748b;
}
.waktu-lbl { color: #475569; }
.waktu-val { color: #94a3b8; margin-left: .25rem; }

/* ═══════════════════ DIVIDER ═══════════════════ */
.divider {
    display: flex; align-items: center; gap: .75rem;
    margin: 1.25rem 0 0;
}
.divider::before, .divider::after {
    content: ''; flex: 1; height: 1px;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,.06), transparent);
}
.divider-lbl {
    font-size: .6rem; font-weight: 700; color: #64748b;
    text-transform: uppercase; letter-spacing: .12em; white-space: nowrap;
}

/* ═══════════════════ FORM ═══════════════════ */
.form-section { margin-top: 1.25rem; display: flex; flex-direction: column; gap: 1.25rem; }

.token-lbl {
    display: block; text-align: center;
    font-size: .65rem; font-weight: 700; color: #94a3b8;
    text-transform: uppercase; letter-spacing: .12em;
    margin-bottom: .75rem;
}

/* ─── DIGIT BOXES (CSS Grid — always fits) ─── */
.digits-row {
    display: grid;
    grid-template-columns: repeat(8, 1fr);
    gap: .375rem;
    max-width: 100%;
}

.digit-box {
    aspect-ratio: 1 / 1;
    width: 100%;
    text-align: center;
    font-size: 1.125rem;
    font-weight: 800;
    color: #f1f5f9;
    background: rgba(255,255,255,.04);
    border: 1.5px solid rgba(255,255,255,.08);
    border-radius: 10px;
    outline: none;
    transition: all .2s;
    caret-color: #22d3ee;
    -webkit-appearance: none;
    appearance: none;
}

.digit-box:focus {
    border-color: rgba(6,182,212,.5);
    background: rgba(6,182,212,.06);
    box-shadow: 0 0 0 3px rgba(6,182,212,.15);
    transform: translateY(-1px);
}

.digit-box.filled {
    border-color: rgba(6,182,212,.3);
    background: rgba(6,182,212,.08);
}

.digit-box.error {
    border-color: rgba(244,63,94,.5);
    background: rgba(244,63,94,.06);
    animation: shake .4s ease-in-out;
}
.digit-box.error:focus {
    border-color: rgba(244,63,94,.6);
    box-shadow: 0 0 0 3px rgba(244,63,94,.15);
}

@keyframes shake {
    0%,100%{transform:translateX(0)}
    20%{transform:translateX(-4px)}
    40%{transform:translateX(4px)}
    60%{transform:translateX(-3px)}
    80%{transform:translateX(3px)}
}

.err-msg {
    margin-top: .625rem; display: flex; align-items: center; justify-content: center;
    gap: .375rem; font-size: .6875rem; font-weight: 700; color: #fb7185;
}
.err-msg svg { width: 14px; height: 14px; flex-shrink: 0; }

/* ─── BUTTON ─── */
.btn-primary {
    position: relative; width: 100%; border: none; border-radius: 12px;
    padding: 0; cursor: pointer; overflow: hidden;
    transition: all .3s;
    background: linear-gradient(135deg, #0891b2, #06b6d4);
    box-shadow: 0 6px 20px rgba(6,182,212,.2);
}
.btn-primary:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 10px 28px rgba(6,182,212,.3); }
.btn-primary:active:not(:disabled) { transform: translateY(0); }
.btn-primary:disabled { opacity: .5; cursor: not-allowed; background: #334155; box-shadow: none; }
.btn-primary:focus-visible { outline: 2px solid #22d3ee; outline-offset: 2px; }

.btn-bg {
    position: absolute; inset: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0), rgba(255,255,255,.08));
    transition: opacity .3s;
}
.btn-primary:hover:not(:disabled) .btn-bg { opacity: 0; }

.btn-inner {
    position: relative; display: flex; align-items: center; justify-content: center;
    gap: .5rem; padding: .8125rem 1.25rem;
    font-size: .875rem; font-weight: 800; color: #fff; letter-spacing: .01em;
}
.btn-inner svg { width: 18px; height: 18px; }

.spinner {
    width: 18px; height: 18px;
    border: 2.5px solid rgba(255,255,255,.25); border-top-color: #fff;
    border-radius: 50%; animation: spin .6s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ═══════════════════ ALERT ═══════════════════ */
.alert {
    display: flex; gap: .625rem; margin-top: 1rem;
    padding: .75rem .875rem;
    background: rgba(251,191,36,.035);
    border: 1px solid rgba(251,191,36,.06);
    border-radius: 10px;
}
.alert svg { width: 14px; height: 14px; margin-top: 1px; flex-shrink: 0; color: rgba(251,191,36,.45); }
.alert-text { font-size: .6875rem; font-weight: 600; color: rgba(251,191,36,.55); line-height: 1.55; }

/* ═══════════════════ HELP ═══════════════════ */
.help-box {
    margin-top: 1.25rem; text-align: center;
    padding: .875rem 1rem;
    background: rgba(255,255,255,.02);
    border: 1px solid rgba(255,255,255,.035);
    border-radius: 12px;
}
.help-title {
    font-size: .5625rem; font-weight: 800; color: #64748b;
    text-transform: uppercase; letter-spacing: .12em; margin-bottom: .25rem;
}
.help-text { font-size: .6875rem; color: #64748b; font-weight: 500; line-height: 1.5; }

/* ═══════════════════ FOOTER ═══════════════════ */
.footer {
    text-align: center; margin-top: 1.5rem;
    font-size: .5rem; font-weight: 700; color: #334155;
    text-transform: uppercase; letter-spacing: .2em;
}

/* ═══════════════════ RESPONSIVE ═══════════════════ */
@media (min-width: 420px) {
    .card { padding: 1.75rem 1.5rem; border-radius: 26px; }
    .digit-box { font-size: 1.25rem; border-radius: 11px; }
    .digits-row { gap: .4375rem; }
    .badge-ring { width: 76px; height: 76px; }
    .badge-icon { width: 34px; height: 34px; }
}

@media (min-width: 640px) {
    .page { padding: 1.5rem; }
    .card { padding: 2.25rem 2rem; border-radius: 30px; }
    .digit-box { font-size: 1.375rem; border-radius: 12px; }
    .digits-row { gap: .5rem; }
    .badge-ring { width: 84px; height: 84px; }
    .badge-icon { width: 38px; height: 38px; }
    .title { font-size: 1.6rem; }

    .info-grid { grid-template-columns: 1fr 1fr; }
    .info-item:nth-child(3) { grid-column: span 2; }
    .info-item svg { width: 20px; height: 20px; }
}

@media (min-width: 768px) {
    .container { max-width: 560px; }
    .card { padding: 2.5rem 2.25rem; border-radius: 32px; }
    .digit-box { font-size: 1.5rem; border-radius: 12px; }
    .digits-row { gap: .5625rem; }
    .badge-ring { width: 88px; height: 88px; }
    .badge-icon { width: 40px; height: 40px; }

    .info-grid { grid-template-columns: 1fr 1fr 1fr; }
    .info-item:nth-child(3) { grid-column: auto; }
    .info-item { padding: .75rem 1rem; }
}

@media (min-width: 1024px) {
    .container { max-width: 600px; }
    .card { padding: 3rem 2.75rem; border-radius: 36px; }
    .page { padding: 2rem; }
}
</style>
