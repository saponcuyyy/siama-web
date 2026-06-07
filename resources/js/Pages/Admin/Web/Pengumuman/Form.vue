<script setup>
import AdminWebLayout from '@/Layouts/AdminWebLayout.vue';
import TiptapEditor from '@/Components/TiptapEditor.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { FileText, Calendar, Upload, ArrowLeft, Save, X, AlertCircle, File, PenSquare } from 'lucide-vue-next';

const props = defineProps({
    pengumuman: Object,
    lampiran_url: String,
});

const form = useForm({
    judul:           props.pengumuman?.judul || '',
    konten:          props.pengumuman?.konten || '',
    prioritas:       props.pengumuman?.prioritas || 'normal',
    tanggal_mulai:   props.pengumuman?.tanggal_mulai || '',
    tanggal_selesai: props.pengumuman?.tanggal_selesai || '',
    status:          props.pengumuman?.status || 'aktif',
    lampiran:        null,
    hapus_lampiran:  false,
    _method:         props.pengumuman ? 'PUT' : undefined,
});

const fileInput    = ref(null);
const isDragging   = ref(false);
const selectedFile = ref(null);
const clientError  = ref('');
const MAX_SIZE_MB  = 10;

const hasExistingLampiran = computed(
    () => props.lampiran_url && !form.hapus_lampiran
);

function isPdf(file) {
    const allowedMimes = ['application/pdf', 'application/octet-stream'];
    const ext = file.name.split('.').pop().toLowerCase();
    return allowedMimes.includes(file.type) || ext === 'pdf';
}

function validateAndSetFile(file) {
    clientError.value = '';
    if (!file) return;

    if (!isPdf(file)) {
        clientError.value = 'Hanya file PDF yang diizinkan.';
        return;
    }
    if (file.size > MAX_SIZE_MB * 1024 * 1024) {
        clientError.value = `Ukuran file tidak boleh melebihi ${MAX_SIZE_MB} MB.`;
        return;
    }

    selectedFile.value  = file;
    form.lampiran       = file;
    form.hapus_lampiran = false;
}

function onFileChange(e) {
    validateAndSetFile(e.target.files[0] ?? null);
}

function onDrop(e) {
    isDragging.value = false;
    validateAndSetFile(e.dataTransfer.files[0] ?? null);
}

function removeSelectedFile() {
    selectedFile.value = null;
    form.lampiran      = null;
    clientError.value  = '';
    if (fileInput.value) fileInput.value.value = '';
}

function removeExistingLampiran() {
    form.hapus_lampiran = true;
}

function undoRemoveExisting() {
    form.hapus_lampiran = false;
}

function formatSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1024 / 1024).toFixed(1) + ' MB';
}

const submit = () => {
    const options = {
        forceFormData: true,
        onError: () => {},
    };

    if (props.pengumuman) {
        form._method = 'PUT';
        form.post(route('admin.web.pengumuman.update', props.pengumuman.hashid), options);
    } else {
        form._method = undefined;
        form.post(route('admin.web.pengumuman.store'), options);
    }
};
</script>

