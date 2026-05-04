<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const props = defineProps({
    settings: Object,
    siswa: Object
});

// Confetti particle system
const confettiItems = ref([]);
const showContent = ref(false);

const isLulus = props.siswa?.status_lulus === 'lulus';

const colors = isLulus ? ['#FFD700', '#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7'] : ['#a0aec0', '#cbd5e1'];
const shapes = ['●', '■', '▲', '◆', '★'];

onMounted(() => {
    // Generate confetti if lulus
    if (isLulus) {
        confettiItems.value = Array.from({ length: 80 }, (_, i) => ({
            id: i,
            left: Math.random() * 100,
            delay: Math.random() * 5,
            duration: 4 + Math.random() * 4,
            color: colors[Math.floor(Math.random() * colors.length)],
            shape: shapes[Math.floor(Math.random() * shapes.length)],
            size: 0.6 + Math.random() * 1.2,
            rotate: Math.random() * 360,
        }));
    }

    setTimeout(() => {
        showContent.value = true;
    }, 300);
});

const namaSekolah = props.settings?.nama_sekolah || 'SMA Negeri 2 Perbaungan';
const tahunAjaran = '2025/2026';
</script>

<template>
    <Head>
        <title>Pengumuman Kelulusan {{ tahunAjaran }} - {{ namaSekolah }}</title>
        <meta name="description" :content="`Pengumuman resmi kelulusan siswa ${namaSekolah} Tahun Pelajaran ${tahunAjaran}`" />
    </Head>

    <div class="kelulusan-page">
        <!-- Confetti Layer -->
        <div class="confetti-container" aria-hidden="true">
            <span
                v-for="item in confettiItems"
                :key="item.id"
                class="confetti-piece"
                :style="{
                    left: item.left + '%',
                    animationDelay: item.delay + 's',
                    animationDuration: item.duration + 's',
                    color: item.color,
                    fontSize: item.size + 'rem',
                    '--rotate': item.rotate + 'deg',
                }"
            >{{ item.shape }}</span>
        </div>

        <!-- Fireworks orbs (bg decoration) -->
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>

        <!-- Main Content -->
        <div class="content-wrapper" :class="{ 'content-visible': showContent }">

            <!-- Back to home -->
            <Link href="/" class="back-link">
                ← Kembali ke Beranda
            </Link>

            <!-- Logo / Badge -->
            <div class="badge-wrapper">
                <div class="badge-ring">
                    <div class="badge-inner">
                        <span class="badge-icon">🎓</span>
                    </div>
                </div>
                <div class="year-badge">{{ tahunAjaran }}</div>
            </div>

            <!-- Headline -->
            <div class="headline-section">
                <p class="pre-title">📣 Halo, {{ siswa?.nama }}</p>
                <h1 class="main-title">
                    <template v-if="isLulus">
                        SELAMAT!<br>
                        <span class="title-highlight">ANDA LULUS</span>
                    </template>
                    <template v-else-if="siswa?.status_lulus === 'ditunda'">
                        PENGUMUMAN<br>
                        <span class="text-yellow-400">DITUNDA</span>
                    </template>
                    <template v-else>
                        MOHON MAAF<br>
                        <span class="text-red-400">ANDA BELUM LULUS</span>
                    </template>
                </h1>
                <div class="school-name">{{ namaSekolah }}</div>
            </div>

            <!-- Decorative Stars -->
            <div class="stars-row" aria-hidden="true" v-if="isLulus">
                <span>⭐</span><span>🌟</span><span>✨</span><span>🌟</span><span>⭐</span>
            </div>

            <!-- Announcement Box -->
            <div class="announcement-card" :class="!isLulus ? 'border-red-400/30 bg-red-900/10' : ''">
                <div class="card-header" :class="!isLulus ? 'bg-gradient-to-r from-red-600 to-rose-500 text-white' : ''">
                    <span class="card-icon">📋</span>
                    <span>Pengumuman Kelulusan</span>
                </div>
                <div class="card-body">
                    <p class="card-text">
                        Berdasarkan hasil rapat dewan guru <strong>{{ namaSekolah }}</strong>, peserta didik atas nama:
                        <br><br>
                        <strong class="text-xl text-white">{{ siswa?.nama }}</strong><br>
                        <span class="text-sm opacity-70">NISN: {{ siswa?.nisn }}</span>
                        <br><br>
                        Dinyatakan
                        <strong v-if="isLulus" class="text-success text-2xl">LULUS</strong>
                        <strong v-else-if="siswa?.status_lulus === 'ditunda'" class="text-yellow-400 text-2xl">DITUNDA</strong>
                        <strong v-else class="text-red-400 text-2xl">TIDAK LULUS</strong>
                    </p>
                    
                    <div class="bg-black/20 rounded-xl p-4 mt-4 text-left border border-white/10">
                        <p class="text-sm text-white/80 font-semibold mb-1">Catatan Tambahan:</p>
                        <p class="text-sm text-white/60 italic">{{ siswa?.keterangan || '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Info Grid -->
            <div class="info-grid">
                <div class="info-card">
                    <div class="info-icon">📅</div>
                    <div class="info-label">Tanggal Pengumuman</div>
                    <div class="info-value">{{ new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}</div>
                </div>
                <div class="info-card">
                    <div class="info-icon">🎓</div>
                    <div class="info-label">Tahun Pelajaran</div>
                    <div class="info-value">{{ tahunAjaran }}</div>
                </div>
                <div class="info-card">
                    <div class="info-icon">✅</div>
                    <div class="info-label">Status Kelulusan</div>
                    <div class="info-value" :class="isLulus ? 'success-text' : (siswa?.status_lulus === 'ditunda' ? 'text-yellow-400' : 'text-red-400')">
                        {{ isLulus ? 'LULUS' : (siswa?.status_lulus === 'ditunda' ? 'DITUNDA' : 'TIDAK LULUS') }}
                    </div>
                </div>
            </div>

            <!-- Important Notes -->
            <div class="notes-card">
                <h3 class="notes-title">📌 Informasi Penting</h3>
                <ul class="notes-list">
                    <li>Ijazah dan SKHUN dapat diambil setelah pengumuman resmi dari sekolah.</li>
                    <li>Harap membawa kartu identitas saat pengambilan dokumen kelulusan.</li>
                    <li>Untuk informasi lebih lanjut, hubungi Tata Usaha sekolah.</li>
                    <li>Selamat menempuh jenjang pendidikan atau karier selanjutnya!</li>
                </ul>
            </div>

            <!-- Contact info -->
            <div class="contact-bar" v-if="settings?.telepon || settings?.email">
                <span v-if="settings?.telepon">📞 {{ settings.telepon }}</span>
                <span class="divider" v-if="settings?.telepon && settings?.email">|</span>
                <span v-if="settings?.email">✉️ {{ settings.email }}</span>
            </div>

            <!-- Footer Message -->
            <div class="footer-message">
                <p class="congrats-big">🎉 Selamat & Sukses! 🎉</p>
                <p class="congrats-sub">Semoga masa depan kalian penuh dengan cahaya dan keberhasilan.<br>Kami bangga dengan kalian semua!</p>
                <div class="footer-school">— {{ namaSekolah }} —</div>
            </div>

            <!-- Action buttons -->
            <div class="action-buttons">
                <Link href="/" class="btn-home">🏠 Kembali ke Beranda</Link>
                <Link href="/kontak" class="btn-contact">📞 Hubungi Sekolah</Link>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* ===== BASE ===== */
.kelulusan-page {
    min-height: 100vh;
    background: linear-gradient(135deg, #0f0c29 0%, #302b63 40%, #24243e 100%);
    position: relative;
    overflow: hidden;
    font-family: 'Inter', 'Segoe UI', sans-serif;
    padding: 2rem 1rem 4rem;
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

/* ===== CONFETTI ===== */
.confetti-container {
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 1;
    overflow: hidden;
}
.confetti-piece {
    position: absolute;
    top: -2rem;
    animation: confettiFall linear infinite;
    opacity: 0.85;
    display: inline-block;
}
@keyframes confettiFall {
    0%   { transform: translateY(-20px) rotate(0deg); opacity: 1; }
    100% { transform: translateY(110vh) rotate(var(--rotate, 360deg)); opacity: 0; }
}

/* ===== CONTENT WRAPPER ===== */
.content-wrapper {
    position: relative;
    z-index: 10;
    max-width: 780px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2rem;
    opacity: 0;
    transform: translateY(30px);
    transition: all 0.8s cubic-bezier(0.22, 1, 0.36, 1);
}
.content-visible {
    opacity: 1;
    transform: translateY(0);
}

/* ===== BACK LINK ===== */
.back-link {
    align-self: flex-start;
    color: rgba(255,255,255,0.6);
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 600;
    letter-spacing: 0.03em;
    transition: color 0.2s;
}
.back-link:hover { color: #FFD700; }

/* ===== BADGE ===== */
.badge-wrapper {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
    margin-top: 1rem;
}
.badge-ring {
    width: 120px; height: 120px;
    border-radius: 50%;
    background: linear-gradient(135deg, #FFD700, #FF8C00, #FFD700);
    padding: 4px;
    animation: spinGlow 4s linear infinite;
    box-shadow: 0 0 40px rgba(255,215,0,0.5);
}
.badge-inner {
    width: 100%; height: 100%;
    border-radius: 50%;
    background: linear-gradient(135deg, #1a1a2e, #16213e);
    display: flex; align-items: center; justify-content: center;
}
.badge-icon { font-size: 3.5rem; }
.year-badge {
    background: linear-gradient(135deg, #FFD700, #FF8C00);
    color: #1a1a2e;
    font-weight: 900;
    font-size: 0.9rem;
    padding: 0.35rem 1.2rem;
    border-radius: 999px;
    letter-spacing: 0.1em;
}
@keyframes spinGlow {
    0% { box-shadow: 0 0 20px rgba(255,215,0,0.4), 0 0 60px rgba(255,165,0,0.2); }
    50% { box-shadow: 0 0 40px rgba(255,215,0,0.8), 0 0 100px rgba(255,165,0,0.4); }
    100% { box-shadow: 0 0 20px rgba(255,215,0,0.4), 0 0 60px rgba(255,165,0,0.2); }
}

/* ===== HEADLINE ===== */
.headline-section { text-align: center; }
.pre-title {
    color: rgba(255,255,255,0.7);
    font-size: 0.9rem;
    font-weight: 700;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    margin-bottom: 0.75rem;
}
.main-title {
    font-size: clamp(3rem, 10vw, 6rem);
    font-weight: 900;
    line-height: 1;
    color: white;
    letter-spacing: -0.02em;
    text-shadow: 0 0 40px rgba(255,255,255,0.2);
}
.title-highlight {
    background: linear-gradient(135deg, #FFD700, #FF8C00, #FFD700);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: shimmer 3s ease-in-out infinite;
    background-size: 200% 100%;
}
@keyframes shimmer {
    0%,100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}
.school-name {
    margin-top: 0.75rem;
    color: rgba(255,255,255,0.6);
    font-size: 1rem;
    font-weight: 600;
    letter-spacing: 0.05em;
}

/* ===== STARS ROW ===== */
.stars-row {
    display: flex; gap: 0.75rem; font-size: 1.5rem;
    animation: pulse 2s ease-in-out infinite;
}
@keyframes pulse { 0%,100% { transform: scale(1); } 50% { transform: scale(1.1); } }

/* ===== ANNOUNCEMENT CARD ===== */
.announcement-card {
    width: 100%;
    background: rgba(255,255,255,0.07);
    border: 1px solid rgba(255,215,0,0.3);
    border-radius: 24px;
    overflow: hidden;
    backdrop-filter: blur(20px);
    box-shadow: 0 20px 60px rgba(0,0,0,0.3), inset 0 1px 0 rgba(255,255,255,0.1);
}
.card-header {
    background: linear-gradient(135deg, #FFD700, #FF8C00);
    color: #1a1a2e;
    font-weight: 900;
    font-size: 1rem;
    padding: 1rem 1.5rem;
    display: flex; align-items: center; gap: 0.75rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
}
.card-icon { font-size: 1.2rem; }
.card-body { padding: 2rem; text-align: center; }
.card-text {
    color: rgba(255,255,255,0.85);
    font-size: 1.05rem;
    line-height: 1.8;
    margin-bottom: 1.5rem;
}
.text-success { color: #4ECDC4; }
.percentage-display {
    display: flex;
    align-items: flex-start;
    justify-content: center;
    gap: 0.25rem;
    margin: 1rem 0 1.5rem;
}
.percentage-number {
    font-size: clamp(5rem, 15vw, 8rem);
    font-weight: 900;
    line-height: 1;
    background: linear-gradient(135deg, #FFD700, #FF8C00);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.percentage-symbol {
    font-size: 3rem;
    font-weight: 900;
    color: #FFD700;
    margin-top: 1rem;
}
.card-subtext {
    color: rgba(255,255,255,0.5);
    font-size: 0.9rem;
    font-style: italic;
    line-height: 1.6;
}

/* ===== INFO GRID ===== */
.info-grid {
    width: 100%;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
}
.info-card {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 16px;
    padding: 1.25rem 1rem;
    text-align: center;
    backdrop-filter: blur(10px);
    transition: transform 0.2s, border-color 0.2s;
}
.info-card:hover {
    transform: translateY(-3px);
    border-color: rgba(255,215,0,0.4);
}
.info-icon { font-size: 1.5rem; margin-bottom: 0.5rem; }
.info-label {
    color: rgba(255,255,255,0.5);
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    margin-bottom: 0.4rem;
}
.info-value {
    color: white;
    font-weight: 800;
    font-size: 0.9rem;
}
.success-text { color: #4ECDC4 !important; }

/* ===== NOTES CARD ===== */
.notes-card {
    width: 100%;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 20px;
    padding: 1.75rem;
    backdrop-filter: blur(10px);
}
.notes-title {
    color: #FFD700;
    font-weight: 800;
    font-size: 1rem;
    margin-bottom: 1rem;
}
.notes-list {
    list-style: none;
    padding: 0; margin: 0;
    display: flex; flex-direction: column; gap: 0.75rem;
}
.notes-list li {
    color: rgba(255,255,255,0.75);
    font-size: 0.9rem;
    line-height: 1.6;
    padding-left: 1.5rem;
    position: relative;
}
.notes-list li::before {
    content: '✓';
    position: absolute; left: 0;
    color: #4ECDC4;
    font-weight: 900;
}

/* ===== CONTACT BAR ===== */
.contact-bar {
    display: flex; align-items: center; gap: 1rem;
    color: rgba(255,255,255,0.6);
    font-size: 0.9rem;
    font-weight: 600;
}
.divider { opacity: 0.4; }

/* ===== FOOTER MESSAGE ===== */
.footer-message { text-align: center; }
.congrats-big {
    font-size: clamp(1.5rem, 5vw, 2.5rem);
    font-weight: 900;
    color: white;
    animation: pulse 2s ease-in-out infinite;
    margin-bottom: 0.75rem;
}
.congrats-sub {
    color: rgba(255,255,255,0.65);
    font-size: 1rem;
    line-height: 1.8;
    margin-bottom: 1rem;
}
.footer-school {
    color: rgba(255,215,0,0.7);
    font-weight: 700;
    font-size: 0.9rem;
    letter-spacing: 0.1em;
}

/* ===== ACTION BUTTONS ===== */
.action-buttons {
    display: flex; gap: 1rem; flex-wrap: wrap; justify-content: center;
    padding-bottom: 2rem;
}
.btn-home, .btn-contact {
    display: inline-flex; align-items: center; gap: 0.5rem;
    padding: 0.9rem 2rem;
    border-radius: 999px;
    font-weight: 800;
    font-size: 0.95rem;
    text-decoration: none;
    transition: all 0.3s;
    letter-spacing: 0.02em;
}
.btn-home {
    background: linear-gradient(135deg, #FFD700, #FF8C00);
    color: #1a1a2e;
    box-shadow: 0 8px 30px rgba(255,215,0,0.4);
}
.btn-home:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 40px rgba(255,215,0,0.6);
}
.btn-contact {
    background: rgba(255,255,255,0.1);
    color: white;
    border: 1px solid rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
}
.btn-contact:hover {
    background: rgba(255,255,255,0.2);
    transform: translateY(-3px);
}

/* ===== RESPONSIVE ===== */
@media (max-width: 600px) {
    .info-grid { grid-template-columns: 1fr; }
    .action-buttons { flex-direction: column; width: 100%; }
    .btn-home, .btn-contact { justify-content: center; }
    .back-link { font-size: 0.8rem; }
}
</style>
