<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    stats: {
        type: Object,
        default: () => ({
            total_siswa: 0,
            total_guru: 0,
            total_rombel: 0
        })
    }
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Dashboard" />

        <div class="space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 hover:shadow-md transition-shadow">
                    <h3 class="text-slate-500 text-sm font-semibold uppercase tracking-wider mb-2">Total Siswa</h3>
                    <p class="text-3xl font-bold text-slate-900">{{ stats.total_siswa.toLocaleString('id-ID') }}</p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 hover:shadow-md transition-shadow">
                    <h3 class="text-slate-500 text-sm font-semibold uppercase tracking-wider mb-2">Total Guru</h3>
                    <p class="text-3xl font-bold text-slate-900">{{ stats.total_guru.toLocaleString('id-ID') }}</p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 hover:shadow-md transition-shadow">
                    <h3 class="text-slate-500 text-sm font-semibold uppercase tracking-wider mb-2">Rombel Aktif</h3>
                    <p class="text-3xl font-bold text-slate-900">{{ stats.total_rombel.toLocaleString('id-ID') }}</p>
                </div>
            </div>
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-slate-200">
                <div class="p-6 text-slate-900">
                    Selamat Datang di Sistem Informasi Akademik (SIAMA). Fondasi sistem telah berhasil dikonfigurasi.
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
