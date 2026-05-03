<script setup>
import { Head, Link, router } from '@inertiajs/vue3';

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <Head title="Dashboard" />

    <div class="min-h-screen bg-slate-50">
        <nav class="bg-white border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center gap-2">
                            <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center shadow-lg shadow-indigo-100">
                                <span class="text-white font-bold text-sm">S</span>
                            </div>
                            <span class="font-bold text-slate-900">SIAMA</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="text-sm text-slate-600">Selamat datang, <strong>Super Admin</strong></span>
                        <button @click="logout" class="text-sm font-semibold text-slate-500 hover:text-red-600 transition-colors">
                            Keluar
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <h1 class="text-2xl font-bold leading-tight text-slate-900">Dashboard Utama</h1>
            </div>
        </header>

        <main class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
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
            
            <div class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-slate-200">
                <div class="p-6 text-slate-900">
                    Sistem Akademik SMA - Fondasi Berhasil Dibangun!
                </div>
            </div>
        </main>
    </div>
</template>
