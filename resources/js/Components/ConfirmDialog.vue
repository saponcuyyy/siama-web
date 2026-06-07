<script setup>
import { AlertTriangle } from 'lucide-vue-next';

defineProps({
    show: Boolean,
    title: { type: String, default: 'Hapus Data?' },
    description: { type: String, default: 'Yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.' },
    confirmText: { type: String, default: 'Ya, Hapus' },
    cancelText: { type: String, default: 'Batal' },
    loading: { type: Boolean, default: false },
});

const emit = defineEmits(['confirm', 'cancel', 'update:show']);

const close = () => emit('update:show', false);

const handleConfirm = () => emit('confirm');

const handleCancel = () => {
    emit('cancel');
    close();
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
        <div class="bg-white w-full max-w-sm rounded-3xl shadow-2xl overflow-hidden text-center animate-in fade-in zoom-in-95 duration-200">
            <div class="p-8">
                <div class="w-14 h-14 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center mx-auto mb-5">
                    <AlertTriangle class="w-8 h-8" />
                </div>
                <h3 class="text-xl font-black text-slate-900 mb-2">{{ title }}</h3>
                <p class="text-slate-500 text-sm font-medium leading-relaxed mb-7">
                    <slot name="description">{{ description }}</slot>
                </p>
                <div class="flex gap-3">
                    <button
                        @click="handleCancel"
                        :disabled="loading"
                        class="flex-1 py-3 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors disabled:opacity-50"
                    >
                        {{ cancelText }}
                    </button>
                    <button
                        @click="handleConfirm"
                        :disabled="loading"
                        class="flex-1 py-3 text-sm font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition-colors flex items-center justify-center gap-2 disabled:opacity-50"
                    >
                        <span v-if="loading" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
                        <AlertTriangle v-else class="w-4 h-4" />
                        {{ loading ? 'Menghapus...' : confirmText }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
