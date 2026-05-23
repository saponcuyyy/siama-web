<script setup>
import { ref, computed, onMounted, nextTick, watch } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    Database, ArrowLeft, Plus, Trash2, Check, X, FileQuestion, Hash, FileText, AlertTriangle
} from 'lucide-vue-next';

const props = defineProps({
    bankSoal: Object,
});

const showAddModal = ref(false);
const showImportModal = ref(false);
const showImportWordModal = ref(false);

const importForm = useForm({
    bank_soal_id: props.bankSoal.id,
    file: null,
});

const importWordForm = useForm({
    bank_soal_id: props.bankSoal.id,
    file: null,
});

const triggerMathJax = () => {
    nextTick(() => {
        if (window.MathJax && typeof window.MathJax.typesetPromise === 'function') {
            window.MathJax.typesetPromise();
        }
    });
};

onMounted(() => {
    triggerMathJax();
});

watch(() => props.bankSoal.soal, () => {
    triggerMathJax();
}, { deep: true });

const submitImport = () => {
    importForm.post(route('admin.ujian.bank-soal.import-excel', props.bankSoal.hashid), {
        onSuccess: () => {
            showImportModal.value = false;
            importForm.reset();
            triggerMathJax();
        }
    });
};

const submitImportWord = () => {
    importWordForm.post(route('admin.ujian.bank-soal.import-word', props.bankSoal.hashid), {
        onSuccess: () => {
            showImportWordModal.value = false;
            importWordForm.reset();
            triggerMathJax();
        }
    });
};

const form = useForm({
    bank_soal_id: props.bankSoal.id,
    tipe: 'pg',
    pertanyaan: '',
    bobot: 1,
    kunci_jawaban: '',
    // Pilihan Ganda
    pilihan: [
        { kode: 'A', teks: '', is_kunci: false },
        { kode: 'B', teks: '', is_kunci: false },
        { kode: 'C', teks: '', is_kunci: false },
        { kode: 'D', teks: '', is_kunci: false },
        { kode: 'E', teks: '', is_kunci: false },
    ],
    // Menjodohkan
    pasangan: [
        { kiri: '', kanan: '' },
        { kiri: '', kanan: '' },
    ]
});

const submitSoal = () => {
    form.post(route('admin.ujian.soal.store'), {
        onSuccess: () => {
            showAddModal.value = false;
            form.reset('pertanyaan', 'kunci_jawaban', 'pilihan', 'pasangan');
            // reset pilihan
            form.pilihan = [
                { kode: 'A', teks: '', is_kunci: false },
                { kode: 'B', teks: '', is_kunci: false },
                { kode: 'C', teks: '', is_kunci: false },
                { kode: 'D', teks: '', is_kunci: false },
                { kode: 'E', teks: '', is_kunci: false },
            ];
            form.pasangan = [
                { kiri: '', kanan: '' },
                { kiri: '', kanan: '' },
            ];
        }
    });
};

// ─── Delete Soal Modal ───────────────────────
const showDeleteSoalModal = ref(false);
const deleteSoalTarget = ref(null);
const isDeletingSoal = ref(false);

const hapusSoal = (soal) => {
    deleteSoalTarget.value = soal;
    showDeleteSoalModal.value = true;
};

const confirmHapusSoal = () => {
    isDeletingSoal.value = true;
    router.delete(route('admin.ujian.soal.destroy', deleteSoalTarget.value.hashid), {
        onSuccess: () => { showDeleteSoalModal.value = false; },
        onFinish: () => { isDeletingSoal.value = false; }
    });
};

const setKunciPG = (index) => {
    form.pilihan.forEach((p, i) => {
        p.is_kunci = (i === index);
    });
};

const tambahPasangan = () => {
    form.pasangan.push({ kiri: '', kanan: '' });
};

const hapusPasangan = (index) => {
    form.pasangan.splice(index, 1);
};

// ─── Edit Bobot Inline ───────────────────────────
const editingBobotId = ref(null);
const editBobotValue = ref(1);

const startEditBobot = (soal) => {
    editingBobotId.value = soal.id;
    editBobotValue.value = soal.bobot;
};

const cancelEditBobot = () => {
    editingBobotId.value = null;
};

const saveBobot = (soal) => {
    if (editBobotValue.value <= 0) return;
    
    router.put(route('admin.ujian.soal.update-bobot', soal.hashid), {
        bobot: editBobotValue.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            editingBobotId.value = null;
        }
    });
};
</script>

