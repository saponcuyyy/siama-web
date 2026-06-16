<script setup>
import { router } from '@inertiajs/vue3';
import { sanitize } from '@/sanitize';

defineProps({
    data: { type: Object, required: true },
});

function visit(url) {
    if (url) router.visit(url);
}
</script>

<template>
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 px-6 py-4 border-t border-slate-200 bg-slate-50/50">
        <p class="text-xs sm:text-sm text-slate-500 font-medium">
            Menampilkan <span class="font-semibold text-slate-700">{{ data.from || 0 }}</span> - <span class="font-semibold text-slate-700">{{ data.to || 0 }}</span> dari <span class="font-semibold text-slate-700">{{ data.total }}</span> data
        </p>
        <div class="flex items-center gap-1.5 flex-wrap">
            <template v-for="(link, i) in data.links" :key="i">
                <button v-if="link.url"
                    :disabled="link.active"
                    @click="visit(link.url)"
                    class="min-w-[32px] h-8 px-2 flex items-center justify-center text-xs rounded-lg transition-all border font-semibold"
                    :class="link.active
                        ? 'bg-indigo-600 border-indigo-600 text-white shadow-sm shadow-indigo-150 cursor-default'
                        : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-indigo-600 hover:border-indigo-200'"
                    v-html="sanitize(link.label)" />
                <span v-else
                    class="min-w-[32px] h-8 px-2 flex items-center justify-center text-xs text-slate-400 font-medium border border-transparent"
                    v-html="sanitize(link.label)" />
            </template>
        </div>
    </div>
</template>
