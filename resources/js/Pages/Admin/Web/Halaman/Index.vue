<script setup>
import AdminWebLayout from '@/Layouts/AdminWebLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({ pages: Object });

const confirmDelete = (id) => {
    if (confirm('Yakin hapus halaman statis ini?')) {
        router.delete(route('admin.web.halaman.destroy', id));
    }
};
</script>

<template>
    <AdminWebLayout>
        <Head title="Manajemen Halaman Statis" />

        <div class="space-y-5">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-black text-slate-900">Halaman Statis</h1>
                    <p class="text-slate-500 text-sm">Kelola konten halaman statis seperti Profil, Visi Misi, dll.</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('admin.web.halaman.create')" class="bg-blue-600 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-blue-700 transition-all active:scale-95 shadow-sm">
                        + Buat Halaman
                    </Link>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="text-left px-5 py-3 font-semibold text-slate-600">Judul</th>
                            <th class="text-left px-5 py-3 font-semibold text-slate-600 hidden md:table-cell">Slug</th>
                            <th class="text-left px-5 py-3 font-semibold text-slate-600 hidden lg:table-cell">Penulis</th>
                            <th class="text-left px-5 py-3 font-semibold text-slate-600">Status</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="page in pages.data" :key="page.id" class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-3 font-medium text-slate-900 max-w-xs truncate">{{ page.title }}</td>
                            <td class="px-5 py-3 text-slate-500 hidden md:table-cell font-mono text-xs">/{{ page.slug }}</td>
                            <td class="px-5 py-3 text-slate-500 hidden lg:table-cell">{{ page.author?.name ?? '-' }}</td>
                            <td class="px-5 py-3">
                                <span :class="['text-xs font-semibold px-2 py-1 rounded-full', page.status === 'published' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700']">
                                    {{ page.status === 'published' ? 'Publik' : 'Draft' }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Link :href="route('admin.web.halaman.edit', page.id)" class="text-xs text-blue-600 hover:underline font-medium">Edit</Link>
                                    <button @click="confirmDelete(page.id)" class="text-xs text-red-500 hover:underline font-medium">Hapus</button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!pages.data.length">
                            <td colspan="5" class="px-5 py-10 text-center text-slate-400">Belum ada halaman. <Link :href="route('admin.web.halaman.create')" class="text-blue-600 hover:underline">Buat sekarang</Link></td>
                        </tr>
                    </tbody>
                </table>
                <!-- Pagination -->
                <div v-if="pages.last_page > 1" class="flex items-center justify-between px-5 py-3 border-t border-slate-100">
                    <p class="text-xs text-slate-500">{{ pages.from }}-{{ pages.to }} dari {{ pages.total }}</p>
                    <div class="flex gap-1">
                        <Link v-if="pages.prev_page_url" :href="pages.prev_page_url" class="px-3 py-1 text-xs rounded-lg border border-slate-200 hover:bg-slate-50">‹ Prev</Link>
                        <Link v-if="pages.next_page_url" :href="pages.next_page_url" class="px-3 py-1 text-xs rounded-lg border border-slate-200 hover:bg-slate-50">Next ›</Link>
                    </div>
                </div>
            </div>
        </div>
    </AdminWebLayout>
</template>