<template>
    <Head :title="`Bank Soal: ${bankSoal.nama}`" />
    
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex items-center gap-4">
                    <Link :href="route('admin.ujian.bank-soal.index')" class="p-2 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">
                        <ArrowLeft class="w-5 h-5 text-slate-600" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                            {{ bankSoal.judul }}
                        </h1>
                        <p class="text-slate-500 font-medium mt-1">
                            {{ bankSoal.mata_pelajaran?.nama }} &bull; Kelas {{ bankSoal.tingkat }}
                        </p>
                    </div>
                </div>
                
                <div class="flex items-center gap-3">
                    <button @click="showImportWordModal = true" class="px-5 py-2.5 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-sm font-bold rounded-xl flex items-center gap-2 transition-colors shadow-sm">
                        <FileText class="w-5 h-5 text-indigo-600" /> Impor Word (.docx)
                    </button>
                    <button @click="showImportModal = true" class="px-5 py-2.5 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-sm font-bold rounded-xl flex items-center gap-2 transition-colors shadow-sm">
                        <FileText class="w-5 h-5 text-emerald-600" /> Impor Excel (.xlsx)
                    </button>
                    <button @click="showAddModal = true" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl flex items-center gap-2 transition-colors shadow-lg shadow-indigo-200">
                        <Plus class="w-5 h-5" /> Buat Soal
                    </button>
                </div>
            </div>

            <!-- Daftar Soal -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100">
                    <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <FileQuestion class="w-5 h-5 text-indigo-600" />
                        Daftar Soal ({{ bankSoal.soal.length }})
                    </h2>
                </div>

                <div class="p-6 space-y-6">
                    <div v-if="bankSoal.soal.length === 0" class="text-center py-12">
                        <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4 text-slate-400">
                            <Database class="w-8 h-8" />
                        </div>
                        <h3 class="text-slate-900 font-bold mb-1">Bank Soal Masih Kosong</h3>
                        <p class="text-slate-500 text-sm">Mulai tambahkan soal ke dalam bank soal ini.</p>
                    </div>

                    <div v-else class="space-y-6">
                        <div v-for="(soal, index) in bankSoal.soal" :key="soal.id" class="p-6 border border-slate-200 rounded-2xl hover:border-indigo-300 transition-colors group relative">
                            <!-- Aksi hapus -->
                            <button @click="hapusSoal(soal)" class="absolute top-4 right-4 p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-colors opacity-0 group-hover:opacity-100">
                                <Trash2 class="w-5 h-5" />
                            </button>

                            <div class="flex items-center gap-2 mb-4">
                                <div class="w-8 h-8 bg-indigo-50 text-indigo-700 font-black rounded-lg flex items-center justify-center shrink-0">
                                    {{ index + 1 }}
                                </div>
                                <span class="px-2.5 py-1 bg-slate-100 text-slate-600 text-xs font-bold uppercase tracking-wider rounded-md border border-slate-200">
                                    {{ soal.tipe.replace('_', ' ') }}
                                </span>
                                
                                <div v-if="editingBobotId === soal.id" class="flex items-center gap-1">
                                    <input type="number" v-model="editBobotValue" min="0.1" step="0.1" class="w-16 px-2 py-0.5 text-xs font-bold border border-indigo-300 rounded focus:ring-indigo-500 focus:border-indigo-500">
                                    <button @click="saveBobot(soal)" class="p-1 text-emerald-600 hover:bg-emerald-50 rounded">
                                        <Check class="w-3.5 h-3.5" />
                                    </button>
                                    <button @click="cancelEditBobot" class="p-1 text-rose-500 hover:bg-rose-50 rounded">
                                        <X class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                                <span v-else @click="startEditBobot(soal)" class="cursor-pointer px-2.5 py-1 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-xs font-bold uppercase tracking-wider rounded-md border border-indigo-200 transition-colors" title="Klik untuk mengubah bobot">
                                    Bobot: {{ soal.bobot }}
                                </span>
                            </div>

                            <div class="prose prose-sm max-w-none text-slate-800 font-medium mb-4" v-html="soal.pertanyaan"></div>

                            <!-- Opsi PG -->
                            <div v-if="soal.tipe === 'pg'" class="space-y-2 pl-10">
                                <div v-for="opsi in soal.pilihan_jawaban" :key="opsi.id" class="flex items-start gap-2">
                                    <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold shrink-0"
                                         :class="soal.kunci_jawaban === opsi.kode ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500'">
                                        {{ opsi.kode }}
                                    </div>
                                    <div class="text-sm text-slate-700 pt-0.5" v-html="opsi.teks"></div>
                                </div>
                            </div>

                            <!-- Menjodohkan -->
                            <div v-else-if="soal.tipe === 'menjodohkan'" class="pl-10">
                                <table class="w-full max-w-md text-sm text-left border-collapse border border-slate-200">
                                    <thead>
                                        <tr class="bg-slate-50">
                                            <th class="border border-slate-200 px-3 py-2 text-slate-500">Kiri (Premis)</th>
                                            <th class="border border-slate-200 px-3 py-2 text-slate-500">Kanan (Jawaban)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="pas in soal.pasangan_menjodohkan" :key="pas.id">
                                            <td class="border border-slate-200 px-3 py-2 font-medium">{{ pas.kiri }}</td>
                                            <td class="border border-slate-200 px-3 py-2 font-medium">{{ pas.kanan }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Benar Salah / Essay -->
                            <div v-else class="pl-10 text-sm font-bold text-emerald-600">
                                <template v-if="soal.tipe === 'benar_salah'">Kunci: {{ soal.kunci_jawaban }}</template>
                                <template v-else-if="soal.tipe === 'essay'">Kunci / Panduan: {{ soal.kunci_jawaban || 'Tidak ada panduan.' }}</template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Tambah Soal -->
        <div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white w-full max-w-4xl max-h-[90vh] flex flex-col rounded-3xl shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between p-6 border-b border-slate-100 shrink-0">
                    <h2 class="text-xl font-black text-slate-900">Buat Soal Baru</h2>
                    <button @click="showAddModal = false" class="p-2 text-slate-400 hover:text-slate-600 bg-slate-50 rounded-xl transition-colors">
                        <X class="w-5 h-5" />
                    </button>
                </div>
                
                <form @submit.prevent="submitSoal" class="flex flex-col h-full overflow-hidden">
                    <div class="flex-1 overflow-y-auto p-6 space-y-6">
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Tipe Soal</label>
                                <select v-model="form.tipe" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600 font-bold">
                                    <option value="pg">Pilihan Ganda</option>
                                    <option value="benar_salah">Benar / Salah</option>
                                    <option value="essay">Essay</option>
                                    <option value="menjodohkan">Menjodohkan</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Bobot Nilai</label>
                                <input type="number" min="1" step="0.5" v-model="form.bobot" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Pertanyaan</label>
                            <!-- Note: ideally use a rich text editor like Tiptap. For simplicity here, textarea -->
                            <textarea v-model="form.pertanyaan" required rows="4" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600" placeholder="Tuliskan soal..."></textarea>
                            <p class="text-xs text-slate-400 mt-1">Anda bisa menggunakan format HTML dasar.</p>
                        </div>

                        <!-- Editor Pilihan Ganda -->
                        <div v-if="form.tipe === 'pg'" class="space-y-4">
                            <h3 class="text-sm font-bold text-slate-900 border-b pb-2">Opsi Jawaban & Kunci</h3>
                            <div v-for="(opsi, index) in form.pilihan" :key="index" class="flex items-start gap-3">
                                <div class="pt-2">
                                    <input type="radio" name="kunci_pg" :checked="opsi.is_kunci" @change="setKunciPG(index)" required class="w-5 h-5 text-emerald-600 focus:ring-emerald-600 border-slate-300">
                                </div>
                                <div class="w-10 pt-1.5 font-black text-slate-500 text-lg">{{ opsi.kode }}.</div>
                                <div class="flex-1">
                                    <textarea v-model="opsi.teks" rows="2" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600" :placeholder="`Jawaban ${opsi.kode}...`"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Editor Benar Salah -->
                        <div v-else-if="form.tipe === 'benar_salah'">
                            <h3 class="text-sm font-bold text-slate-900 mb-3">Kunci Jawaban</h3>
                            <div class="flex gap-4">
                                <label class="flex-1 border-2 border-slate-200 rounded-xl p-4 flex items-center justify-center gap-2 cursor-pointer transition-colors"
                                       :class="form.kunci_jawaban === 'Benar' ? 'bg-emerald-50 border-emerald-500 text-emerald-700' : 'hover:border-emerald-300'">
                                    <input type="radio" value="Benar" v-model="form.kunci_jawaban" required class="hidden">
                                    <span class="font-black">BENAR</span>
                                </label>
                                <label class="flex-1 border-2 border-slate-200 rounded-xl p-4 flex items-center justify-center gap-2 cursor-pointer transition-colors"
                                       :class="form.kunci_jawaban === 'Salah' ? 'bg-rose-50 border-rose-500 text-rose-700' : 'hover:border-rose-300'">
                                    <input type="radio" value="Salah" v-model="form.kunci_jawaban" required class="hidden">
                                    <span class="font-black">SALAH</span>
                                </label>
                            </div>
                        </div>

                        <!-- Editor Essay -->
                        <div v-else-if="form.tipe === 'essay'">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Panduan Jawaban / Kunci (Opsional)</label>
                            <textarea v-model="form.kunci_jawaban" rows="3" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600" placeholder="Referensi jawaban yang diharapkan..."></textarea>
                        </div>

                        <!-- Editor Menjodohkan -->
                        <div v-else-if="form.tipe === 'menjodohkan'" class="space-y-4">
                            <h3 class="text-sm font-bold text-slate-900 border-b pb-2">Pasangan Jawaban</h3>
                            
                            <div v-for="(pas, index) in form.pasangan" :key="index" class="flex gap-4 items-start">
                                <div class="flex-1">
                                    <input type="text" v-model="pas.kiri" placeholder="Sebelah Kiri (Premis)" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600">
                                </div>
                                <div class="flex-1">
                                    <input type="text" v-model="pas.kanan" placeholder="Sebelah Kanan (Jawaban)" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600">
                                </div>
                                <button type="button" @click="hapusPasangan(index)" v-if="form.pasangan.length > 2" class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg mt-0.5">
                                    <Trash2 class="w-5 h-5" />
                                </button>
                            </div>
                            
                            <button type="button" @click="tambahPasangan" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold rounded-lg transition-colors">
                                + Tambah Pasangan
                            </button>
                        </div>
                    </div>

                    <div class="p-6 border-t border-slate-100 bg-white shrink-0 flex justify-end gap-3">
                        <button type="button" @click="showAddModal = false" class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                            Batal
                        </button>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors flex items-center gap-2">
                            <Check class="w-4 h-4" /> {{ form.processing ? 'Menyimpan...' : 'Simpan Soal' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Impor Soal Excel -->
        <div v-if="showImportModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between p-6 border-b border-slate-100 shrink-0">
                    <h2 class="text-xl font-black text-slate-900">Impor Soal dari Excel (.xlsx)</h2>
                    <button @click="showImportModal = false" class="p-2 text-slate-400 hover:text-slate-600 bg-slate-50 rounded-xl transition-colors">
                        <X class="w-5 h-5" />
                    </button>
                </div>
                
                <form @submit.prevent="submitImport" class="p-6 space-y-6">
                    <div class="bg-amber-50 border border-amber-100 rounded-2xl p-4 text-xs text-amber-800 space-y-2">
                        <p class="font-bold flex items-center gap-1.5">
                            <span class="w-2 h-2 bg-amber-500 rounded-full"></span> Penting untuk format dokumen:
                        </p>
                        <ul class="list-disc pl-4 space-y-1 mb-2">
                            <li>Gunakan template Excel yang disediakan.</li>
                            <li>Pastikan tipe soal dan tingkat kesulitan sesuai dengan pilihan di template.</li>
                        </ul>
                        <a :href="route('admin.ujian.soal.template.excel')" target="_blank" class="text-indigo-600 hover:underline font-bold inline-flex items-center mt-2">
                            <FileText class="w-4 h-4 mr-1" /> Download Template Excel
                        </a>
                    </div>
                    
                    <div v-if="$page.props.flash && $page.props.flash.import_errors && $page.props.flash.import_errors.length > 0" class="p-3 bg-rose-50 border border-rose-200 rounded-xl max-h-40 overflow-y-auto">
                        <p class="text-sm text-rose-600 font-bold mb-1">Error Validasi:</p>
                        <ul class="list-disc list-inside text-xs text-rose-600 space-y-1">
                            <li v-for="(err, i) in $page.props.flash.import_errors" :key="i">{{ err }}</li>
                        </ul>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Pilih File Excel (.xlsx/.xls)</label>
                        <input 
                            type="file" 
                            accept=".xlsx, .xls"
                            @input="importForm.file = $event.target.files[0]"
                            required 
                            class="w-full px-4 py-3 bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl focus:ring-indigo-600 focus:border-indigo-600 cursor-pointer"
                        />
                        <progress v-if="importForm.progress" :value="importForm.progress.percentage" max="100" class="w-full h-1.5 bg-indigo-100 rounded mt-3">
                            {{ importForm.progress.percentage }}%
                        </progress>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" @click="showImportModal = false" class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                            Batal
                        </button>
                        <button type="submit" :disabled="importForm.processing" class="px-5 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors flex items-center gap-2">
                            <Check class="w-4 h-4" /> {{ importForm.processing ? 'Mengunggah...' : 'Unggah & Impor' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Impor Soal Word -->
        <div v-if="showImportWordModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between p-6 border-b border-slate-100 shrink-0">
                    <h2 class="text-xl font-black text-slate-900">Impor Soal dari Word (.docx)</h2>
                    <button @click="showImportWordModal = false" class="p-2 text-slate-400 hover:text-slate-600 bg-slate-50 rounded-xl transition-colors">
                        <X class="w-5 h-5" />
                    </button>
                </div>
                
                <form @submit.prevent="submitImportWord" class="p-6 space-y-6">
                    <div class="bg-amber-50 border border-amber-100 rounded-2xl p-4 text-xs text-amber-800 space-y-2">
                        <p class="font-bold flex items-center gap-1.5">
                            <span class="w-2 h-2 bg-amber-500 rounded-full"></span> Penting untuk format dokumen:
                        </p>
                        <ul class="list-disc pl-4 space-y-1 mb-2">
                            <li>Gunakan penanda <strong>[SOAL]</strong> untuk memulai teks soal.</li>
                            <li>Gunakan penanda <strong>[A], [B], [C], [D], [E]</strong> untuk pilihan ganda.</li>
                            <li>Gunakan penanda <strong>[KUNCI]</strong> untuk kunci jawaban.</li>
                            <li>Gunakan penanda <strong>[TIPE]</strong> untuk jenis soal (pg, benar_salah, essay, menjodohkan).</li>
                            <li>Gunakan penanda <strong>[BOBOT]</strong> untuk menentukan nilai/bobot masing-masing soal.</li>
                        </ul>
                        <a :href="route('admin.ujian.soal.template.word')" target="_blank" class="text-indigo-600 hover:underline font-bold inline-flex items-center mt-2">
                            <FileText class="w-4 h-4 mr-1" /> Download Template Word
                        </a>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Pilih File Word (.docx)</label>
                        <input 
                            type="file" 
                            accept=".docx"
                            @input="importWordForm.file = $event.target.files[0]"
                            required 
                            class="w-full px-4 py-3 bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl focus:ring-indigo-600 focus:border-indigo-600 cursor-pointer"
                        />
                        <progress v-if="importWordForm.progress" :value="importWordForm.progress.percentage" max="100" class="w-full h-1.5 bg-indigo-100 rounded mt-3">
                            {{ importWordForm.progress.percentage }}%
                        </progress>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" @click="showImportWordModal = false" class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                            Batal
                        </button>
                        <button type="submit" :disabled="importWordForm.processing" class="px-5 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors flex items-center gap-2">
                            <Check class="w-4 h-4" /> {{ importWordForm.processing ? 'Mengunggah...' : 'Unggah & Impor' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal: Konfirmasi Hapus Soal Permanen -->
        <div v-if="showDeleteSoalModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white w-full max-w-sm rounded-3xl shadow-2xl overflow-hidden text-center">
                <div class="p-8">
                    <div class="w-14 h-14 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center mx-auto mb-5">
                        <AlertTriangle class="w-7 h-7" />
                    </div>
                    <h3 class="text-xl font-black text-slate-900 mb-2">Hapus Soal Permanen?</h3>
                    <p class="text-slate-500 text-sm font-medium leading-relaxed mb-7">
                        Soal ini akan <span class="font-bold text-rose-600">dihapus secara permanen</span> dari bank soal. Jika soal ini sedang digunakan di paket ujian, maka akan ikut terhapus dari paket tersebut.
                    </p>
                    <div class="flex gap-3">
                        <button 
                            @click="showDeleteSoalModal = false" 
                            :disabled="isDeletingSoal"
                            class="flex-1 py-3 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors"
                        >
                            Batal
                        </button>
                        <button 
                            @click="confirmHapusSoal" 
                            :disabled="isDeletingSoal"
                            class="flex-1 py-3 text-sm font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition-colors flex items-center justify-center gap-2"
                        >
                            <span v-if="isDeletingSoal" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
                            <Trash2 v-else class="w-4 h-4" />
                            {{ isDeletingSoal ? 'Menghapus...' : 'Ya, Hapus Permanen' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
