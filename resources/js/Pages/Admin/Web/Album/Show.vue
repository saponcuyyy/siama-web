<script setup>
import AdminWebLayout from '@/Layouts/AdminWebLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    album: Object,
    photos: Array
});

const isUploading = ref(false);
const form = useForm({
    photos: []
});

const handleFiles = (e) => {
    form.photos = Array.from(e.target.files);
};

const upload = () => {
    isUploading.value = true;
    form.post(route('admin.web.album.photos.upload', props.album.id), {
        onSuccess: () => {
            form.reset();
            isUploading.value = false;
        },
        onError: () => isUploading.value = false
    });
};

const deletePhoto = (id) => {
    if (confirm('Hapus foto ini?')) {
        router.delete(route('admin.web.galeri.destroy', id), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <AdminWebLayout>
        <Head :title="'Album: ' + album.nama" />

        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('admin.web.album.index')" class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:text-slate-600 hover:border-slate-300 transition-all shadow-sm">
                        ←
                    </Link>
                    <div>
                        <h1 class="text-xl font-black text-slate-900">{{ album.nama }}</h1>
                        <p class="text-slate-500 text-sm">Manajemen foto dalam album ini.</p>
                    </div>
                </div>
            </div>

            <!-- Upload Area -->
            <div class="bg-white rounded-2xl border-2 border-dashed border-slate-200 p-8 text-center space-y-4">
                <div class="max-w-xs mx-auto">
                    <input type="file" @change="handleFiles" multiple accept="image/*" class="hidden" id="photo-upload">
                    <label for="photo-upload" class="cursor-pointer space-y-2 block">
                        <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-3xl">📷</span>
                        </div>
                        <p class="font-bold text-slate-700">Pilih Foto untuk Diupload</p>
                        <p class="text-xs text-slate-400">Anda dapat memilih beberapa foto sekaligus. (Maks 5MB per foto)</p>
                    </label>
                </div>

                <div v-if="form.photos.length > 0" class="pt-4 animate-in fade-in slide-in-from-bottom-2 duration-300">
                    <p class="text-sm font-semibold text-blue-600 mb-4">{{ form.photos.length }} foto terpilih</p>
                    <button 
                        @click="upload" 
                        :disabled="isUploading"
                        class="bg-blue-600 text-white px-8 py-2.5 rounded-xl font-bold hover:bg-blue-700 transition-all shadow-lg shadow-blue-100 disabled:opacity-50"
                    >
                        {{ isUploading ? 'Sedang Mengupload...' : 'Mulai Upload' }}
                    </button>
                </div>
            </div>

            <!-- Photos Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
                <div v-for="photo in photos" :key="photo.id" class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden group relative aspect-square">
                    <img :src="photo.image_url" class="w-full h-full object-cover">
                    
                    <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                        <button @click="deletePhoto(photo.id)" class="w-8 h-8 bg-white text-red-500 rounded-lg flex items-center justify-center hover:scale-110 transition-transform shadow-sm">
                            🗑️
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="!photos.length && !form.photos.length" class="text-center py-20 text-slate-400 italic">
                Album ini masih kosong. Silakan upload foto pertama Anda.
            </div>
        </div>
    </AdminWebLayout>
</template>
