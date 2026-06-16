<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    History, Search, ChevronDown, ChevronRight, Shield,
    Plus, Pencil, Trash2, X, RefreshCw, Filter, Activity,
    User, Calendar, Globe, Monitor, Clock, Eye
} from 'lucide-vue-next';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import 'dayjs/locale/id';
dayjs.extend(relativeTime);
dayjs.locale('id');

const props = defineProps({
    activities: Object,
    stats: Object,
    filters: Object,
});

// ─── Filters ────────────────────────────────────────────────────────────────
const search = ref(props.filters.search || '');
const event  = ref(props.filters.event  || '');
const from   = ref(props.filters.from   || '');
const to     = ref(props.filters.to     || '');

const applyFilters = () => {
    router.get(route('admin.audit-trail.index'), {
        search: search.value,
        event:  event.value,
        from:   from.value,
        to:     to.value,
    }, { preserveState: true, replace: true });
};

const resetFilters = () => {
    search.value = '';
    event.value  = '';
    from.value   = '';
    to.value     = '';
    applyFilters();
};

const hasFilters = computed(() =>
    search.value || event.value || from.value || to.value
);

// ─── Detail panel ────────────────────────────────────────────────────────────
const expandedId = ref(null);
const toggleExpand = (id) => {
    expandedId.value = expandedId.value === id ? null : id;
};

// ─── Helpers ─────────────────────────────────────────────────────────────────
const EVENT_CONFIG = {
    created: { label: 'Dibuat',  bg: 'bg-emerald-50', text: 'text-emerald-700', border: 'border-emerald-200', icon: Plus,   dot: 'bg-emerald-500' },
    updated: { label: 'Diubah',  bg: 'bg-amber-50',   text: 'text-amber-700',   border: 'border-amber-200',   icon: Pencil, dot: 'bg-amber-400' },
    deleted: { label: 'Dihapus', bg: 'bg-rose-50',    text: 'text-rose-700',    border: 'border-rose-200',    icon: Trash2, dot: 'bg-rose-500' },
};

const getEventCfg = (ev) => EVENT_CONFIG[ev] ?? {
    label: ev, bg: 'bg-slate-50', text: 'text-slate-600', border: 'border-slate-200', icon: Activity, dot: 'bg-slate-400'
};

const formatDate = (iso) => dayjs(iso).format('DD MMM YYYY, HH:mm:ss');
const fromNow    = (iso) => dayjs(iso).fromNow();

const shortAgent = (ua) => {
    if (!ua) return '-';
    if (ua.includes('Chrome'))  return 'Chrome';
    if (ua.includes('Firefox')) return 'Firefox';
    if (ua.includes('Safari'))  return 'Safari';
    if (ua.includes('Edge'))    return 'Edge';
    return ua.slice(0, 30) + '…';
};

const diffKeys = (oldObj, newObj) => {
    const keys = new Set([...Object.keys(oldObj ?? {}), ...Object.keys(newObj ?? {})]);
    return [...keys].filter(k => {
        const o = JSON.stringify((oldObj ?? {})[k]);
        const n = JSON.stringify((newObj ?? {})[k]);
        return o !== n;
    });
};

const formatVal = (val) => {
    if (val === null || val === undefined) return '—';
    if (typeof val === 'boolean') return val ? 'true' : 'false';
    return String(val);
};

// ─── Pagination helper ───────────────────────────────────────────────────────
const visit = (url) => { if (url) router.visit(url); };
</script>

