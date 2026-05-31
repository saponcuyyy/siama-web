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
    <div class="flex items-center justify-between mt-6">
        <p class="text-sm text-slate-500">
            Menampilkan {{ data.from }} - {{ data.to }} dari {{ data.total }}
        </p>
        <div class="flex items-center gap-1">
            <template v-for="(link, i) in data.links" :key="i">
                <button v-if="link.url"
                    :disabled="link.active"
                    @click="visit(link.url)"
                    class="px-3 py-1 text-sm rounded-lg transition-colors"
                    :class="link.active
                        ? 'bg-emerald-500 text-white font-bold cursor-default'
                        : 'text-slate-600 hover:bg-slate-100'"
                    v-html="sanitize(link.label)" />
                <span v-else
                    class="px-3 py-1 text-sm text-slate-400"
                    v-html="sanitize(link.label)" />
            </template>
        </div>
    </div>
</template>
