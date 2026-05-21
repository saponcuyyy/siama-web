<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    CheckSquare, Search, Check, AlertCircle 
} from 'lucide-vue-next';

const props = defineProps({
    jawabanList: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');

const handleSearch = () => {
    router.get(route('admin.ujian.penilaian.index'), { search: search.value }, { preserveState: true, replace: true });
};

const forms = ref({});

// Initialize forms
props.jawabanList.data.forEach(j => {
    forms.value[j.id] = useForm({
        skor: 0
    });
});

const submitNilai = (id) => {
    forms.value[id].post(route('admin.ujian.penilaian.nilai', id), {
        preserveScroll: true,
        onSuccess: () => {
            // Remove from list or show success state
        }
    });
};
</script>

<template>
    <Head title="Penilaian Essay" />
    
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between gap-4 items-start sm:items-center">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                        <CheckSquare class="w-7 h-7 text-indigo-600" />
                        Penilaian Essay
                    </h1>
                    <p class="text-slate-500 font-medium mt-1">Koreksi manual jawaban essay siswa yang membutuhkan penilaian.</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row gap-4 items-center justify-between">
                <div class="relative w-full sm:w-96">
                    <input 
                        type="text" 
                        v-model="search" 
                        @keyup.enter="handleSearch"
                        placeholder="Cari nama siswa..." 
                        class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-600 focus:border-indigo-600 transition-colors"
                    >
                    <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
                </div>
            </div>

            <!-- List of Answers -->
            <div class="space-y-4">
                <div v-if="jawabanList.data.length === 0" class="bg-white rounded-3xl border border-slate-200 p-12 text-center">
                    <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4 text-slate-400">
                        <CheckSquare class="w-8 h-8" />
                    </div>
                    <h3 class="text-slate-900 font-bold mb-1">Semua Selesai Dinilai!</h3>
                    <p class="text-slate-500 text-sm">Tidak ada jawaban essay yang menunggu penilaian saat ini.</p>
                </div>

                <div v-for="jawaban in jawabanList.data" :key="jawaban.id" class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden flex flex-col md:flex-row">
                    <!-- Detail Soal & Jawaban -->
                    <div class="flex-1 p-6 border-b md:border-b-0 md:border-r border-slate-100">
                        <div class="flex items-center gap-3 mb-4 border-b border-slate-100 pb-4">
                            <div class="w-10 h-10 bg-indigo-50 rounded-full flex items-center justify-center font-bold text-indigo-700">
                                {{ jawaban.peserta_ujian?.siswa?.nama.charAt(0) }}
                            </div>
                            <div>
                                <p class="font-bold text-slate-900">{{ jawaban.peserta_ujian?.siswa?.nama }}</p>
                                <p class="text-xs text-slate-500">
                                    {{ jawaban.peserta_ujian?.sesi_ujian?.paket_ujian?.nama }}
                                </p>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Pertanyaan:</h4>
                            <div class="prose prose-sm max-w-none text-slate-800 font-medium p-4 bg-slate-50 rounded-xl" v-html="jawaban.soal?.pertanyaan"></div>
                        </div>

                        <div>
                            <h4 class="text-xs font-bold text-indigo-400 uppercase tracking-wider mb-2 flex items-center gap-1">
                                Jawaban Siswa
                            </h4>
                            <div class="p-4 bg-white border-2 border-indigo-50 rounded-xl text-slate-800 whitespace-pre-wrap">
                                {{ jawaban.jawaban || '(Tidak dijawab)' }}
                            </div>
                        </div>
                    </div>

                    <!-- Panel Penilaian -->
                    <div class="w-full md:w-72 bg-slate-50 p-6 flex flex-col justify-between shrink-0">
                        <div>
                            <div v-if="jawaban.soal?.kunci_jawaban" class="mb-6 p-4 bg-emerald-50 rounded-xl border border-emerald-100">
                                <h4 class="text-xs font-bold text-emerald-600 uppercase tracking-wider mb-1 flex items-center gap-1">
                                    <AlertCircle class="w-3 h-3" /> Panduan Nilai
                                </h4>
                                <p class="text-sm text-emerald-800">{{ jawaban.soal.kunci_jawaban }}</p>
                            </div>

                            <label class="block text-sm font-bold text-slate-700 mb-2">Berikan Nilai (Max: {{ jawaban.soal?.bobot }})</label>
                            <input 
                                type="number" 
                                min="0" 
                                :max="jawaban.soal?.bobot" 
                                step="0.5"
                                v-model="forms[jawaban.id].skor"
                                class="w-full text-center text-2xl font-black text-indigo-600 px-4 py-3 bg-white border-2 border-slate-200 rounded-2xl focus:border-indigo-600 focus:ring-0 transition-colors"
                            >
                        </div>
                        
                        <button 
                            @click="submitNilai(jawaban.id)"
                            :disabled="forms[jawaban.id].processing || forms[jawaban.id].skor > jawaban.soal?.bobot"
                            class="w-full mt-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition-colors flex items-center justify-center gap-2 disabled:opacity-50"
                        >
                            <Check class="w-5 h-5" /> Simpan Nilai
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Pagination -->
            <div v-if="jawabanList.links && jawabanList.links.length > 3" class="flex justify-center mt-6">
                <!-- Pagination omitted for brevity, same as other pages -->
            </div>
        </div>
    </AuthenticatedLayout>
</template>
