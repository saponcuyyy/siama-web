<script setup>
import AdminWebLayout from '@/Layouts/AdminWebLayout.vue';
import TiptapEditor from '@/Components/TiptapEditor.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    page: Object
});

const form = useForm({
    title: props.page?.title || '',
    content: props.page?.content || '',
    meta_title: props.page?.meta_title || '',
    meta_description: props.page?.meta_description || '',
    status: props.page?.status || 'draft',
});

const submit = () => {
    if (props.page) {
        form.put(route('admin.web.halaman.update', props.page.id));
    } else {
        form.post(route('admin.web.halaman.store'));
    }
};
</script>

<template>
    <AdminWebLayout>
        <Head :title="page ? 'Edit Halaman' : 'Buat Halaman'" />

        <div class="max-w-5xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-black text-slate-900">{{ page ? 'Edit Halaman' : 'Buat Halaman' }}</h1>
                    <p class="text-slate-500 text-sm">Formulir untuk mempublikasikan halaman statis.</p>
                </div>
                <Link :href="route('admin.web.halaman.index')" class="text-sm font-semibold text-slate-500 hover:text-slate-700">
                    &larr; Kembali
                </Link>
            </div>

            <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-6">
                        <div class="space-y-1">
                            <label class="block text-sm font-semibold text-slate-700">Judul Halaman</label>
                            <input v-model="form.title" type="text" class="w-full border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500" required placeholder="Masukkan judul halaman...">
                            <p v-if="form.errors.title" class="text-xs text-red-500">{{ form.errors.title }}</p>
                        </div>

                        <div class="space-y-1">
                            <label class="block text-sm font-semibold text-slate-700">Konten Halaman</label>
                            <TiptapEditor v-model="form.content" />
                            <p v-if="form.errors.content" class="text-xs text-red-500">{{ form.errors.content }}</p>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Content -->
                <div class="space-y-6">
                    <!-- Status & SEO -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-6">
                        <h2 class="font-bold text-slate-800 text-sm uppercase tracking-wider">Pengaturan</h2>

                        <div class="space-y-1">
                            <label class="block text-sm font-semibold text-slate-700">Status</label>
                            <select v-model="form.status" class="w-full border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500" required>
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                                <option value="archived">Archived</option>
                            </select>
                            <p v-if="form.errors.status" class="text-xs text-red-500">{{ form.errors.status }}</p>
                        </div>

                        <div class="border-t border-slate-100 pt-4 space-y-4 mt-2">
                            <h3 class="font-bold text-slate-700 text-sm">SEO Meta (Opsional)</h3>
                            
                            <div class="space-y-1">
                                <label class="block text-xs font-semibold text-slate-600">Meta Title</label>
                                <input v-model="form.meta_title" type="text" class="w-full border-slate-200 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                                <p v-if="form.errors.meta_title" class="text-xs text-red-500">{{ form.errors.meta_title }}</p>
                            </div>

                            <div class="space-y-1">
                                <label class="block text-xs font-semibold text-slate-600">Meta Description</label>
                                <textarea v-model="form.meta_description" rows="3" class="w-full border-slate-200 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                                <p v-if="form.errors.meta_description" class="text-xs text-red-500">{{ form.errors.meta_description }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <button 
                        type="submit" 
                        :disabled="form.processing"
                        class="w-full bg-blue-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-blue-700 transition-all shadow-lg shadow-blue-200 disabled:opacity-50 active:scale-95"
                    >
                        {{ form.processing ? 'Menyimpan...' : (page ? 'Perbarui Halaman' : 'Simpan Halaman') }}
                    </button>
                </div>
            </form>
        </div>
    </AdminWebLayout>
</template>
