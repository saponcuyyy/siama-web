<script setup>
import AdminWebLayout from '@/Layouts/AdminWebLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ pengumuman: Object });

const confirmDelete = (id) => {
    if (confirm('Yakin hapus pengumuman ini?')) {
        router.delete(route('admin.web.pengumuman.destroy', id));
    }
};
</script>

<template>
    <AdminWebLayout>
        <Head title="Manajemen Pengumuman" />

        <div class="space-y-5">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-black text-slate-900">Pengumuman</h1>
                    <p class="text-slate-500 text-sm">Kelola pengumuman resmi sekolah</p>
                </div>
                <Link :href="route('admin.web.pengumuman.create')" class="bg-green-600 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-green-700 transition-all active:scale-95 shadow-sm">
                    + Tambah Pengumuman
                </Link>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="text-left px-5 py-3 font-semibold text-slate-600">Judul Pengumuman</th>
                            <th class="text-left px-5 py-3 font-semibold text-slate-600 hidden md:table-cell">Prioritas</th>
                            <th class="text-left px-5 py-3 font-semibold text-slate-600 hidden lg:table-cell">Masa Aktif</th>
                            <th class="text-left px-5 py-3 font-semibold text-slate-600">Status</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="p in pengumuman.data" :key="p.id" class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-3 font-medium text-slate-900 max-w-sm truncate">{{ p.judul }}</td>
                            <td class="px-5 py-3 text-slate-500 hidden md:table-cell">
                                <span :class="['text-xs font-semibold px-2 py-1 rounded-full', 
                                    p.prioritas === 'tinggi' ? 'bg-red-100 text-red-700' : 
                                    p.prioritas === 'normal' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-700']">
                                    {{ p.prioritas.charAt(0).toUpperCase() + p.prioritas.slice(1) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-slate-500 hidden lg:table-cell text-xs">
                                {{ p.tanggal_mulai ? new Date(p.tanggal_mulai).toLocaleDateString('id-ID') : 'Selamanya' }}
                                {{ p.tanggal_selesai ? ' - ' + new Date(p.tanggal_selesai).toLocaleDateString('id-ID') : '' }}
                            </td>
                            <td class="px-5 py-3">
                                <span :class="['text-xs font-semibold px-2 py-1 rounded-full', p.status === 'aktif' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500']">
                                    {{ p.status === 'aktif' ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Link :href="route('admin.web.pengumuman.edit', p.hashid)" class="text-xs text-blue-600 hover:underline font-medium">Edit</Link>
                                    <button @click="confirmDelete(p.id)" class="text-xs text-red-500 hover:underline font-medium">Hapus</button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!pengumuman.data.length">
                            <td colspan="5" class="px-5 py-10 text-center text-slate-400">Belum ada pengumuman. <Link :href="route('admin.web.pengumuman.create')" class="text-green-600 hover:underline">Tambah sekarang</Link></td>
                        </tr>
                    </tbody>
                </table>
                <!-- Pagination -->
                <div v-if="pengumuman.last_page > 1" class="flex items-center justify-between px-5 py-3 border-t border-slate-100">
                    <p class="text-xs text-slate-500">{{ pengumuman.from }}-{{ pengumuman.to }} dari {{ pengumuman.total }}</p>
                    <div class="flex gap-1">
                        <Link v-if="pengumuman.prev_page_url" :href="pengumuman.prev_page_url" class="px-3 py-1 text-xs rounded-lg border border-slate-200 hover:bg-slate-50">‹ Prev</Link>
                        <Link v-if="pengumuman.next_page_url" :href="pengumuman.next_page_url" class="px-3 py-1 text-xs rounded-lg border border-slate-200 hover:bg-slate-50">Next ›</Link>
                    </div>
                </div>
            </div>
        </div>
    </AdminWebLayout>
</template>
