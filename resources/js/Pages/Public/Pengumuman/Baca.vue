<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { ChevronLeft, FileText, Table2, FileDown, Calendar, AlertTriangle, Clock } from 'lucide-vue-next';

const props = defineProps({
    pengumuman: Object,
    settings: Object,
    tables: Array,
});

const hasTables = computed(() => props.tables && props.tables.length > 0);
</script>

<template>
    <GuestLayout :settings="settings">
        <Head :title="pengumuman.judul" />

        <section class="pt-28 pb-16 bg-slate-50 min-h-screen">
            <div class="max-w-5xl mx-auto px-4 sm:px-6">
                <!-- Breadcrumb -->
                <div class="mb-8">
                    <nav class="flex items-center gap-2 text-sm text-slate-400">
                        <Link href="/" class="hover:text-blue-600 font-medium transition-colors">Beranda</Link>
                        <span class="text-slate-300">/</span>
                        <Link :href="route('public.pengumuman.index')" class="hover:text-blue-600 font-medium transition-colors">Pengumuman</Link>
                        <span class="text-slate-300">/</span>
                        <span class="text-slate-600 font-semibold truncate max-w-[200px]">{{ pengumuman.judul }}</span>
                    </nav>
                </div>

                <!-- Main Card -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm mb-6 overflow-hidden">
                    <div :class="['h-1.5',
                        pengumuman.prioritas === 'tinggi' ? 'bg-red-500' :
                        pengumuman.prioritas === 'normal' ? 'bg-blue-600' : 'bg-slate-300'
                    ]"></div>

                    <div class="p-6 md:p-8">
                        <!-- Meta badges -->
                        <div class="flex flex-wrap items-center gap-3 mb-4">
                            <span :class="[
                                'inline-flex items-center gap-1 text-[10px] font-black px-2.5 py-1 rounded-lg uppercase tracking-wider border',
                                pengumuman.prioritas === 'tinggi' ? 'bg-red-50 text-red-600 border-red-200' :
                                pengumuman.prioritas === 'normal' ? 'bg-blue-50 text-blue-600 border-blue-200' :
                                'bg-slate-50 text-slate-600 border-slate-200'
                            ]">
                                <AlertTriangle v-if="pengumuman.prioritas === 'tinggi'" class="w-3 h-3" />
                                <Clock v-else class="w-3 h-3" />
                                {{ pengumuman.prioritas }}
                            </span>
                            <span class="inline-flex items-center gap-1.5 text-xs text-slate-400 font-medium">
                                <Calendar class="w-3.5 h-3.5" />
                                {{ new Date(pengumuman.created_at).toLocaleDateString('id-ID', {day:'numeric', month:'long', year:'numeric'}) }}
                            </span>
                        </div>

                        <h1 class="text-xl md:text-3xl font-black text-slate-900 leading-tight mb-4">
                            {{ pengumuman.judul }}
                        </h1>

                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-blue-50 text-blue-600 font-bold flex items-center justify-center text-xs ring-2 ring-blue-100">
                                {{ pengumuman.author?.name ? pengumuman.author.name.charAt(0).toUpperCase() : 'A' }}
                            </div>
                            <div>
                                <p class="font-semibold text-slate-700 text-xs">Diterbitkan oleh</p>
                                <p class="text-sm font-medium text-slate-500">{{ pengumuman.author?.name || 'Administrator' }}</p>
                            </div>
                        </div>

                        <div v-if="pengumuman.konten && pengumuman.konten !== '-'" class="mt-6 pt-6 border-t border-slate-100">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">Ringkasan</p>
                            <div class="prose prose-sm md:prose-base prose-slate max-w-none text-slate-600 leading-relaxed" v-html="pengumuman.konten"></div>
                        </div>
                    </div>
                </div>

                <!-- Tables -->
                <div v-if="hasTables" class="space-y-6">
                    <div
                        v-for="(table, ti) in tables"
                        :key="ti"
                        class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden"
                    >
                        <div class="flex items-center gap-2 px-6 py-4 bg-slate-50/80 border-b border-slate-200">
                            <Table2 class="w-5 h-5 text-slate-500" />
                            <h2 class="font-bold text-slate-700 text-sm uppercase tracking-wider">
                                {{ tables.length > 1 ? `Tabel ${ti + 1}` : 'Data' }}
                            </h2>
                        </div>
                        <div class="p-4 sm:p-6">
                            <DataTable
                                :columns="table.columns"
                                :rows="table.rows"
                                :title="tables.length > 1 ? `Tabel ${ti + 1}` : ''"
                            />
                        </div>
                    </div>
                </div>

                <!-- Document fallback -->
                <div v-else class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 bg-slate-50/80 border-b border-slate-200">
                        <div class="flex items-center gap-2">
                            <FileText class="w-5 h-5 text-slate-500" />
                            <h2 class="font-bold text-slate-700 text-sm uppercase tracking-wider">
                                Dokumen Lampiran
                            </h2>
                        </div>
                        <a
                            :href="route('public.pengumuman.pdf', pengumuman.hashid)"
                            target="_blank"
                            class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:text-blue-700 hover:underline transition-colors"
                        >
                            <FileDown class="w-4 h-4" />
                            Unduh PDF
                        </a>
                    </div>
                    <iframe
                        :src="route('public.pengumuman.embed', pengumuman.hashid)"
                        class="w-full"
                        style="height: 70vh; min-height: 400px;"
                        frameborder="0"
                    ></iframe>
                </div>

                <!-- Back link -->
                <div class="mt-8">
                    <Link
                        :href="route('public.pengumuman.index')"
                        class="inline-flex items-center gap-2 text-sm font-semibold text-slate-400 hover:text-blue-600 transition-colors"
                    >
                        <ChevronLeft class="w-4 h-4" />
                        Kembali ke Pengumuman
                    </Link>
                </div>
            </div>
        </section>
    </GuestLayout>
</template>
