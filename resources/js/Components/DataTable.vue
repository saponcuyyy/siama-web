<script setup>
import { ref, computed, watch } from 'vue';
import { ChevronLeft, ChevronRight, Search, X, ChevronUp, ChevronDown } from 'lucide-vue-next';

const props = defineProps({
    columns: { type: Array, required: true },
    rows: { type: Array, required: true },
    pageSize: { type: Number, default: 15 },
    searchable: { type: Boolean, default: true },
    title: { type: String, default: '' },
});

const search = ref('');
const currentPage = ref(1);
const sortCol = ref(null);
const sortDir = ref('asc');

// Detect if a cell value looks like a number
function isNumeric(val) {
    if (val === '' || val === null || val === undefined) return false;
    return !isNaN(parseFloat(String(val).replace(/,/g, '.'))) && isFinite(String(val).replace(/,/g, '.'));
}

// Format numbers with thousand separators for display
function formatCell(val) {
    if (!isNumeric(val)) return val;
    const num = parseFloat(String(val).replace(/,/g, '.'));
    if (Number.isInteger(num)) return num.toLocaleString('id-ID');
    return num.toLocaleString('id-ID', { maximumFractionDigits: 2 });
}

// Whether a column is predominantly numeric
const numericColumns = computed(() => {
    return props.columns.map((_, ci) => {
        const sample = props.rows.slice(0, 20).map(r => r[ci]).filter(v => v !== '');
        if (sample.length === 0) return false;
        const numCount = sample.filter(v => isNumeric(v)).length;
        return numCount / sample.length >= 0.6;
    });
});

