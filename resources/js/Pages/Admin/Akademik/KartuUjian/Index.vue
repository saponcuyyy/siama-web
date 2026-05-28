<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Printer, GraduationCap, Users, Search } from 'lucide-vue-next';

const props = defineProps({
    rombelList: Object,
});
</script>

<template>
    <Head title="Kartu Ujian" />
    <AuthenticatedLayout>
        <div class="max-w-5xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 flex items-center gap-2">
                        <Printer class="w-7 h-7 text-amber-600" />
                        Cetak Kartu Ujian
                    </h1>
                    <p class="text-slate-500 font-medium mt-1">Pilih rombel untuk mencetak kartu ujian siswa.</p>
                </div>
            </div>

            <div v-if="rombelList.data.length === 0" class="bg-white rounded-3xl border border-slate-200 p-12 text-center">
                <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4 text-slate-400">
                    <GraduationCap class="w-8 h-8" />
                </div>
                <h3 class="text-slate-900 font-bold">Belum Ada Data Rombel</h3>
                <p class="text-slate-500 text-sm">Tambahkan data rombongan belajar terlebih dahulu.</p>
            </div>

            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="rombel in rombelList.data" :key="rombel.id"
                    class="bg-white rounded-3xl border border-slate-200 hover:border-amber-300 hover:shadow-md transition-all p-5">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl flex items-center justify-center text-white font-black shadow-lg shadow-amber-200">
                            {{ rombel.tingkat }}
                        </div>
                    </div>
                    <h3 class="text-lg font-black text-slate-900 mb-1">{{ rombel.nama }}</h3>
                    <p class="text-sm text-indigo-600 font-bold mb-1">{{ rombel.tahun_ajaran?.nama }}</p>
                    <p class="text-xs text-slate-500 font-medium mb-4 flex items-center gap-1.5">
                        <Users class="w-3.5 h-3.5" />
                        {{ rombel.siswa_count }} siswa aktif
                    </p>
                    <a :href="route('admin.web.rombel.kartu-ujian', rombel.hashid)" target="_blank"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold rounded-xl transition-colors shadow-lg shadow-amber-200">
                        <Printer class="w-4 h-4" />
                        Cetak Kartu
                    </a>
                </div>
            </div>

            <div v-if="rombelList.links.length > 3" class="flex justify-center">
                <div class="flex flex-wrap gap-1">
                    <template v-for="(link, k) in rombelList.links" :key="k">
                        <Link v-if="link.url" :href="link.url"
                            class="px-3 py-1 rounded-lg text-sm font-medium transition-colors"
                            :class="link.active ? 'bg-indigo-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                            v-html="link.label" />
                        <span v-else class="px-3 py-1 text-sm text-slate-400" v-html="link.label" />
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
