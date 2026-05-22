<script setup>
import AdminWebLayout from '@/Layouts/AdminWebLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ berita: Object });

const confirmDelete = (id) => {
    if (confirm('Yakin hapus berita ini?')) {
        router.delete(route('admin.web.berita.destroy', id));
    }
};
</script>

<template>
    <AdminWebLayout>
        <Head title="Manajemen Berita" />

        <div class="space-y-5">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-black text-slate-900">Berita & Artikel</h1>
                    <p class="text-slate-500 text-sm">Kelola konten berita website sekolah</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('admin.web.kategori-berita.index')" class="text-sm font-semibold text-blue-600 hover:underline">
                        Kelola Kategori
                    </Link>
                    <Link :href="route('admin.web.berita.create')" class="bg-blue-600 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-blue-700 transition-all active:scale-95 shadow-sm">
                        + Tulis Berita
                    </Link>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="text-left px-5 py-3 font-semibold text-slate-600">Judul</th>
                            <th class="text-left px-5 py-3 font-semibold text-slate-600 hidden md:table-cell">Kategori</th>
                            <th class="text-left px-5 py-3 font-semibold text-slate-600 hidden lg:table-cell">Penulis</th>
                            <th class="text-left px-5 py-3 font-semibold text-slate-600">Status</th>
                            <th class="text-left px-5 py-3 font-semibold text-slate-600 hidden md:table-cell">Tanggal</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="b in berita.data" :key="b.id" class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-3 font-medium text-slate-900 max-w-xs truncate">{{ b.judul }}</td>
                            <td class="px-5 py-3 text-slate-500 hidden md:table-cell">
                                <span class="bg-slate-100 text-slate-600 text-[10px] font-bold px-2 py-0.5 rounded uppercase">
                                    {{ b.kategori?.nama || 'Tanpa Kategori' }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-slate-500 hidden lg:table-cell">{{ b.author?.name ?? '-' }}</td>
                            <td class="px-5 py-3">
                                <span :class="['text-xs font-semibold px-2 py-1 rounded-full', b.status === 'published' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700']">
                                    {{ b.status === 'published' ? 'Publik' : 'Draft' }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-slate-400 hidden md:table-cell text-xs">
                                {{ new Date(b.created_at).toLocaleDateString('id-ID') }}
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Link :href="route('admin.web.berita.edit', b.hashid)" class="text-xs text-blue-600 hover:underline font-medium">Edit</Link>
                                    <button @click="confirmDelete(b.id)" class="text-xs text-red-500 hover:underline font-medium">Hapus</button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!berita.data.length">
                            <td colspan="6" class="px-5 py-10 text-center text-slate-400">Belum ada berita. <Link :href="route('admin.web.berita.create')" class="text-blue-600 hover:underline">Tulis sekarang</Link></td>
                        </tr>
                    </tbody>
                </table>
                <!-- Pagination -->
                <div v-if="berita.last_page > 1" class="flex items-center justify-between px-5 py-3 border-t border-slate-100">
                    <p class="text-xs text-slate-500">{{ berita.from }}-{{ berita.to }} dari {{ berita.total }}</p>
                    <div class="flex gap-1">
                        <Link v-if="berita.prev_page_url" :href="berita.prev_page_url" class="px-3 py-1 text-xs rounded-lg border border-slate-200 hover:bg-slate-50">‹ Prev</Link>
                        <Link v-if="berita.next_page_url" :href="berita.next_page_url" class="px-3 py-1 text-xs rounded-lg border border-slate-200 hover:bg-slate-50">Next ›</Link>
                    </div>
                </div>
            </div>
        </div>
    </AdminWebLayout>
</template>
