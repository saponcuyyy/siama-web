<script setup>
const props = defineProps({
    soal: Object,
    jawaban: Object,
});
const emit = defineEmits(['update:jawaban', 'save']);

function updateMatch(kiri, val) {
    const cur = { ...(props.jawaban || {}) };
    cur[kiri] = val;
    emit('update:jawaban', cur);
    emit('save', 2);
}

function isMatched(kiri) {
    const v = props.jawaban?.[kiri];
    return v && v !== '';
}
</script>

<template>
    <div class="space-y-4">
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
                <div v-for="(kiri,i) in soal.pilihan_kiri" :key="i"
                    class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center transition-opacity"
                    :class="isMatched(kiri) ? 'opacity-100' : 'opacity-90'">
                    <div class="flex-1 p-3 md:p-4 bg-white rounded-xl border font-medium text-slate-700 text-sm shadow-sm flex items-center gap-2 transition-colors"
                        :class="isMatched(kiri) ? 'border-emerald-300 bg-emerald-50/50' : 'border-slate-200'">
                        <svg v-if="isMatched(kiri)" class="w-4 h-4 text-emerald-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                        {{ kiri }}
                    </div>
                    <select :value="jawaban?.[kiri]||''"
                        @change="updateMatch(kiri, $event.target.value)"
                        class="flex-1 p-3 md:p-4 bg-white border rounded-xl text-slate-700 font-medium text-sm shadow-sm cursor-pointer focus:ring-2 focus:ring-emerald-500/20 appearance-none transition-all"
                        :class="isMatched(kiri) ? 'border-emerald-400 bg-emerald-50/30' : 'border-slate-200 hover:border-slate-300'">
                        <option value="" disabled>-- Pilih --</option>
                        <option v-for="(kanan,j) in soal.pilihan_kanan" :key="j" :value="kanan">
                            {{ kanan }}
                        </option>
                    </select>
                    <div v-if="isMatched(kiri)" class="flex items-center justify-center sm:w-6">
                        <svg class="w-5 h-5 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
