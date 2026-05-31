<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    ArrowLeft, CheckCircle2, XCircle, AlertCircle, HelpCircle,
    FileQuestion, Type, AlignLeft, Brackets, Award
} from 'lucide-vue-next';
import dayjs from 'dayjs';
import 'dayjs/locale/id';
import { sanitize } from '@/sanitize';
dayjs.locale('id');

const props = defineProps({
    sesi: Object,
    peserta: Object,
    jawabanList: Array,
    maxScore: Number,
});

const tipeLabel = (tipe) => {
    const labels = { pg: 'PG', benar_salah: 'B/S', essay: 'Essay', menjodohkan: 'Menjodohkan' };
    return labels[tipe] || tipe;
};

const tipeIcon = (tipe) => {
    const icons = { pg: FileQuestion, benar_salah: Type, essay: AlignLeft, menjodohkan: Brackets };
    return icons[tipe] || HelpCircle;
};

const tipeColor = (tipe) => {
    const colors = {
        pg: 'bg-blue-50 text-blue-700 border-blue-200',
        benar_salah: 'bg-purple-50 text-purple-700 border-purple-200',
        essay: 'bg-amber-50 text-amber-700 border-amber-200',
        menjodohkan: 'bg-rose-50 text-rose-700 border-rose-200',
    };
    return colors[tipe] || 'bg-slate-50 text-slate-700 border-slate-200';
};

const statusColor = (status) => {
    const colors = {
        mengerjakan: 'bg-indigo-100 text-indigo-700 border-indigo-200',
        selesai: 'bg-emerald-100 text-emerald-700 border-emerald-200',
        belum_mulai: 'bg-slate-100 text-slate-700 border-slate-200',
        didiskualifikasi: 'bg-rose-100 text-rose-700 border-rose-200',
    };
    return colors[status] || 'bg-slate-100 text-slate-700 border-slate-200';
};

const formatDate = (date) => date ? dayjs(date).format('DD MMM YYYY HH:mm') : '-';

const jawabanDijawab = (item) => {
    if (item.tipe === 'menjodohkan') {
        return item.jawaban_menjodohkan && Object.keys(item.jawaban_menjodohkan).length > 0;
    }
    return item.jawaban !== null && item.jawaban !== undefined && item.jawaban !== '';
};

const isJawabanBenar = (item) => {
    if (item.tipe === 'essay') {
        return item.skor !== null && item.skor !== undefined;
    }
    return item.is_benar === true;
};

const isJawabanSalah = (item) => {
    if (item.tipe === 'essay') return false;
    return item.is_benar === false;
};
</script>

