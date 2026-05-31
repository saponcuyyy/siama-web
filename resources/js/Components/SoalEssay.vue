<script setup>
import { ref, watch, onUnmounted } from 'vue';

const props = defineProps({
    soal: Object,
    jawaban: String,
});
const emit = defineEmits(['update:jawaban', 'save']);

const chars = ref((props.jawaban || '').length);
let debounceTimer = null;

function onInput(e) {
    const val = e.target.value;
    chars.value = val.length;
    emit('update:jawaban', val);
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => emit('save', 3), 2000);
}

function onBlur() {
    clearTimeout(debounceTimer);
    emit('save', 5);
}

watch(() => props.jawaban, (val) => {
    chars.value = (val || '').length;
});

onUnmounted(() => {
    clearTimeout(debounceTimer);
});
</script>

<template>
    <div>
        <textarea :value="jawaban"
            @input="onInput"
            @blur="onBlur"
            rows="6" placeholder="Ketik jawaban Anda di sini..."
            class="w-full p-4 md:p-5 bg-white border-2 border-slate-200 rounded-xl md:rounded-2xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 text-slate-800 resize-y transition-all text-sm md:text-base">
        </textarea>
        <div class="flex items-center justify-between mt-2 text-[10px] md:text-xs font-bold tracking-wider">
            <span class="text-slate-400">{{ chars }} karakter</span>
            <span class="text-emerald-600">Tersimpan otomatis</span>
        </div>
    </div>
</template>