const filteredRows = computed(() => {
    let rows = props.rows;
    if (search.value.trim()) {
        const q = search.value.toLowerCase();
        rows = rows.filter(row => row.some(cell => String(cell).toLowerCase().includes(q)));
    }
    if (sortCol.value !== null) {
        const ci = sortCol.value;
        rows = [...rows].sort((a, b) => {
            const av = a[ci] ?? '';
            const bv = b[ci] ?? '';
            if (isNumeric(av) && isNumeric(bv)) {
                return sortDir.value === 'asc'
                    ? parseFloat(av) - parseFloat(bv)
                    : parseFloat(bv) - parseFloat(av);
            }
            return sortDir.value === 'asc'
                ? String(av).localeCompare(String(bv), 'id')
                : String(bv).localeCompare(String(av), 'id');
        });
    }
    return rows;
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

const pageButtons = computed(() => {
    const pages = [];
    for (let p = 1; p <= totalPages.value; p++) {
        if (
            p === 1 || p === totalPages.value ||
            Math.abs(p - currentPage.value) <= 1
        ) {
            pages.push(p);
        } else if (
            p === currentPage.value - 2 || p === currentPage.value + 2
        ) {
            pages.push('...');
        }
    }
    // Deduplicate consecutive '...'
    return pages.filter((p, i) => p !== '...' || pages[i - 1] !== '...');
});

watch(search, () => { currentPage.value = 1; });
watch(sortCol, () => { currentPage.value = 1; });

function toggleSort(ci) {
    if (sortCol.value === ci) {
        sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortCol.value = ci;
        sortDir.value = 'asc';
    }
}

function prevPage() { if (currentPage.value > 1) currentPage.value--; }
function nextPage() { if (currentPage.value < totalPages.value) currentPage.value++; }
function clearSearch() { search.value = ''; }
</script>

<template>
    <div class="flex flex-col gap-3">

        <!-- Toolbar: search -->
        <div v-if="searchable || title" class="flex flex-wrap items-center justify-between gap-2">
            <h3 v-if="title" class="text-sm font-bold text-slate-700">{{ title }}</h3>
            <div v-if="searchable" class="relative ml-auto">
                <Search class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400 pointer-events-none" />
                <input
                    v-model="search"
                    type="text"
                    placeholder="Cari data..."
                    class="w-52 pl-8 pr-7 py-1.5 text-xs border border-slate-200 rounded-lg bg-white focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 outline-none transition"
                />
                <button
                    v-if="search"
                    @click="clearSearch"
                    class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition"
                >
                    <X class="w-3 h-3" />
                </button>
            </div>
        </div>

        <!-- Table wrapper with horizontal scroll -->
        <div class="overflow-x-auto rounded-xl border border-slate-200 shadow-sm">
            <table class="w-full text-sm border-collapse">
                <!-- Header -->
                <thead>
                    <tr class="bg-gradient-to-r from-blue-700 to-blue-600">
                        <th
                            v-for="(col, ci) in columns"
                            :key="ci"
                            @click="toggleSort(ci)"
                            class="px-4 py-3 text-left text-[11px] font-bold text-white uppercase tracking-wider whitespace-nowrap select-none cursor-pointer hover:bg-blue-500/30 transition-colors group"
                            :class="{ 'text-right': numericColumns[ci] }"
                        >
                            <span class="inline-flex items-center gap-1">
                                {{ col || '—' }}
                                <span class="opacity-0 group-hover:opacity-60 transition-opacity ml-0.5">
                                    <ChevronUp v-if="sortCol === ci && sortDir === 'asc'" class="w-3 h-3 opacity-100" />
                                    <ChevronDown v-else-if="sortCol === ci && sortDir === 'desc'" class="w-3 h-3 opacity-100" />
                                    <ChevronUp v-else class="w-3 h-3" />
                                </span>
                            </span>
                        </th>
                    </tr>
                </thead>

                <!-- Body -->
                <tbody>
                    <tr
                        v-for="(row, ri) in paginatedRows"
                        :key="ri"
                        class="border-t border-slate-100 transition-colors"
                        :class="ri % 2 === 0 ? 'bg-white hover:bg-blue-50/40' : 'bg-slate-50/60 hover:bg-blue-50/40'"
                    >
                        <td
                            v-for="(cell, ci) in row"
                            :key="ci"
                            class="px-4 py-2.5 text-slate-700 whitespace-nowrap align-middle"
                            :class="[
                                numericColumns[ci] ? 'text-right font-mono tabular-nums text-slate-800' : '',
                                ci === 0 ? 'font-medium text-slate-900' : ''
                            ]"
                        >
                            {{ numericColumns[ci] ? formatCell(cell) : (cell || '—') }}
                        </td>
                    </tr>

                    <!-- Empty state -->
                    <tr v-if="paginatedRows.length === 0">
                        <td :colspan="columns.length" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center gap-2 text-slate-400">
                                <Search class="w-7 h-7 opacity-40" />
                                <p class="text-xs font-semibold">
                                    {{ search ? 'Tidak ada data yang cocok dengan pencarian.' : 'Tidak ada data.' }}
                                </p>
                            </div>
                        </td>
                    </tr>
                </tbody>

                <!-- Footer summary row -->
                <tfoot v-if="filteredRows.length > 0">
                    <tr class="border-t border-slate-200 bg-slate-50">
                        <td
                            :colspan="columns.length"
                            class="px-4 py-2 text-[11px] text-slate-400 font-medium"
                        >
                            Menampilkan {{ startRow }}–{{ endRow }} dari
                            <span class="font-bold text-slate-600">{{ filteredRows.length }}</span> baris
                            <span v-if="search" class="ml-1">
                                (difilter dari {{ rows.length }} total)
                            </span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="flex items-center justify-end gap-1 mt-1">
            <button
                @click="prevPage"
                :disabled="currentPage <= 1"
                class="p-1.5 rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-100 transition disabled:opacity-30 disabled:cursor-not-allowed"
            >
                <ChevronLeft class="w-3.5 h-3.5" />
            </button>

            <template v-for="(p, idx) in pageButtons" :key="idx">
                <span v-if="p === '...'" class="px-1 text-slate-300 text-xs">…</span>
                <button
                    v-else
                    @click="currentPage = p"
                    class="w-7 h-7 rounded-lg text-xs font-bold border transition"
                    :class="p === currentPage
                        ? 'bg-blue-600 text-white border-blue-600 shadow-sm shadow-blue-200'
                        : 'border-slate-200 text-slate-600 hover:bg-slate-50'"
                >
                    {{ p }}
                </button>
            </template>

            <button
                @click="nextPage"
                :disabled="currentPage >= totalPages"
                class="p-1.5 rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-100 transition disabled:opacity-30 disabled:cursor-not-allowed"
            >
                <ChevronRight class="w-3.5 h-3.5" />
            </button>
        </div>

    </div>
</template>
