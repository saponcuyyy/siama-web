<script setup>
import AdminWebLayout from '@/Layouts/AdminWebLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import draggable from 'vuedraggable';

const props = defineProps({
    fasilitas: Array
});

const isEditing = ref(null);
const form = useForm({
    nama: '',
    deskripsi: '',
    foto: null,
    status: 'aktif',
    _method: 'POST'
});

const fotoPreview = ref(null);

const handleFoto = (e) => {
    const file = e.target.files[0];
    form.foto = file;
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => fotoPreview.value = e.target.result;
        reader.readAsDataURL(file);
    }
};

const submit = () => {
    if (isEditing.value) {
        form.post(route('admin.web.fasilitas.update', isEditing.value.hashid), {
            onSuccess: () => {
                isEditing.value = null;
                form.reset();
                fotoPreview.value = null;
            }
        });
    } else {
        form.post(route('admin.web.fasilitas.store'), {
            onSuccess: () => {
                form.reset();
                fotoPreview.value = null;
            }
        });
    }
};

const editFasilitas = (f) => {
    isEditing.value = f;
    form.nama = f.nama;
    form.deskripsi = f.deskripsi || '';
    form.status = f.status;
    form._method = 'PUT';
    fotoPreview.value = f.image_url || null;
};

const cancelEdit = () => {
    isEditing.value = null;
    form.reset();
    form._method = 'POST';
    fotoPreview.value = null;
};

const onDragEnd = () => {
    const ids = props.fasilitas.map(f => f.id);
    router.post(route('admin.web.fasilitas.order'), { ids }, {
        preserveScroll: true
    });
};

const confirmDelete = (id) => {
    if (confirm('Yakin hapus fasilitas ini?')) {
        router.delete(route('admin.web.fasilitas.destroy', id));
    }
};
</script>

<template>
    <AdminWebLayout>
        <Head title="Manajemen Fasilitas" />

        <div class="max-w-6xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-black text-slate-900">Fasilitas Sekolah</h1>
                    <p class="text-slate-500 text-sm">Kelola daftar sarana dan prasarana sekolah.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Form -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm sticky top-24">
                        <h2 class="font-bold text-slate-800 mb-4">{{ isEditing ? 'Edit Fasilitas' : 'Tambah Fasilitas' }}</h2>
                        <form @submit.prevent="submit" class="space-y-4">
                            <div class="space-y-1">
                                <label class="text-sm font-semibold text-slate-700">Nama Fasilitas</label>
                                <input v-model="form.nama" type="text" class="w-full border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500" required>
                                <p v-if="form.errors.nama" class="text-xs text-red-500">{{ form.errors.nama }}</p>
                            </div>

                            <div class="space-y-1">
                                <label class="text-sm font-semibold text-slate-700">Deskripsi Singkat</label>
                                <textarea v-model="form.deskripsi" rows="3" class="w-full border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500"></textarea>
                                <p v-if="form.errors.deskripsi" class="text-xs text-red-500">{{ form.errors.deskripsi }}</p>
                            </div>

                            <div class="space-y-1">
                                <label class="text-sm font-semibold text-slate-700">Status</label>
                                <select v-model="form.status" class="w-full border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500">
                                    <option value="aktif">Aktif</option>
                                    <option value="nonaktif">Nonaktif</option>
                                </select>
                            </div>

                            <div class="space-y-1">
                                <label class="text-sm font-semibold text-slate-700">Foto</label>
                                <div v-if="fotoPreview" class="aspect-video mb-2 rounded-xl overflow-hidden border border-slate-200 bg-slate-100">
                                    <img :src="fotoPreview" class="w-full h-full object-cover">
                                </div>
                                <input type="file" @change="handleFoto" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                <p v-if="form.errors.foto" class="text-xs text-red-500">{{ form.errors.foto }}</p>
                            </div>

                            <div class="flex gap-2 pt-2">
                                <button type="submit" :disabled="form.processing" class="flex-1 bg-blue-600 text-white px-4 py-2.5 rounded-xl font-semibold text-sm hover:bg-blue-700 transition-all shadow-sm disabled:opacity-50">
                                    {{ isEditing ? 'Update Fasilitas' : 'Simpan Fasilitas' }}
                                </button>
                                <button v-if="isEditing" @click="cancelEdit" type="button" class="px-4 py-2.5 rounded-xl font-semibold text-sm border border-slate-200 text-slate-600 hover:bg-slate-50 transition-all">
                                    Batal
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- List with Drag & Drop -->
                <div class="lg:col-span-2 space-y-4">
                    <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 flex items-center gap-3">
                        <span class="text-xl">↕️</span>
                        <p class="text-xs text-blue-800 font-medium">Anda dapat menyeret (drag & drop) item di bawah ini untuk mengatur urutan tampilan di landing page.</p>
                    </div>

                    <draggable 
                        v-model="props.fasilitas" 
                        item-key="id"
                        class="grid grid-cols-1 md:grid-cols-2 gap-4"
                        @end="onDragEnd"
                        handle=".drag-handle"
                    >
                        <template #item="{element}">
                            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col group">
                                <div class="aspect-video bg-slate-100 relative">
                                    <img v-if="element.image_url" :src="element.image_url" class="w-full h-full object-cover">
                                    <div v-else class="w-full h-full flex items-center justify-center text-slate-300 text-3xl">🏢</div>
                                    <div class="drag-handle absolute top-2 right-2 w-8 h-8 bg-white/80 backdrop-blur rounded-lg flex items-center justify-center cursor-move opacity-0 group-hover:opacity-100 transition-opacity shadow-sm border border-slate-200">
                                        ⠿
                                    </div>
                                    <div :class="['absolute top-2 left-2 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase', element.status === 'aktif' ? 'bg-green-500 text-white' : 'bg-slate-500 text-white']">
                                        {{ element.status }}
                                    </div>
                                </div>
                                <div class="p-4 flex-1 flex flex-col">
                                    <h3 class="font-bold text-slate-900 truncate">{{ element.nama }}</h3>
                                    <p class="text-xs text-slate-500 line-clamp-2 mt-1 flex-1">{{ element.deskripsi || 'Tidak ada deskripsi.' }}</p>
                                    <div class="mt-4 flex justify-end gap-3 pt-3 border-t border-slate-50">
                                        <button @click="editFasilitas(element)" class="text-xs font-bold text-blue-600 hover:underline">Edit</button>
                                        <button @click="confirmDelete(element.id)" class="text-xs font-bold text-red-500 hover:underline">Hapus</button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </draggable>

                    <div v-if="!fasilitas.length" class="bg-white rounded-2xl border border-slate-200 py-12 text-center">
                        <p class="text-slate-400 italic">Belum ada fasilitas yang ditambahkan.</p>
                    </div>
                </div>
            </div>
        </div>
    </AdminWebLayout>
</template>
