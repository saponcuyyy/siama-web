<script setup>
import { ref, computed, watch } from 'vue';
import { ChevronLeft, ChevronRight, Search, X } from 'lucide-vue-next';

const props = defineProps({
    columns: { type: Array, required: true },
    rows: { type: Array, required: true },
    pageSize: { type: Number, default: 10 },
    searchable: { type: Boolean, default: true },
    title: { type: String, default: '' },
});

const search = ref('');
const currentPage = ref(1);

const filteredRows = computed(() => {
    if (!search.value.trim()) return props.rows;
    const q = search.value.toLowerCase();
    return props.rows.filter(row =>
        row.some(cell => String(cell).toLowerCase().includes(q))
    );
});

const totalPages = computed(() =>
    Math.max(1, Math.ceil(filteredRows.value.length / props.pageSize))
);

const paginatedRows = computed(() => {
    const start = (currentPage.value - 1) * props.pageSize;
    return filteredRows.value.slice(start, start + props.pageSize);
});

const startRow = computed(() =>
    filteredRows.value.length === 0 ? 0 : (currentPage.value - 1) * props.pageSize + 1
);

const endRow = computed(() =>
    Math.min(currentPage.value * props.pageSize, filteredRows.value.length)
);

watch(search, () => { currentPage.value = 1; });

function prevPage() {
    if (currentPage.value > 1) currentPage.value--;
}

function nextPage() {
    if (currentPage.value < totalPages.value) currentPage.value++;
}

function clearSearch() {
    search.value = '';
}
</script>

<template>
    <div class="space-y-3">
        <!-- Title & Search -->
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h3 v-if="title" class="text-sm font-bold text-slate-700">{{ title }}</h3>
            <div v-if="searchable" class="relative ml-auto">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                <input
                    v-model="search"
                    type="text"
                    placeholder="Cari..."
                    class="w-56 pl-9 pr-8 py-1.5 text-xs border border-slate-200 rounded-lg focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                />
                <button
                    v-if="search"
                    @click="clearSearch"
                    class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                >
                    <X class="w-3.5 h-3.5" />
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th v-for="(col, ci) in columns" :key="ci"
                            class="px-4 py-3 text-left text-xs font-bold text-slate-600 uppercase tracking-wider whitespace-nowrap"
                        >
                            {{ col }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="(row, ri) in paginatedRows" :key="ri"
                        class="hover:bg-slate-50 transition-colors"
                    >
                        <td v-for="(cell, ci) in row" :key="ci"
                            class="px-4 py-2.5 text-slate-700 whitespace-nowrap"
                        >
                            {{ cell }}
                        </td>
                    </tr>
                    <tr v-if="paginatedRows.length === 0">
                        <td :colspan="columns.length" class="px-4 py-10 text-center text-slate-400 text-sm">
                            Tidak ada data ditemukan.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="filteredRows.length > pageSize" class="flex items-center justify-between text-xs">
            <p class="text-slate-400 font-medium">
                {{ startRow }}-{{ endRow }} dari {{ filteredRows.length }}
            </p>
            <div class="flex items-center gap-1">
                <button
                    @click="prevPage"
                    :disabled="currentPage <= 1"
                    class="p-1.5 rounded-lg text-slate-500 hover:bg-slate-100 border border-slate-200 transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
                >
                    <ChevronLeft class="w-3.5 h-3.5" />
                </button>
                <template v-for="p in totalPages" :key="p">
                    <button
                        v-if="p === 1 || p === totalPages || Math.abs(p - currentPage) <= 1"
                        @click="currentPage = p"
                        :class="p === currentPage ? 'bg-blue-600 text-white border-blue-600' : 'text-slate-600 hover:bg-slate-50 border-slate-200'"
                        class="w-7 h-7 rounded-lg text-xs font-bold border transition-colors"
                    >
                        {{ p }}
                    </button>
                    <span
                        v-else-if="p === currentPage - 2 || p === currentPage + 2"
                        class="text-slate-300 px-1"
                    >...</span>
                </template>
                <button
                    @click="nextPage"
                    :disabled="currentPage >= totalPages"
                    class="p-1.5 rounded-lg text-slate-500 hover:bg-slate-100 border border-slate-200 transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
                >
                    <ChevronRight class="w-3.5 h-3.5" />
                </button>
            </div>
        </div>
    </div>
</template>
