<script setup>
const props = defineProps({
    soal: Object,
    jawaban: String,
});
const emit = defineEmits(['update:jawaban', 'save']);
</script>

<template>
    <div class="flex gap-4">
        <label v-for="opt in ['Benar','Salah']" :key="opt"
            class="flex-1 flex items-center justify-center gap-3 p-5 md:p-6 rounded-xl md:rounded-2xl border-2 cursor-pointer transition-all font-black text-sm md:text-base uppercase tracking-wider select-none"
            :class="jawaban === opt
                ? opt==='Benar'
                    ? 'border-emerald-500 bg-emerald-50 text-emerald-700 shadow-sm'
                    : 'border-rose-500 bg-rose-50 text-rose-700 shadow-sm'
                : 'border-slate-200 bg-white text-slate-500 hover:border-slate-300 hover:shadow-sm'">
            <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 transition-all"
                :class="jawaban === opt
                    ? opt==='Benar'
                        ? 'border-emerald-500 bg-emerald-500'
                        : 'border-rose-500 bg-rose-500'
                    : 'border-slate-300 bg-white'">
                <svg v-if="jawaban === opt" class="w-3 h-3 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12" />
                </svg>
            </div>
            <input type="radio" :value="opt"
                :checked="jawaban === opt"
                @change="emit('update:jawaban', opt); emit('save', 2)"
                class="sr-only" />
            {{ opt }}
        </label>
    </div>
</template>