<template>
    <AdminWebLayout>
        <Head :title="pengumuman ? 'Edit Pengumuman' : 'Tambah Pengumuman'" />

        <div class="max-w-5xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-black text-slate-900">
                        {{ pengumuman ? 'Edit Pengumuman' : 'Tambah Pengumuman' }}
                    </h1>
                    <p class="text-slate-500 text-sm">Formulir untuk mempublikasikan pengumuman sekolah.</p>
                </div>
                <Link :href="route('admin.web.pengumuman.index')" class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-500 hover:text-slate-700 transition-colors">
                    <ArrowLeft class="w-4 h-4" />
                    Kembali
                </Link>
            </div>

            <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- ─── Main Content ──────────────────────────────── -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Card: Konten -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="flex items-center gap-2 px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                            <FileText class="w-5 h-5 text-slate-500" />
                            <h2 class="font-bold text-slate-800 text-sm uppercase tracking-wider">Konten Pengumuman</h2>
                        </div>
                        <div class="p-6 space-y-6">
                            <div class="space-y-1.5">
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Judul Pengumuman</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                                        <PenSquare class="w-5 h-5 text-slate-400" />
                                    </div>
                                    <input
                                        v-model="form.judul"
                                        type="text"
                                        class="w-full border-slate-200 rounded-xl focus:ring-2 focus:ring-green-500/20 focus:border-green-500 text-base font-semibold text-slate-900 placeholder:text-slate-400 pl-11 pr-4 py-3"
                                        required
                                        placeholder="Contoh: Libur Hari Raya Idul Fitri 1448 H"
                                        maxlength="255"
                                    >
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                        <span class="text-[10px] font-medium text-slate-400 tabular-nums" :class="form.judul.length >= 255 ? 'text-red-500' : ''">
                                            {{ form.judul.length }}/255
                                        </span>
                                    </div>
                                </div>
                                <p v-if="form.errors.judul" class="text-xs text-red-500 flex items-center gap-1 mt-1.5">
                                    <AlertCircle class="w-3.5 h-3.5" />{{ form.errors.judul }}
                                </p>
                            </div>

                            <div class="space-y-1.5">
                                <label class="block text-sm font-semibold text-slate-700">Isi Pengumuman</label>
                                <TiptapEditor v-model="form.konten" />
                                <p v-if="form.errors.konten" class="text-xs text-red-500 flex items-center gap-1">
                                    <AlertCircle class="w-3.5 h-3.5" />{{ form.errors.konten }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Card: Lampiran PDF -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="flex items-center gap-2 px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                            <Upload class="w-5 h-5 text-slate-500" />
                            <h2 class="font-bold text-slate-800 text-sm uppercase tracking-wider">Lampiran PDF</h2>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <p class="text-sm text-slate-600">Unggah file PDF untuk dikonversi menjadi halaman web.</p>
                                <p class="text-xs text-slate-400 mt-0.5">Maksimum 10 MB. Format: PDF.</p>
                            </div>

                            <!-- Existing lampiran (edit mode) -->
                            <div v-if="pengumuman?.lampiran" class="rounded-xl border p-3"
                                :class="form.hapus_lampiran ? 'border-red-200 bg-red-50' : 'border-blue-200 bg-blue-50'">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0"
                                        :class="form.hapus_lampiran ? 'bg-red-200' : 'bg-blue-200'">
                                        <File class="w-5 h-5" :class="form.hapus_lampiran ? 'text-red-600' : 'text-blue-600'" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold" :class="form.hapus_lampiran ? 'text-red-700 line-through' : 'text-blue-800'">
                                            Halaman Web Aktif
                                        </p>
                                        <p class="text-xs" :class="form.hapus_lampiran ? 'text-red-500' : 'text-blue-500'">
                                            {{ form.hapus_lampiran ? 'Akan dihapus saat disimpan' : 'Halaman HTML hasil konversi PDF sudah aktif' }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <a v-if="lampiran_url && !form.hapus_lampiran"
                                            :href="lampiran_url"
                                            target="_blank"
                                            class="text-xs font-semibold text-blue-600 hover:text-blue-800 hover:underline px-2 py-1 rounded-lg hover:bg-blue-100 transition-colors">
                                            Lihat Halaman
                                        </a>
                                        <button v-if="!form.hapus_lampiran"
                                            type="button"
                                            @click="removeExistingLampiran"
                                            class="text-xs font-semibold text-red-500 hover:text-red-700 px-2 py-1 rounded-lg hover:bg-red-100 transition-colors">
                                            Hapus
                                        </button>
                                        <button v-else
                                            type="button"
                                            @click="undoRemoveExisting"
                                            class="text-xs font-semibold text-slate-600 hover:text-slate-800 px-2 py-1 rounded-lg hover:bg-slate-100 transition-colors">
                                            Batalkan
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Dropzone -->
                            <div
                                v-if="!selectedFile"
                                class="relative rounded-xl border-2 border-dashed transition-all duration-200 cursor-pointer"
                                :class="isDragging
                                    ? 'border-blue-400 bg-blue-50 scale-[1.01]'
                                    : 'border-slate-300 bg-slate-50 hover:border-blue-400 hover:bg-blue-50'"
                                @dragover.prevent="isDragging = true"
                                @dragleave.prevent="isDragging = false"
                                @drop.prevent="onDrop"
                                @click="fileInput.click()"
                            >
                                <div class="flex flex-col items-center justify-center py-8 px-4 text-center select-none">
                                    <div class="w-12 h-12 rounded-full flex items-center justify-center mb-3 transition-colors"
                                        :class="isDragging ? 'bg-blue-200' : 'bg-slate-200'">
                                        <Upload class="w-6 h-6" :class="isDragging ? 'text-blue-500' : 'text-slate-400'" />
                                    </div>
                                    <p class="text-sm font-semibold text-slate-600" :class="isDragging ? 'text-blue-600' : ''">
                                        {{ isDragging ? 'Lepaskan file di sini' : 'Drag & drop file PDF di sini' }}
                                    </p>
                                    <p class="text-xs text-slate-400 mt-1">atau klik untuk memilih file</p>
                                </div>
                                <input
                                    ref="fileInput"
                                    type="file"
                                    accept=".pdf,application/pdf"
                                    class="sr-only"
                                    @change="onFileChange"
                                >
                            </div>

                            <!-- File terpilih -->
                            <div v-if="selectedFile" class="flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 p-3">
                                <div class="w-9 h-9 rounded-lg bg-green-200 flex items-center justify-center shrink-0">
                                    <File class="w-5 h-5 text-green-700" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-green-800 truncate">{{ selectedFile.name }}</p>
                                    <p class="text-xs text-green-600">{{ formatSize(selectedFile.size) }}</p>
                                </div>
                                <button type="button" @click="removeSelectedFile"
                                    class="shrink-0 text-xs font-semibold text-red-500 hover:text-red-700 px-2 py-1 rounded-lg hover:bg-red-100 transition-colors">
                                    <X class="w-4 h-4" />
                                </button>
                            </div>

                            <p v-if="clientError" class="text-xs text-red-500 flex items-center gap-1">
                                <AlertCircle class="w-3.5 h-3.5 shrink-0" />{{ clientError }}
                            </p>
                            <p v-if="form.errors.lampiran" class="text-xs text-red-500 flex items-center gap-1">
                                <AlertCircle class="w-3.5 h-3.5 shrink-0" />{{ form.errors.lampiran }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- ─── Sidebar ──────────────────────────────────── -->
                <div class="space-y-6">
                    <!-- Card: Pengaturan -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="flex items-center gap-2 px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                            <FileText class="w-5 h-5 text-slate-500" />
                            <h2 class="font-bold text-slate-800 text-sm uppercase tracking-wider">Pengaturan</h2>
                        </div>
                        <div class="p-6 space-y-5">
                            <div class="space-y-1.5">
                                <label class="block text-sm font-semibold text-slate-700">Prioritas</label>
                                <select v-model="form.prioritas" class="w-full border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 text-sm">
                                    <option value="rendah">Rendah (Info Biasa)</option>
                                    <option value="normal">Normal</option>
                                    <option value="tinggi">Tinggi (Penting &amp; Mendesak)</option>
                                </select>
                                <p v-if="form.errors.prioritas" class="text-xs text-red-500">{{ form.errors.prioritas }}</p>
                            </div>

                            <div class="space-y-1.5">
                                <label class="block text-sm font-semibold text-slate-700">Status</label>
                                <select v-model="form.status" class="w-full border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 text-sm">
                                    <option value="aktif">Aktif (Tampil di website)</option>
                                    <option value="nonaktif">Nonaktif (Sembunyikan)</option>
                                </select>
                                <p v-if="form.errors.status" class="text-xs text-red-500">{{ form.errors.status }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Card: Masa Berlaku -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="flex items-center gap-2 px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                            <Calendar class="w-5 h-5 text-slate-500" />
                            <h2 class="font-bold text-slate-800 text-sm uppercase tracking-wider">Masa Berlaku</h2>
                        </div>
                        <div class="p-6 space-y-5">
                            <div class="space-y-1.5">
                                <label class="block text-sm font-semibold text-slate-700">Tanggal Mulai</label>
                                <input v-model="form.tanggal_mulai" type="date" class="w-full border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 text-sm">
                                <p class="text-[10px] text-slate-400">Kosongi jika berlaku selamanya</p>
                                <p v-if="form.errors.tanggal_mulai" class="text-xs text-red-500">{{ form.errors.tanggal_mulai }}</p>
                            </div>

                            <div class="space-y-1.5">
                                <label class="block text-sm font-semibold text-slate-700">Tanggal Selesai</label>
                                <input v-model="form.tanggal_selesai" type="date" class="w-full border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 text-sm">
                                <p class="text-[10px] text-slate-400">Kosongi jika tidak ada batas akhir</p>
                                <p v-if="form.errors.tanggal_selesai" class="text-xs text-red-500">{{ form.errors.tanggal_selesai }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Progress upload -->
                    <div v-if="form.progress" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-2">
                        <div class="flex justify-between text-xs text-slate-500">
                            <span>Mengupload lampiran...</span>
                            <span class="font-semibold">{{ form.progress.percentage }}%</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                            <div class="bg-blue-500 h-full rounded-full transition-all duration-300"
                                :style="{ width: form.progress.percentage + '%' }"></div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <button
                        type="submit"
                        :disabled="form.processing || !!clientError"
                        class="w-full bg-green-600 text-white px-6 py-3.5 rounded-xl font-bold text-sm hover:bg-green-700 transition-all shadow-lg shadow-green-200 disabled:opacity-50 disabled:cursor-not-allowed active:scale-[0.98] flex items-center justify-center gap-2"
                    >
                        <span v-if="form.processing" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
                        <Save v-else class="w-4 h-4" />
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Pengumuman' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminWebLayout>
</template>
