<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Dashboard" />

        <div class="space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 hover:shadow-md transition-shadow">
                    <h3 class="text-slate-500 text-sm font-semibold uppercase tracking-wider mb-2">Total Siswa</h3>
                    <p class="text-3xl font-bold text-slate-900">1,248</p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 hover:shadow-md transition-shadow">
                    <h3 class="text-slate-500 text-sm font-semibold uppercase tracking-wider mb-2">Total Guru</h3>
                    <p class="text-3xl font-bold text-slate-900">84</p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 hover:shadow-md transition-shadow">
                    <h3 class="text-slate-500 text-sm font-semibold uppercase tracking-wider mb-2">Rombel Aktif</h3>
                    <p class="text-3xl font-bold text-slate-900">36</p>
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
