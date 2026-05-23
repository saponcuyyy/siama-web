<script setup>
import { ref, computed, watch } from 'vue';
import { Search, ChevronDown, X } from 'lucide-vue-next';

const props = defineProps({
    modelValue: { type: [String, Number], default: '' },
    options: { type: Array, required: true },
    placeholder: { type: String, default: '-- Cari & Pilih --' },
    labelKey: { type: String, default: 'label' },
    valueKey: { type: String, default: 'value' },
    searchKeys: { type: [Array, String], default: () => ['label'] },
    disabled: { type: Boolean, default: false },
});

const keys = computed(() =>
    Array.isArray(props.searchKeys) ? props.searchKeys : [props.searchKeys]
);

const emit = defineEmits(['update:modelValue']);

const open = ref(false);
const query = ref('');
const highlightIndex = ref(-1);

const filteredOptions = computed(() => {
    if (!query.value) return props.options;
    const q = query.value.toLowerCase();
    return props.options.filter(opt => {
        return keys.value.some(key => {
            const val = typeof opt === 'object' ? opt[key] : opt;
            return String(val ?? '').toLowerCase().includes(q);
        });
    });
});

const selectedLabel = computed(() => {
    if (!props.modelValue) return '';
    const opt = props.options.find(o => o[props.valueKey] == props.modelValue);
    return opt ? opt[props.labelKey] : '';
});

const selectOption = (opt) => {
    emit('update:modelValue', opt[props.valueKey]);
    query.value = '';
    open.value = false;
    highlightIndex.value = -1;
};

const toggle = () => {
    if (!props.disabled) open.value = !open.value;
};

const close = () => {
    open.value = false;
    query.value = '';
    highlightIndex.value = -1;
};

const clear = () => {
    emit('update:modelValue', '');
    query.value = '';
};

const onKeydown = (e) => {
    if (!open.value && e.key === 'Enter') { open.value = true; return; }
    if (!open.value) return;

    if (e.key === 'ArrowDown') {
        e.preventDefault();
        highlightIndex.value = Math.min(highlightIndex.value + 1, filteredOptions.value.length - 1);
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        highlightIndex.value = Math.max(highlightIndex.value - 1, 0);
    } else if (e.key === 'Enter' && highlightIndex.value >= 0) {
        e.preventDefault();
        selectOption(filteredOptions.value[highlightIndex.value]);
    } else if (e.key === 'Escape') {
        close();
    }
};

const inputRef = ref(null);

watch(open, (isOpen) => {
    if (isOpen) {
        query.value = '';
        highlightIndex.value = -1;
        setTimeout(() => inputRef.value?.focus(), 50);
    }
});
</script>

<template>
    <div class="relative" @keydown="onKeydown">
        <div
            @click="toggle"
            :class="['w-full flex items-center gap-2 px-4 py-2.5 rounded-xl border cursor-pointer transition-colors',
                disabled ? 'bg-slate-100 cursor-not-allowed' : 'bg-slate-50 hover:border-indigo-400',
                open ? 'border-indigo-500 ring-1 ring-indigo-200' : 'border-slate-200'
            ]"
        >
            <Search class="w-4 h-4 text-slate-400 shrink-0" />
            <span v-if="selectedLabel" class="flex-1 font-bold text-slate-900 truncate">
                {{ selectedLabel }}
            </span>
            <span v-else class="flex-1 text-slate-400 text-sm font-medium">
                {{ placeholder }}
            </span>
            <button v-if="modelValue" @click.stop="clear" class="p-0.5 text-slate-400 hover:text-rose-500 transition-colors">
                <X class="w-4 h-4" />
            </button>
            <ChevronDown class="w-4 h-4 text-slate-400 shrink-0" :class="open ? 'rotate-180' : ''" />
        </div>

        <div v-if="open" class="absolute z-50 mt-1 w-full bg-white border border-slate-200 rounded-xl shadow-lg overflow-hidden">
            <div class="p-2 border-b border-slate-100">
                <input
                    ref="inputRef"
                    v-model="query"
                    type="text"
                    placeholder="Ketik untuk mencari..."
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500"
                />
            </div>
            <ul class="max-h-60 overflow-y-auto py-1">
                <li
                    v-for="(opt, i) in filteredOptions"
                    :key="opt[valueKey]"
                    @click="selectOption(opt)"
                    @mouseenter="highlightIndex = i"
                    :class="[
                        'px-4 py-2.5 text-sm cursor-pointer flex items-center gap-2 transition-colors',
                        opt[valueKey] == modelValue ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-700',
                        highlightIndex === i && opt[valueKey] != modelValue ? 'bg-slate-50' : ''
                    ]"
                >
                    <span v-html="opt[valueKey] == modelValue ? '✓' : ''" class="w-4 shrink-0 text-indigo-600 text-xs" />
                    <span class="truncate">{{ opt[labelKey] }}</span>
                </li>
                <li v-if="filteredOptions.length === 0" class="px-4 py-6 text-center text-slate-400 text-sm font-medium">
                    Tidak ditemukan
                </li>
            </ul>
        </div>

        <div v-if="open" class="fixed inset-0 z-40" @click="close"></div>
    </div>
</template>