<template>
    <Head title="Audit Trail" />
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto space-y-6">

            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between gap-4 items-start sm:items-center">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                        <History class="w-7 h-7 text-indigo-600" />
                        Audit Trail
                    </h1>
                    <p class="text-slate-500 font-medium mt-1">Rekam jejak seluruh aktivitas perubahan data oleh pengguna.</p>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 flex items-center gap-4">
                    <div class="w-11 h-11 rounded-xl bg-indigo-50 flex items-center justify-center shrink-0">
                        <Activity class="w-5 h-5 text-indigo-600" />
                    </div>
                    <div>
                        <p class="text-2xl font-black text-slate-900">{{ stats.total.toLocaleString() }}</p>
                        <p class="text-xs text-slate-500 font-medium">Total Log</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 flex items-center gap-4">
                    <div class="w-11 h-11 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0">
                        <Plus class="w-5 h-5 text-emerald-600" />
                    </div>
                    <div>
                        <p class="text-2xl font-black text-slate-900">{{ stats.created.toLocaleString() }}</p>
                        <p class="text-xs text-slate-500 font-medium">Dibuat</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 flex items-center gap-4">
                    <div class="w-11 h-11 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
                        <Pencil class="w-5 h-5 text-amber-500" />
                    </div>
                    <div>
                        <p class="text-2xl font-black text-slate-900">{{ stats.updated.toLocaleString() }}</p>
                        <p class="text-xs text-slate-500 font-medium">Diubah</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 flex items-center gap-4">
                    <div class="w-11 h-11 rounded-xl bg-rose-50 flex items-center justify-center shrink-0">
                        <Trash2 class="w-5 h-5 text-rose-500" />
                    </div>
                    <div>
                        <p class="text-2xl font-black text-slate-900">{{ stats.deleted.toLocaleString() }}</p>
                        <p class="text-xs text-slate-500 font-medium">Dihapus</p>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Search -->
                    <div class="relative flex-1 min-w-56">
                        <input
                            type="text" v-model="search" @keyup.enter="applyFilters"
                            placeholder="Cari nama pengguna, model, IP..."
                            class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-indigo-600 focus:border-indigo-600"
                        />
                        <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
                    </div>

                    <!-- Event filter -->
                    <select v-model="event"
                        class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:ring-indigo-600 focus:border-indigo-600">
                        <option value="">Semua Event</option>
                        <option value="created">Dibuat</option>
                        <option value="updated">Diubah</option>
                        <option value="deleted">Dihapus</option>
                    </select>

                    <!-- Date range -->
                    <div class="flex items-center gap-2">
                        <input type="date" v-model="from"
                            class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-indigo-600 focus:border-indigo-600" />
                        <span class="text-slate-400 text-sm font-medium">—</span>
                        <input type="date" v-model="to"
                            class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-indigo-600 focus:border-indigo-600" />
                    </div>

                    <button @click="applyFilters"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl flex items-center gap-2 transition-colors shrink-0">
                        <Filter class="w-4 h-4" /> Filter
                    </button>

                    <button v-if="hasFilters" @click="resetFilters"
                        class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-bold rounded-xl flex items-center gap-2 transition-colors shrink-0">
                        <X class="w-4 h-4" /> Reset
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <!-- Table header bar -->
                <div class="px-6 py-4 bg-slate-50/50 border-b border-slate-200 flex items-center justify-between">
                    <p class="text-sm font-bold text-slate-600">
                        Total: <span class="text-indigo-600">{{ activities.total.toLocaleString() }}</span> log
                    </p>
                    <button @click="applyFilters" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-colors" title="Refresh">
                        <RefreshCw class="w-4 h-4" />
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider">
                                <th class="p-4 pl-6 w-8"></th>
                                <th class="p-4">Event</th>
                                <th class="p-4">Model</th>
                                <th class="p-4">Dilakukan Oleh</th>
                                <th class="p-4">IP Address</th>
                                <th class="p-4">Browser</th>
                                <th class="p-4 pr-6">Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-for="log in activities.data" :key="log.id">
                                <!-- Main row -->
                                <tr
                                    @click="toggleExpand(log.id)"
                                    class="border-b border-slate-100 hover:bg-slate-50/50 transition-colors cursor-pointer"
                                    :class="expandedId === log.id ? 'bg-indigo-50/30' : ''"
                                >
                                    <td class="p-4 pl-6">
                                        <div class="flex items-center justify-center w-6 h-6 rounded-lg text-slate-400 transition-colors"
                                            :class="expandedId === log.id ? 'bg-indigo-100 text-indigo-600' : 'hover:bg-slate-100'">
                                            <ChevronRight class="w-4 h-4 transition-transform duration-200"
                                                :class="expandedId === log.id ? 'rotate-90' : ''" />
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold border"
                                            :class="[getEventCfg(log.event).bg, getEventCfg(log.event).text, getEventCfg(log.event).border]">
                                            <component :is="getEventCfg(log.event).icon" class="w-3 h-3" />
                                            {{ getEventCfg(log.event).label }}
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex items-center gap-2">
                                            <div class="w-2 h-2 rounded-full shrink-0" :class="getEventCfg(log.event).dot"></div>
                                            <span class="font-bold text-slate-800 text-sm">{{ log.model }}</span>
                                            <span class="text-slate-400 text-xs font-mono">#{{ log.subject_id }}</span>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div v-if="log.causer" class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0 text-xs font-black">
                                                {{ log.causer.name.charAt(0).toUpperCase() }}
                                            </div>
                                            <span class="font-semibold text-slate-700 text-sm">{{ log.causer.name }}</span>
                                        </div>
                                        <span v-else class="text-slate-400 text-xs italic">Sistem</span>
                                    </td>
                                    <td class="p-4">
                                        <span class="flex items-center gap-1.5 text-slate-600 text-sm">
                                            <Globe class="w-3.5 h-3.5 text-slate-400" />
                                            {{ log.ip_address || '—' }}
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <span class="text-slate-500 text-sm flex items-center gap-1.5">
                                            <Monitor class="w-3.5 h-3.5 text-slate-400" />
                                            {{ shortAgent(log.user_agent) }}
                                        </span>
                                    </td>
                                    <td class="p-4 pr-6">
                                        <div class="flex flex-col">
                                            <span class="text-slate-700 text-xs font-semibold flex items-center gap-1">
                                                <Clock class="w-3 h-3 text-slate-400" />
                                                {{ fromNow(log.created_at) }}
                                            </span>
                                            <span class="text-slate-400 text-xs mt-0.5">{{ formatDate(log.created_at) }}</span>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Expanded detail -->
                                <tr v-if="expandedId === log.id" class="bg-indigo-50/20 border-b border-slate-100">
                                    <td colspan="7" class="px-8 py-5">
                                        <div class="space-y-4">

                                            <!-- Created: just show attributes -->
                                            <template v-if="log.event === 'created' && log.new">
                                                <div>
                                                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                                                        <Eye class="w-3.5 h-3.5" /> Data yang Dibuat
                                                    </p>
                                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                                                        <div v-for="(val, key) in log.new" :key="key"
                                                            class="bg-white rounded-xl border border-emerald-100 px-4 py-2.5 flex items-start gap-3">
                                                            <span class="text-xs font-bold text-slate-500 w-28 shrink-0 pt-0.5">{{ key }}</span>
                                                            <span class="text-xs text-slate-800 font-medium break-all">{{ formatVal(val) }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>

                                            <!-- Updated: diff view -->
                                            <template v-else-if="log.event === 'updated'">
                                                <div>
                                                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                                                        <Pencil class="w-3.5 h-3.5" /> Perubahan Data
                                                    </p>
                                                    <div v-if="diffKeys(log.old, log.new).length === 0"
                                                        class="text-sm text-slate-400 italic">Tidak ada perubahan yang terdeteksi.</div>
                                                    <div v-else class="space-y-2">
                                                        <div v-for="key in diffKeys(log.old, log.new)" :key="key"
                                                            class="bg-white rounded-xl border border-amber-100 px-4 py-2.5 flex flex-col sm:flex-row gap-2">
                                                            <span class="text-xs font-bold text-slate-500 w-28 shrink-0">{{ key }}</span>
                                                            <div class="flex flex-1 gap-3 flex-wrap">
                                                                <div class="flex-1 min-w-0">
                                                                    <span class="text-[10px] uppercase font-bold text-rose-400 block mb-1">Sebelum</span>
                                                                    <span class="text-xs text-rose-700 bg-rose-50 px-2 py-1 rounded-lg break-all block font-medium">
                                                                        {{ formatVal((log.old ?? {})[key]) }}
                                                                    </span>
                                                                </div>
                                                                <div class="flex-1 min-w-0">
                                                                    <span class="text-[10px] uppercase font-bold text-emerald-500 block mb-1">Sesudah</span>
                                                                    <span class="text-xs text-emerald-700 bg-emerald-50 px-2 py-1 rounded-lg break-all block font-medium">
                                                                        {{ formatVal((log.new ?? {})[key]) }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>

                                            <!-- Deleted -->
                                            <template v-else-if="log.event === 'deleted' && log.new">
                                                <div>
                                                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                                                        <Trash2 class="w-3.5 h-3.5" /> Data yang Dihapus
                                                    </p>
                                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                                                        <div v-for="(val, key) in log.new" :key="key"
                                                            class="bg-white rounded-xl border border-rose-100 px-4 py-2.5 flex items-start gap-3">
                                                            <span class="text-xs font-bold text-slate-500 w-28 shrink-0 pt-0.5">{{ key }}</span>
                                                            <span class="text-xs text-slate-800 font-medium break-all line-through opacity-60">{{ formatVal(val) }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>

                                            <!-- Meta -->
                                            <div class="flex flex-wrap gap-4 pt-2 border-t border-slate-100">
                                                <div class="flex items-center gap-2 text-xs text-slate-500">
                                                    <User class="w-3.5 h-3.5" />
                                                    <span class="font-semibold">Oleh:</span>
                                                    {{ log.causer?.name ?? 'Sistem' }}
                                                </div>
                                                <div class="flex items-center gap-2 text-xs text-slate-500">
                                                    <Globe class="w-3.5 h-3.5" />
                                                    <span class="font-semibold">IP:</span>
                                                    {{ log.ip_address || '—' }}
                                                </div>
                                                <div class="flex items-center gap-2 text-xs text-slate-500">
                                                    <Calendar class="w-3.5 h-3.5" />
                                                    <span class="font-semibold">Waktu:</span>
                                                    {{ formatDate(log.created_at) }}
                                                </div>
                                                <div class="flex items-center gap-2 text-xs text-slate-500">
                                                    <Shield class="w-3.5 h-3.5" />
                                                    <span class="font-semibold">Log ID:</span>
                                                    #{{ log.id }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </template>

                            <!-- Empty -->
                            <tr v-if="activities.data.length === 0">
                                <td colspan="7" class="p-14 text-center">
                                    <div class="w-14 h-14 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4 text-slate-400">
                                        <History class="w-7 h-7" />
                                    </div>
                                    <p class="font-bold text-slate-700">Tidak ada log aktivitas</p>
                                    <p class="text-slate-400 text-sm mt-1">Coba ubah filter pencarian Anda.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 px-6 py-4 border-t border-slate-200 bg-slate-50/50">
                    <p class="text-xs sm:text-sm text-slate-500 font-medium">
                        Menampilkan
                        <span class="font-semibold text-slate-700">{{ activities.from ?? 0 }}</span>–<span class="font-semibold text-slate-700">{{ activities.to ?? 0 }}</span>
                        dari <span class="font-semibold text-slate-700">{{ activities.total }}</span> log
                    </p>
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <template v-for="(link, i) in activities.links" :key="i">
                            <button v-if="link.url"
                                :disabled="link.active"
                                @click="visit(link.url)"
                                class="min-w-[32px] h-8 px-2 flex items-center justify-center text-xs rounded-lg transition-all border font-semibold"
                                :class="link.active
                                    ? 'bg-indigo-600 border-indigo-600 text-white cursor-default'
                                    : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-indigo-600 hover:border-indigo-200'"
                                v-html="link.label" />
                            <span v-else
                                class="min-w-[32px] h-8 px-2 flex items-center justify-center text-xs text-slate-400 border border-transparent"
                                v-html="link.label" />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
