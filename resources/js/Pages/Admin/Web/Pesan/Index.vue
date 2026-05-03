<script setup>
import AdminWebLayout from '@/Layouts/AdminWebLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({ pesan: Object });

const confirmDelete = (id) => {
    if (confirm('Yakin hapus pesan ini?')) {
        router.delete(route('admin.web.pesan.destroy', id));
    }
};
</script>

<template>
    <AdminWebLayout>
        <Head title="Pesan Masuk" />

        <div class="space-y-5">
            <!-- Header -->
            <div>
                <h1 class="text-xl font-black text-slate-900">Pesan Masuk</h1>
                <p class="text-slate-500 text-sm">Kotak masuk dari formulir kontak website</p>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="text-left px-5 py-3 font-semibold text-slate-600">Pengirim</th>
                            <th class="text-left px-5 py-3 font-semibold text-slate-600 hidden md:table-cell">Subjek</th>
                            <th class="text-left px-5 py-3 font-semibold text-slate-600">Status</th>
                            <th class="text-left px-5 py-3 font-semibold text-slate-600 hidden lg:table-cell">Tanggal</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="p in pesan.data" :key="p.id" :class="['transition-colors', p.status === 'belum_dibaca' ? 'bg-blue-50/50 hover:bg-blue-50' : 'hover:bg-slate-50']">
                            <td class="px-5 py-3">
                                <p :class="['font-medium text-slate-900', {'font-bold': p.status === 'belum_dibaca'}]">{{ p.nama }}</p>
                                <p class="text-xs text-slate-500">{{ p.email }}</p>
                            </td>
                            <td class="px-5 py-3 text-slate-700 hidden md:table-cell max-w-xs truncate" :class="{'font-semibold text-slate-900': p.status === 'belum_dibaca'}">
                                {{ p.subjek }}
                            </td>
                            <td class="px-5 py-3">
                                <span :class="['text-xs font-semibold px-2 py-1 rounded-full', 
                                    p.status === 'belum_dibaca' ? 'bg-red-100 text-red-700' : 
                                    p.status === 'dibalas' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600']">
                                    {{ p.status.replace('_', ' ') }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-slate-400 hidden lg:table-cell text-xs">
                                {{ new Date(p.created_at).toLocaleString('id-ID') }}
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Link :href="route('admin.web.pesan.show', p.id)" class="text-xs text-blue-600 hover:underline font-medium">Baca</Link>
                                    <button @click="confirmDelete(p.id)" class="text-xs text-red-500 hover:underline font-medium">Hapus</button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!pesan.data.length">
                            <td colspan="5" class="px-5 py-10 text-center text-slate-400">Tidak ada pesan masuk.</td>
                        </tr>
                    </tbody>
                </table>
                <!-- Pagination -->
                <div v-if="pesan.last_page > 1" class="flex items-center justify-between px-5 py-3 border-t border-slate-100">
                    <p class="text-xs text-slate-500">{{ pesan.from }}-{{ pesan.to }} dari {{ pesan.total }}</p>
                    <div class="flex gap-1">
                        <Link v-if="pesan.prev_page_url" :href="pesan.prev_page_url" class="px-3 py-1 text-xs rounded-lg border border-slate-200 hover:bg-slate-50">‹ Prev</Link>
                        <Link v-if="pesan.next_page_url" :href="pesan.next_page_url" class="px-3 py-1 text-xs rounded-lg border border-slate-200 hover:bg-slate-50">Next ›</Link>
                    </div>
                </div>
            </div>
        </div>
    </AdminWebLayout>
</template>
