<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Library, Clock, Bell, Construction, BookOpen, BookMarked, Users, CreditCard, FileSpreadsheet, History } from 'lucide-vue-next';
</script>

<template>
    <Head title="Perpustakaan" />

    <AuthenticatedLayout>
        <div class="max-w-5xl mx-auto space-y-8 py-4">

            <!-- Hero -->
            <div class="relative overflow-hidden bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-800 rounded-3xl shadow-2xl shadow-indigo-200/50">
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_rgba(255,255,255,0.12)_0%,_transparent_60%)]" />
                <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-white/5 blur-3xl" />
                <div class="absolute -bottom-16 -left-16 w-64 h-64 rounded-full bg-white/5 blur-2xl" />
                <div class="relative z-10 p-10 sm:p-14 text-center">
                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-white/15 backdrop-blur-sm mb-6 ring-1 ring-white/20">
                        <Library class="w-10 h-10 text-white" />
                    </div>
                    <h1 class="text-4xl sm:text-5xl font-black text-white tracking-tight mb-3">
                        Perpustakaan Digital
                    </h1>
                    <p class="text-indigo-200 text-lg sm:text-xl font-medium max-w-xl mx-auto">
                        Fitur akan segera hadir. Kami sedang mempersiapkan sistem manajemen perpustakaan sekolah yang modern dan mudah diakses.
                    </p>
                </div>
            </div>

            <!-- Status Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-amber-50 mb-5 ring-8 ring-amber-50/50">
                    <Construction class="w-8 h-8 text-amber-500" />
                </div>
                <h2 class="text-2xl font-black text-slate-900 mb-2">Dalam Pengembangan</h2>
                <p class="text-slate-500 max-w-lg mx-auto leading-relaxed">
                    Fitur Manajemen Perpustakaan saat ini masih dalam tahap pengerjaan. 
                    Kami sedang merancang sistem katalog buku digital, pelacakan peminjaman, 
                    serta integrasi kartu anggota digital untuk siswa dan guru.
                </p>
                <div class="mt-6 inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 text-indigo-700 rounded-xl text-sm font-bold">
                    <Clock class="w-4 h-4" />
                    Estimasi rilis: <span class="text-indigo-900">Tahun Ajaran 2026/2027</span>
                </div>
            </div>

            <!-- Feature Preview -->
            <div>
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-1 h-7 bg-indigo-500 rounded-full" />
                    <h3 class="text-lg font-black text-slate-900">Fitur yang akan tersedia</h3>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 hover:shadow-md hover:border-indigo-200 transition-all group">
                        <div class="w-11 h-11 rounded-xl bg-indigo-50 flex items-center justify-center mb-4 group-hover:bg-indigo-100 transition-colors">
                            <BookOpen class="w-5 h-5 text-indigo-600" />
                        </div>
                        <h4 class="font-bold text-slate-900 mb-1">Katalog Buku Digital</h4>
                        <p class="text-sm text-slate-500 leading-relaxed">Kelola dan telusuri inventaris buku cetak maupun e-book sekolah secara sistematis.</p>
                    </div>
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 hover:shadow-md hover:border-indigo-200 transition-all group">
                        <div class="w-11 h-11 rounded-xl bg-indigo-50 flex items-center justify-center mb-4 group-hover:bg-indigo-100 transition-colors">
                            <BookMarked class="w-5 h-5 text-indigo-600" />
                        </div>
                        <h4 class="font-bold text-slate-900 mb-1">Peminjaman & Pengembalian</h4>
                        <p class="text-sm text-slate-500 leading-relaxed">Pencatatan sirkulasi buku masuk dan keluar secara otomatis menggunakan scan barcode/QR code.</p>
                    </div>
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 hover:shadow-md hover:border-indigo-200 transition-all group">
                        <div class="w-11 h-11 rounded-xl bg-indigo-50 flex items-center justify-center mb-4 group-hover:bg-indigo-100 transition-colors">
                            <CreditCard class="w-5 h-5 text-indigo-600" />
                        </div>
                        <h4 class="font-bold text-slate-900 mb-1">Kartu Anggota Digital</h4>
                        <p class="text-sm text-slate-500 leading-relaxed">Kartu akses perpustakaan bagi siswa dan guru langsung dari portal masing-masing.</p>
                    </div>
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 hover:shadow-md hover:border-indigo-200 transition-all group">
                        <div class="w-11 h-11 rounded-xl bg-indigo-50 flex items-center justify-center mb-4 group-hover:bg-indigo-100 transition-colors">
                            <History class="w-5 h-5 text-indigo-600" />
                        </div>
                        <h4 class="font-bold text-slate-900 mb-1">Manajemen Denda</h4>
                        <p class="text-sm text-slate-500 leading-relaxed">Perhitungan otomatis masa denda keterlambatan pengembalian buku secara real-time.</p>
                    </div>
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 hover:shadow-md hover:border-indigo-200 transition-all group">
                        <div class="w-11 h-11 rounded-xl bg-indigo-50 flex items-center justify-center mb-4 group-hover:bg-indigo-100 transition-colors">
                            <FileSpreadsheet class="w-5 h-5 text-indigo-600" />
                        </div>
                        <h4 class="font-bold text-slate-900 mb-1">Laporan & Statistik</h4>
                        <p class="text-sm text-slate-500 leading-relaxed">Pantau grafik buku terpopuler, keaktifan pengunjung, serta rekap peminjaman bulanan.</p>
                    </div>
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 hover:shadow-md hover:border-indigo-200 transition-all group">
                        <div class="w-11 h-11 rounded-xl bg-indigo-50 flex items-center justify-center mb-4 group-hover:bg-indigo-100 transition-colors">
                            <Users class="w-5 h-5 text-indigo-600" />
                        </div>
                        <h4 class="font-bold text-slate-900 mb-1">Manajemen Anggota</h4>
                        <p class="text-sm text-slate-500 leading-relaxed">Sinkronisasi data keanggotaan langsung dengan database siswa dan staf sekolah.</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
