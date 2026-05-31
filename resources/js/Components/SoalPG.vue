<script setup>
import { sanitize } from '@/sanitize';

const props = defineProps({
    soal: Object,
    jawaban: String,
});
const emit = defineEmits(['update:jawaban', 'save']);
</script>

<template>
    <div class="space-y-3">
        <label v-for="opsi in soal.pilihan" :key="opsi.kode"
            class="flex items-start gap-3 md:gap-4 p-4 md:p-5 rounded-xl md:rounded-2xl border-2 cursor-pointer transition-all"
            :class="jawaban === opsi.kode
                ? 'border-emerald-500 bg-emerald-50/50'
                : 'border-slate-200 bg-white hover:border-slate-300'">
            <div class="flex items-center h-5 md:h-6">
                <input type="radio" :name="'s'+soal.id" :value="opsi.kode"
                    :checked="jawaban === opsi.kode"
                    @change="emit('update:jawaban', opsi.kode); emit('save', 2)"
                    class="w-4 h-4 md:w-5 md:h-5 accent-emerald-600" />
            </div>
            <div class="flex-1 min-w-0">
                <div v-if="opsi.gambar_url" class="rounded-xl overflow-hidden border border-slate-100 max-w-xs">
                    <img :src="opsi.gambar_url" alt="Opsi" class="w-full h-auto" />
                </div>
                <div v-if="opsi.teks" class="prose prose-sm prose-slate mt-1" v-html="sanitize(opsi.teks)" />
            </div>
        </label>
    </div>
</template>
