<script setup>
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed } from 'vue';
import { FileSpreadsheet, ArrowUpDown, ChevronDown, Download } from 'lucide-vue-next';

const props = defineProps({
    rombels: Array,
    mapels: Array,
    rows: Array,
    filters: Object,
});

const selectedRombel = ref(props.filters.rombel_id || '');

const changeRombel = () => {
    router.get(route('admin.ujian.nilai.index'), { rombel_id: selectedRombel.value || null }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const sortField = ref('');
const sortDir = ref('asc');

const sortedRows = computed(() => {
    if (!sortField.value) return props.rows;
    return [...props.rows].sort((a, b) => {
        let va, vb;
        if (sortField.value === 'nama' || sortField.value === 'nisn') {
            va = a[sortField.value];
            vb = b[sortField.value];
        } else if (sortField.value === 'rata_rata') {
            va = a.rata_rata ?? -1;
            vb = b.rata_rata ?? -1;
        } else {
            const mapelId = parseInt(sortField.value);
            va = a.nilai[mapelId] ?? -1;
            vb = b.nilai[mapelId] ?? -1;
        }
        if (va === vb) return 0;
        const cmp = va > vb ? 1 : -1;
        return sortDir.value === 'asc' ? cmp : -cmp;
    });
});

const toggleSort = (field) => {
    if (sortField.value === field) {
        sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortDir.value = 'desc';
    }
};

const sortIcon = (field) => {
    if (sortField.value !== field) return ArrowUpDown;
    return ChevronDown;
};
</script>

<template>
    <Head title="Nilai Siswa" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
                <div class="flex items-center gap-3">
                    <FileSpreadsheet class="w-6 h-6 text-indigo-600" />
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Nilai Siswa</h1>
                </div>
                <div class="flex items-center gap-3">
                    <select v-model="selectedRombel" @change="changeRombel"
                        class="text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer min-w-[200px]">
                        <option value="">Pilih Rombel</option>
                        <option v-for="r in rombels" :key="r.id" :value="r.id">
                            {{ r.tingkat }} {{ r.nama }}
                        </option>
                    </select>
                    <a v-if="filters.rombel_id" :href="route('admin.ujian.nilai.export', { rombel_id: filters.rombel_id })"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 text-white rounded-xl text-sm font-bold hover:bg-emerald-700 transition-colors shadow-sm">
                        <Download class="w-4 h-4" />
                        Download Excel
                    </a>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!filters.rombel_id" class="bg-white rounded-3xl border border-slate-200 shadow-sm p-12 text-center">
                <FileSpreadsheet class="w-12 h-12 text-slate-300 mx-auto mb-4" />
                <h3 class="text-lg font-bold text-slate-700 mb-1">Belum ada data</h3>
                <p class="text-sm text-slate-500">Pilih rombel untuk menampilkan nilai siswa</p>
            </div>

            <!-- Table -->
            <div v-else class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider">
                                <th class="p-4 pl-6 w-10 text-center">#</th>
                                <th class="p-4 min-w-[180px] cursor-pointer select-none" @click="toggleSort('nama')">
                                    <span class="inline-flex items-center gap-1">
                                        Nama
                                        <component :is="sortIcon('nama')" class="w-3.5 h-3.5" />
                                    </span>
                                </th>
                                <th class="p-4 min-w-[120px] cursor-pointer select-none" @click="toggleSort('nisn')">
                                    <span class="inline-flex items-center gap-1">
                                        NISN
                                        <component :is="sortIcon('nisn')" class="w-3.5 h-3.5" />
                                    </span>
                                </th>
                                <th v-for="m in mapels" :key="m.id"
                                    class="p-3 min-w-[100px] text-center cursor-pointer select-none" @click="toggleSort(String(m.id))">
                                    <span class="inline-flex items-center gap-1 justify-center">
                                        {{ m.nama }}
                                        <component :is="sortIcon(String(m.id))" class="w-3.5 h-3.5 flex-shrink-0" />
                                    </span>
                                </th>
                                <th class="p-4 min-w-[90px] text-center cursor-pointer select-none" @click="toggleSort('rata_rata')">
                                    <span class="inline-flex items-center gap-1 justify-center">
                                        Rata-rata
                                        <component :is="sortIcon('rata_rata')" class="w-3.5 h-3.5" />
                                    </span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="(row, i) in sortedRows" :key="row.siswa_id"
                                class="hover:bg-slate-50/50 transition-colors">
                                <td class="p-4 pl-6 text-center text-xs font-bold text-slate-400">{{ i + 1 }}</td>
                                <td class="p-4">
                                    <p class="font-bold text-slate-900">{{ row.nama }}</p>
                                </td>
                                <td class="p-4">
                                    <p class="text-sm font-medium text-slate-500">{{ row.nisn }}</p>
                                </td>
                                <td v-for="m in mapels" :key="m.id" class="p-3 text-center">
                                    <template v-if="row.nilai[m.id] !== null && row.nilai[m.id] !== undefined">
                                        <span class="text-sm font-bold"
                                            :class="row.nilai[m.id] >= 75 ? 'text-emerald-600' : 'text-rose-600'">
                                            {{ row.nilai[m.id] }}
                                        </span>
                                    </template>
                                    <span v-else class="text-slate-300 text-xs">&mdash;</span>
                                </td>
                                <td class="p-4 text-center">
                                    <template v-if="row.rata_rata !== null">
                                        <span class="text-sm font-black"
                                            :class="row.rata_rata >= 75 ? 'text-emerald-600' : 'text-rose-600'">
                                            {{ row.rata_rata }}
                                        </span>
                                    </template>
                                    <span v-else class="text-slate-300 text-xs">&mdash;</span>
                                </td>
                            </tr>
                            <tr v-if="sortedRows.length === 0">
                                <td :colspan="3 + mapels.length + 1" class="p-8 text-center text-slate-500 font-medium">
                                    Belum ada data nilai untuk rombel ini.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