<template>
    <Head :title="`Detail Jawaban: ${peserta.siswa?.nama}`" />

    <AuthenticatedLayout>
        <div class="max-w-5xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex items-center gap-4">
                    <Link :href="route('admin.ujian.sesi.monitor', sesi.hashid)"
                        class="p-2 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">
                        <ArrowLeft class="w-5 h-5 text-slate-600" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                            Detail Jawaban Siswa
                        </h1>
                        <p class="text-slate-500 font-medium mt-1">
                            {{ sesi.nama_sesi }} &mdash; {{ sesi.paket_ujian?.mata_pelajaran?.nama }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Student Info Card -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
                <div class="flex flex-col sm:flex-row gap-6">
                    <div class="flex-1 space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 font-black text-xl">
                                {{ peserta.siswa?.nama?.charAt(0) || '?' }}
                            </div>
                            <div>
                                <h2 class="text-xl font-black text-slate-900">{{ peserta.siswa?.nama }}</h2>
                                <p class="text-sm font-medium text-slate-500">NISN: {{ peserta.siswa?.nisn || '-' }}</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-wider border"
                                :class="statusColor(peserta.status)">
                                {{ peserta.status.replace('_', ' ') }}
                            </span>
                            <span class="px-3 py-1 bg-slate-50 text-slate-700 rounded-lg text-xs font-bold border border-slate-200">
                                {{ peserta.siswa?.rombel?.nama || '-' }}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-6 sm:border-l sm:border-slate-200 sm:pl-6">
                        <div class="text-center">
                            <p class="text-3xl font-black"
                                :class="peserta.nilai_akhir >= (maxScore * 0.7) ? 'text-emerald-600' : 'text-rose-600'">
                                {{ peserta.nilai_akhir ?? 0 }}
                            </p>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Nilai Akhir</p>
                        </div>
                        <div class="text-center">
                            <p class="text-3xl font-black text-slate-900">{{ maxScore }}</p>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Maksimal</p>
                        </div>
                        <div class="text-center">
                            <p class="text-3xl font-black text-slate-900">{{ jawabanList.length }}</p>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Total Soal</p>
                        </div>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-slate-100 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                    <div>
                        <span class="text-slate-400 font-semibold text-xs uppercase tracking-wider">Mulai</span>
                        <p class="font-medium text-slate-700">{{ formatDate(peserta.mulai_at) }}</p>
                    </div>
                    <div>
                        <span class="text-slate-400 font-semibold text-xs uppercase tracking-wider">Selesai</span>
                        <p class="font-medium text-slate-700">{{ formatDate(peserta.selesai_at) }}</p>
                    </div>
                    <div>
                        <span class="text-slate-400 font-semibold text-xs uppercase tracking-wider">IP Address</span>
                        <p class="font-medium text-slate-700">{{ peserta.ip_address || '-' }}</p>
                    </div>
                    <div>
                        <span class="text-slate-400 font-semibold text-xs uppercase tracking-wider">Browser</span>
                        <p class="font-medium text-slate-700 truncate" :title="peserta.browser">{{ peserta.browser || '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Summary Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Terjawab</p>
                    <p class="text-2xl font-black text-indigo-600">
                        {{ jawabanList.filter(jawabanDijawab).length }}
                        <span class="text-sm font-medium text-slate-400">/ {{ jawabanList.length }}</span>
                    </p>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Benar</p>
                    <p class="text-2xl font-black text-emerald-600">
                        {{ jawabanList.filter(isJawabanBenar).length }}
                    </p>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Salah</p>
                    <p class="text-2xl font-black text-rose-600">
                        {{ jawabanList.filter(isJawabanSalah).length }}
                    </p>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Tidak Dijawab</p>
                    <p class="text-2xl font-black text-slate-400">
                        {{ jawabanList.filter(i => !jawabanDijawab(i)).length }}
                    </p>
                </div>
            </div>

            <!-- Questions List -->
            <div class="space-y-4">
                <div v-for="(item, index) in jawabanList" :key="item.soal_id"
                    class="bg-white rounded-3xl border shadow-sm overflow-hidden"
                    :class="isJawabanBenar(item) ? 'border-emerald-200' : isJawabanSalah(item) ? 'border-rose-200' : 'border-slate-200'">

                    <!-- Question Header -->
                    <div class="p-5 flex items-start justify-between gap-4 border-b border-slate-100"
                        :class="isJawabanBenar(item) ? 'bg-emerald-50/30' : isJawabanSalah(item) ? 'bg-rose-50/30' : 'bg-slate-50/30'">
                        <div class="flex items-start gap-3 flex-1 min-w-0">
                            <span class="flex-shrink-0 w-8 h-8 rounded-xl flex items-center justify-center text-sm font-black"
                                :class="isJawabanBenar(item) ? 'bg-emerald-100 text-emerald-700' : isJawabanSalah(item) ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-600'">
                                {{ item.nomor }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1.5">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider border"
                                        :class="tipeColor(item.tipe)">
                                        <component :is="tipeIcon(item.tipe)" class="w-3 h-3" />
                                        {{ tipeLabel(item.tipe) }}
                                    </span>
                                    <span class="text-xs font-bold text-slate-400">
                                        Bobot: {{ item.bobot }}
                                    </span>
                                    <span v-if="item.is_ragu"
                                        class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-50 text-amber-700 border border-amber-200 rounded-md text-[10px] font-bold">
                                        <AlertCircle class="w-3 h-3" /> Ragu
                                    </span>
                                </div>
                                <div class="text-sm font-semibold text-slate-900 leading-relaxed prose prose-sm max-w-none"
                                    v-html="sanitize(item.pertanyaan)">
                                </div>
                                <img v-if="item.gambar_url" :src="item.gambar_url"
                                    class="mt-3 rounded-xl border border-slate-200 max-h-64 object-contain" />
                            </div>
                        </div>
                        <div class="flex-shrink-0 text-right">
                            <template v-if="item.tipe === 'essay'">
                                <span v-if="item.skor !== null && item.skor !== undefined"
                                    class="inline-flex items-center px-2.5 py-1 rounded-lg text-sm font-black bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    {{ item.skor }}
                                </span>
                                <span v-else
                                    class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    Belum Dinilai
                                </span>
                            </template>
                            <template v-else>
                                <span v-if="isJawabanBenar(item)"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-sm font-black bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <CheckCircle2 class="w-4 h-4" /> {{ item.nilai ?? 0 }}
                                </span>
                                <span v-else-if="isJawabanSalah(item)"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-sm font-black bg-rose-50 text-rose-700 border border-rose-200">
                                    <XCircle class="w-4 h-4" /> {{ item.nilai ?? 0 }}
                                </span>
                                <span v-else
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-50 text-slate-400 border border-slate-200">
                                    {{ jawabanDijawab(item) ? '-' : 'Kosong' }}
                                </span>
                            </template>
                        </div>
                    </div>

                    <!-- Question Body -->
                    <div class="p-5 space-y-4">
                        <!-- PG: Pilihan Jawaban -->
                        <div v-if="item.tipe === 'pg' && item.pilihan_jawaban" class="space-y-2">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Pilihan Jawaban</p>
                            <div v-for="p in item.pilihan_jawaban" :key="p.kode"
                                class="flex items-center gap-3 px-4 py-2.5 rounded-xl border text-sm font-medium transition-colors"
                                :class="[
                                    p.is_benar && p.kode === item.jawaban ? 'bg-emerald-50 border-emerald-300 text-emerald-800' :
                                    p.is_benar ? 'bg-emerald-50/50 border-emerald-200 text-emerald-700' :
                                    p.kode === item.jawaban ? 'bg-rose-50 border-rose-300 text-rose-800' :
                                    'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'
                                ]">
                                <span class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-black border"
                                    :class="[
                                        p.is_benar && p.kode === item.jawaban ? 'bg-emerald-200 border-emerald-300 text-emerald-800' :
                                        p.is_benar ? 'bg-emerald-100 border-emerald-200 text-emerald-700' :
                                        p.kode === item.jawaban ? 'bg-rose-200 border-rose-300 text-rose-800' :
                                        'bg-slate-50 border-slate-200 text-slate-500'
                                    ]">
                                    {{ p.kode }}
                                </span>
                                <span class="flex-1">{{ p.teks }}</span>
                                <CheckCircle2 v-if="p.is_benar" class="w-4 h-4 text-emerald-500 flex-shrink-0" />
                                <XCircle v-else-if="p.kode === item.jawaban" class="w-4 h-4 text-rose-500 flex-shrink-0" />
                            </div>
                        </div>

                        <!-- Benar Salah -->
                        <div v-if="item.tipe === 'benar_salah'" class="space-y-2">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Jawaban</p>
                            <div class="flex gap-3">
                                <div class="flex-1 px-4 py-3 rounded-xl border text-sm font-bold text-center"
                                    :class="item.jawaban === 'benar'
                                        ? (item.is_benar ? 'bg-emerald-50 border-emerald-300 text-emerald-700' : 'bg-rose-50 border-rose-300 text-rose-700')
                                        : 'bg-white border-slate-200 text-slate-400'">
                                    {{ item.jawaban === 'benar' ? '✓ Benar (jawaban siswa)' : 'Benar' }}
                                </div>
                                <div class="flex-1 px-4 py-3 rounded-xl border text-sm font-bold text-center"
                                    :class="item.jawaban === 'salah'
                                        ? (item.is_benar ? 'bg-emerald-50 border-emerald-300 text-emerald-700' : 'bg-rose-50 border-rose-300 text-rose-700')
                                        : 'bg-white border-slate-200 text-slate-400'">
                                    {{ item.jawaban === 'salah' ? '✗ Salah (jawaban siswa)' : 'Salah' }}
                                </div>
                            </div>
                            <div class="flex items-center gap-2 px-4 py-2 bg-slate-50 rounded-xl border border-slate-200 text-sm">
                                <Award class="w-4 h-4 text-indigo-500" />
                                <span class="font-semibold text-slate-700">Kunci Jawaban:</span>
                                <span class="font-black text-indigo-600 uppercase">{{ item.kunci_jawaban }}</span>
                            </div>
                        </div>

                        <!-- Essay -->
                        <div v-if="item.tipe === 'essay'" class="space-y-3">
                            <div>
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Jawaban Siswa</p>
                                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 text-sm font-medium text-slate-800 leading-relaxed whitespace-pre-wrap">
                                    {{ item.jawaban || '(tidak dijawab)' }}
                                </div>
                            </div>
                            <div v-if="item.catatan_guru" class="px-4 py-3 bg-indigo-50 rounded-xl border border-indigo-200 text-sm">
                                <span class="font-bold text-indigo-700">Catatan Guru:</span>
                                <p class="text-indigo-600 mt-1">{{ item.catatan_guru }}</p>
                            </div>
                        </div>

                        <!-- Menjodohkan -->
                        <div v-if="item.tipe === 'menjodohkan' && item.pasangan_menjodohkan" class="space-y-2">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Pasangan Menjodohkan</p>
                            <div v-for="p in item.pasangan_menjodohkan" :key="p.kiri"
                                class="flex items-center gap-3 px-4 py-3 rounded-xl border text-sm"
                                :class="item.jawaban_menjodohkan && item.jawaban_menjodohkan[p.kiri] === p.kanan
                                    ? 'bg-emerald-50 border-emerald-200'
                                    : 'bg-white border-slate-200'">
                                <div class="flex-1 px-3 py-2 bg-white rounded-lg border border-slate-200 font-medium text-slate-800">
                                    {{ p.kiri }}
                                </div>
                                <div class="text-slate-300 font-bold">
                                    <template v-if="item.jawaban_menjodohkan && item.jawaban_menjodohkan[p.kiri]">
                                        {{ item.jawaban_menjodohkan[p.kiri] }}
                                    </template>
                                    <span v-else class="text-slate-300">-</span>
                                </div>
                                <div class="flex-shrink-0">
                                    <CheckCircle2 v-if="item.jawaban_menjodohkan && item.jawaban_menjodohkan[p.kiri] === p.kanan"
                                        class="w-5 h-5 text-emerald-500" />
                                    <XCircle v-else-if="item.jawaban_menjodohkan && item.jawaban_menjodohkan[p.kiri] !== p.kanan"
                                        class="w-5 h-5 text-rose-500" />
                                    <span v-else class="w-5 h-5 inline-block text-slate-300 text-xs font-bold">?</span>
                                </div>
                                <div class="flex-1 px-3 py-2 bg-indigo-50 rounded-lg border border-indigo-200 font-medium text-indigo-700 text-right">
                                    {{ p.kanan }}
                                </div>
                            </div>
                        </div>

                        <!-- Kunci Jawaban (for non-essay) -->
                        <div v-if="item.tipe !== 'essay' && item.tipe !== 'benar_salah' && item.kunci_jawaban"
                            class="flex items-center gap-2 px-4 py-2.5 bg-slate-50 rounded-xl border border-slate-200 text-sm">
                            <Award class="w-4 h-4 text-indigo-500" />
                            <span class="font-semibold text-slate-700">Kunci Jawaban:</span>
                            <span class="font-black text-indigo-600">{{ item.kunci_jawaban }}</span>
                        </div>

                        <!-- Pembahasan -->
                        <div v-if="item.pembahasan"
                            class="px-4 py-3 bg-blue-50 rounded-xl border border-blue-200 text-sm">
                            <span class="font-bold text-blue-700">Pembahasan:</span>
                            <div class="text-blue-600 mt-1 leading-relaxed" v-html="sanitize(item.pembahasan)"></div>
                        </div>

                        <!-- Meta Info -->
                        <div v-if="jawabanDijawab(item)" class="flex flex-wrap gap-4 text-xs text-slate-400 font-medium pt-2 border-t border-slate-100">
                            <span v-if="item.dijawab_at">Dijawab: {{ formatDate(item.dijawab_at) }}</span>
                            <span v-if="item.durasi_detik">Durasi: {{ item.durasi_detik }} detik</span>
                        </div>
                    </div>
                </div>

                <div v-if="jawabanList.length === 0"
                    class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8 text-center text-slate-500 font-medium">
                    Belum ada soal dalam paket ujian ini.
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
