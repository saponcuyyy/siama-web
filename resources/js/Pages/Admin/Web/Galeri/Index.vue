<script setup>
import AdminWebLayout from '@/Layouts/AdminWebLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({ galeri: Object });

const confirmDelete = (id) => {
    if (confirm('Yakin hapus foto ini dari galeri?')) {
        router.delete(route('admin.web.galeri.destroy', id));
    }
};
</script>

<template>
    <AdminWebLayout>
        <Head title="Manajemen Galeri" />

        <div class="space-y-5">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-black text-slate-900">Galeri Foto</h1>
                    <p class="text-slate-500 text-sm">Kelola dokumentasi foto kegiatan sekolah</p>
                </div>
                <Link :href="route('admin.web.galeri.create')" class="bg-purple-600 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-purple-700 transition-all active:scale-95 shadow-sm">
                    + Upload Foto
                </Link>
            </div>

            <!-- Grid -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <div v-for="g in galeri.data" :key="g.id" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden group">
                    <div class="aspect-video bg-slate-100 relative overflow-hidden">
                        <!-- Nanti diganti img tag: <img :src="g.file_path" class="w-full h-full object-cover"> -->
                        <div class="absolute inset-0 flex items-center justify-center text-slate-300 text-4xl">📷</div>
                        
                        <!-- Overlay actions -->
                        <div class="absolute inset-0 bg-slate-900/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                            <Link :href="route('admin.web.galeri.edit', g.id)" class="w-8 h-8 bg-white rounded-full flex items-center justify-center text-blue-600 hover:scale-110 transition-transform">✏️</Link>
                            <button @click="confirmDelete(g.id)" class="w-8 h-8 bg-white rounded-full flex items-center justify-center text-red-600 hover:scale-110 transition-transform">🗑️</button>
                        </div>
                    </div>
                    <div class="p-3">
                        <p class="font-bold text-slate-900 text-sm truncate">{{ g.judul }}</p>
                        <div class="flex items-center justify-between mt-1">
                            <p class="text-xs text-slate-500">{{ g.kategori }}</p>
                            <span :class="['text-[10px] font-bold px-1.5 py-0.5 rounded', g.status === 'aktif' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500']">
                                {{ g.status === 'aktif' ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="!galeri.data.length" class="col-span-full bg-white rounded-2xl border border-slate-200 py-12 text-center">
                    <span class="text-4xl">📸</span>
                    <p class="text-slate-500 font-medium mt-3">Belum ada foto di galeri</p>
                    <Link :href="route('admin.web.galeri.create')" class="text-blue-600 hover:underline text-sm font-semibold mt-1 inline-block">Upload foto pertama</Link>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="galeri.last_page > 1" class="flex items-center justify-between px-1 py-3">
                <p class="text-xs text-slate-500">{{ galeri.from }}-{{ galeri.to }} dari {{ galeri.total }}</p>
                <div class="flex gap-1">
                    <Link v-if="galeri.prev_page_url" :href="galeri.prev_page_url" class="px-3 py-1 text-xs bg-white rounded-lg border border-slate-200 hover:bg-slate-50">‹ Prev</Link>
                    <Link v-if="galeri.next_page_url" :href="galeri.next_page_url" class="px-3 py-1 text-xs bg-white rounded-lg border border-slate-200 hover:bg-slate-50">Next ›</Link>
                </div>
            </div>
        </div>
    </AdminWebLayout>
</template>
