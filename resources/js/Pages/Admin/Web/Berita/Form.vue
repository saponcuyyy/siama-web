<script setup>
import AdminWebLayout from '@/Layouts/AdminWebLayout.vue';
import TiptapEditor from '@/Components/TiptapEditor.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    berita: Object,
    kategori: Array
});

const form = useForm({
    judul: props.berita?.judul || '',
    kategori_id: props.berita?.kategori_id || '',
    ringkasan: props.berita?.ringkasan || '',
    konten: props.berita?.konten || '',
    status: props.berita?.status || 'draft',
    thumbnail: null,
    published_at: props.berita?.published_at ? new Date(props.berita.published_at).toISOString().split('T')[0] : '',
    _method: props.berita ? 'PUT' : 'POST'
});

const thumbnailPreview = ref(props.berita?.image_url || null);

const handleThumbnail = (e) => {
    const file = e.target.files[0];
    form.thumbnail = file;
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => thumbnailPreview.value = e.target.result;
        reader.readAsDataURL(file);
    }
};

const submit = () => {
    if (props.berita) {
        // We use post with _method=PUT because of multipart/form-data limitations in PHP for PUT requests
        form.post(route('admin.web.berita.update', props.berita.id));
    } else {
        form.post(route('admin.web.berita.store'));
    }
};
</script>

<template>
    <AdminWebLayout>
        <Head :title="berita ? 'Edit Berita' : 'Tulis Berita'" />

        <div class="max-w-5xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-black text-slate-900">{{ berita ? 'Edit Berita' : 'Tulis Berita' }}</h1>
                    <p class="text-slate-500 text-sm">Formulir untuk mempublikasikan berita dan artikel sekolah.</p>
                </div>
                <Link :href="route('admin.web.berita.index')" class="text-sm font-semibold text-slate-500 hover:text-slate-700">
                    &larr; Kembali
                </Link>
            </div>

            <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-6">
                        <div class="space-y-1">
                            <label class="block text-sm font-semibold text-slate-700">Judul Berita</label>
                            <input v-model="form.judul" type="text" class="w-full border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500" required placeholder="Masukkan judul berita...">
                            <p v-if="form.errors.judul" class="text-xs text-red-500">{{ form.errors.judul }}</p>
                        </div>

                        <div class="space-y-1">
                            <label class="block text-sm font-semibold text-slate-700">Ringkasan Singkat</label>
                            <textarea v-model="form.ringkasan" rows="3" class="w-full border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500" required placeholder="Ringkasan untuk kartu berita..."></textarea>
                            <p v-if="form.errors.ringkasan" class="text-xs text-red-500">{{ form.errors.ringkasan }}</p>
                        </div>

                        <div class="space-y-1">
                            <label class="block text-sm font-semibold text-slate-700">Konten Utama</label>
                            <TiptapEditor v-model="form.konten" />
                            <p v-if="form.errors.konten" class="text-xs text-red-500">{{ form.errors.konten }}</p>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Content -->
                <div class="space-y-6">
                    <!-- Status & Category -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-6">
                        <h2 class="font-bold text-slate-800 text-sm uppercase tracking-wider">Pengaturan</h2>
                        
                        <div class="space-y-1">
                            <label class="block text-sm font-semibold text-slate-700">Kategori</label>
                            <select v-model="form.kategori_id" class="w-full border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500" required>
                                <option value="">Pilih Kategori</option>
                                <option v-for="kat in kategori" :key="kat.id" :value="kat.id">{{ kat.nama }}</option>
                            </select>
                            <p v-if="form.errors.kategori_id" class="text-xs text-red-500">{{ form.errors.kategori_id }}</p>
                        </div>

                        <div class="space-y-1">
                            <label class="block text-sm font-semibold text-slate-700">Status</label>
                            <select v-model="form.status" class="w-full border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500" required>
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                                <option value="archived">Archived</option>
                            </select>
                            <p v-if="form.errors.status" class="text-xs text-red-500">{{ form.errors.status }}</p>
                        </div>

                        <div class="space-y-1">
                            <label class="block text-sm font-semibold text-slate-700">Tanggal Publish (Opsional)</label>
                            <input v-model="form.published_at" type="date" class="w-full border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <!-- Thumbnail -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4">
                        <h2 class="font-bold text-slate-800 text-sm uppercase tracking-wider">Thumbnail</h2>
                        <div v-if="thumbnailPreview" class="aspect-video rounded-xl overflow-hidden bg-slate-100 border border-slate-200">
                            <img :src="thumbnailPreview" class="w-full h-full object-cover">
                        </div>
                        <input type="file" @change="handleThumbnail" class="hidden" id="thumbnail-upload" accept="image/*">
                        <label for="thumbnail-upload" class="block w-full text-center px-4 py-2 border-2 border-dashed border-slate-200 rounded-xl hover:border-blue-400 hover:bg-blue-50 cursor-pointer transition-all text-sm font-semibold text-slate-600">
                            {{ thumbnailPreview ? 'Ganti Foto' : 'Pilih Foto' }}
                        </label>
                        <p class="text-[10px] text-slate-400">Format: JPG, PNG, WebP (Maks 2MB)</p>
                        <p v-if="form.errors.thumbnail" class="text-xs text-red-500">{{ form.errors.thumbnail }}</p>
                    </div>

                    <!-- Actions -->
                    <button 
                        type="submit" 
                        :disabled="form.processing"
                        class="w-full bg-blue-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-blue-700 transition-all shadow-lg shadow-blue-200 disabled:opacity-50 active:scale-95"
                    >
                        {{ form.processing ? 'Menyimpan...' : (berita ? 'Perbarui Berita' : 'Terbitkan Berita') }}
                    </button>
                </div>
            </form>
        </div>
    </AdminWebLayout>
</template>
