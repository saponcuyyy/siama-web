<script setup>
import AdminWebLayout from '@/Layouts/AdminWebLayout.vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    albums: Object
});

const showModal = ref(false);
const isEditing = ref(null);
const form = useForm({
    nama: '',
    deskripsi: '',
    status: 'aktif',
    cover: null,
    _method: 'POST'
});

const coverPreview = ref(null);

const handleCover = (e) => {
    const file = e.target.files[0];
    form.cover = file;
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => coverPreview.value = e.target.result;
        reader.readAsDataURL(file);
    }
};

const openCreate = () => {
    isEditing.value = null;
    form.reset();
    form._method = 'POST';
    coverPreview.value = null;
    showModal.value = true;
};

const openEdit = (album) => {
    isEditing.value = album;
    form.nama = album.nama;
    form.deskripsi = album.deskripsi || '';
    form.status = album.status;
    form._method = 'PUT';
    coverPreview.value = album.image_url || null;
    showModal.value = true;
};

const submit = () => {
    if (isEditing.value) {
        form.post(route('admin.web.album.update', isEditing.value.id), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            }
        });
    } else {
        form.post(route('admin.web.album.store'), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            }
        });
    }
};

const confirmDelete = (id) => {
    if (confirm('Yakin hapus album ini? Semua foto di dalamnya akan kehilangan kaitan album.')) {
        router.delete(route('admin.web.album.destroy', id));
    }
};
</script>

<template>
    <AdminWebLayout>
        <Head title="Manajemen Album Galeri" />

        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-black text-slate-900">Album Galeri</h1>
                    <p class="text-slate-500 text-sm">Kelola album foto kegiatan sekolah.</p>
                </div>
                <button @click="openCreate" class="bg-purple-600 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-purple-700 transition-all shadow-sm">
                    + Buat Album
                </button>
            </div>

            <!-- Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <div v-for="album in albums.data" :key="album.id" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col group">
                    <div class="aspect-[4/3] bg-slate-100 relative">
                        <img v-if="album.image_url" :src="album.image_url" class="w-full h-full object-cover">
                        <div v-else class="w-full h-full flex items-center justify-center text-slate-300 text-4xl">📁</div>
                        
                        <div :class="['absolute top-3 left-3 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase shadow-sm', album.status === 'aktif' ? 'bg-green-500 text-white' : 'bg-slate-500 text-white']">
                            {{ album.status }}
                        </div>

                        <!-- Badge Count -->
                        <div class="absolute bottom-3 right-3 bg-black/50 backdrop-blur text-white text-[10px] font-bold px-2 py-1 rounded-lg">
                            {{ album.galeri_count }} Foto
                        </div>
                    </div>
                    <div class="p-4 flex-1 flex flex-col">
                        <h3 class="font-bold text-slate-900 group-hover:text-purple-600 transition-colors">{{ album.nama }}</h3>
                        <p class="text-xs text-slate-500 line-clamp-2 mt-1 flex-1">{{ album.deskripsi || 'Tidak ada deskripsi.' }}</p>
                        
                        <div class="mt-4 flex items-center justify-between gap-3 pt-3 border-t border-slate-50">
                            <Link :href="route('admin.web.album.show', album.id)" class="text-xs font-bold text-purple-600 hover:underline">Kelola Foto →</Link>
                            <div class="flex gap-3">
                                <button @click="openEdit(album)" class="text-[10px] font-bold text-slate-400 hover:text-blue-600 uppercase tracking-wider transition-colors">Edit</button>
                                <button @click="confirmDelete(album.id)" class="text-[10px] font-bold text-slate-400 hover:text-red-500 uppercase tracking-wider transition-colors">Hapus</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!albums.data.length" class="bg-white rounded-2xl border border-slate-200 py-20 text-center">
                <span class="text-5xl">📁</span>
                <p class="text-slate-400 font-medium mt-4">Belum ada album galeri.</p>
                <button @click="openCreate" class="text-purple-600 hover:underline font-bold text-sm mt-1">Buat album pertama</button>
            </div>

            <!-- Pagination -->
            <div v-if="albums.last_page > 1" class="flex items-center justify-between px-1 py-3 border-t border-slate-100">
                <p class="text-xs text-slate-500">{{ albums.from }}-{{ albums.to }} dari {{ albums.total }}</p>
                <div class="flex gap-1">
                    <Link v-if="albums.prev_page_url" :href="albums.prev_page_url" class="px-3 py-1 text-xs bg-white rounded-lg border border-slate-200 hover:bg-slate-50">‹ Prev</Link>
                    <Link v-if="albums.next_page_url" :href="albums.next_page_url" class="px-3 py-1 text-xs bg-white rounded-lg border border-slate-200 hover:bg-slate-50">Next ›</Link>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <transition name="fade">
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
                <div class="bg-white rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <h2 class="font-black text-slate-900 uppercase tracking-tight">{{ isEditing ? 'Edit Album' : 'Buat Album Baru' }}</h2>
                        <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
                    </div>
                    <form @submit.prevent="submit" class="p-6 space-y-4">
                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-slate-700">Nama Album</label>
                            <input v-model="form.nama" type="text" class="w-full border-slate-200 rounded-xl focus:ring-purple-500 focus:border-purple-500" required>
                            <p v-if="form.errors.nama" class="text-xs text-red-500">{{ form.errors.nama }}</p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-slate-700">Deskripsi</label>
                            <textarea v-model="form.deskripsi" rows="3" class="w-full border-slate-200 rounded-xl focus:ring-purple-500 focus:border-purple-500"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-sm font-semibold text-slate-700">Status</label>
                                <select v-model="form.status" class="w-full border-slate-200 rounded-xl focus:ring-purple-500 focus:border-purple-500">
                                    <option value="aktif">Aktif</option>
                                    <option value="nonaktif">Nonaktif</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="text-sm font-semibold text-slate-700">Cover Album</label>
                                <input type="file" @change="handleCover" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                            </div>
                        </div>
                        <div v-if="coverPreview" class="aspect-video rounded-xl overflow-hidden border border-slate-200 bg-slate-100">
                            <img :src="coverPreview" class="w-full h-full object-cover">
                        </div>
                        <div class="pt-4 flex gap-3">
                            <button type="submit" :disabled="form.processing" class="flex-1 bg-purple-600 text-white px-6 py-2.5 rounded-xl font-bold hover:bg-purple-700 transition-all shadow-lg shadow-purple-200 disabled:opacity-50">
                                {{ form.processing ? 'Sedang Memproses...' : (isEditing ? 'Simpan Perubahan' : 'Buat Album') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </transition>
    </AdminWebLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
