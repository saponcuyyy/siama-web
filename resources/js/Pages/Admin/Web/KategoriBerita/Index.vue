<script setup>
import AdminWebLayout from '@/Layouts/AdminWebLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    kategori: Array
});

const isEditing = ref(null);
const form = useForm({
    nama: ''
});

const submit = () => {
    if (isEditing.value) {
        form.put(route('admin.web.kategori-berita.update', isEditing.value.hashid), {
            onSuccess: () => {
                isEditing.value = null;
                form.reset();
            }
        });
    } else {
        form.post(route('admin.web.kategori-berita.store'), {
            onSuccess: () => form.reset()
        });
    }
};

const editKategori = (kat) => {
    isEditing.value = kat;
    form.nama = kat.nama;
};

const cancelEdit = () => {
    isEditing.value = null;
    form.reset();
};
</script>

<template>
    <AdminWebLayout>
        <Head title="Manajemen Kategori Berita" />

        <div class="max-w-4xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-black text-slate-900">Kategori Berita</h1>
                    <p class="text-slate-500 text-sm">Kelola kategori untuk pengelompokan artikel.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Form -->
                <div class="md:col-span-1">
                    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
                        <h2 class="font-bold text-slate-800 mb-4">{{ isEditing ? 'Edit Kategori' : 'Tambah Kategori' }}</h2>
                        <form @submit.prevent="submit" class="space-y-4">
                            <div class="space-y-1">
                                <label class="text-sm font-semibold text-slate-700">Nama Kategori</label>
                                <input 
                                    v-model="form.nama" 
                                    type="text" 
                                    class="w-full border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500" 
                                    placeholder="Contoh: Pengumuman"
                                    required
                                >
                                <p v-if="form.errors.nama" class="text-xs text-red-500">{{ form.errors.nama }}</p>
                            </div>
                            <div class="flex gap-2">
                                <button 
                                    type="submit" 
                                    :disabled="form.processing"
                                    class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-xl font-semibold text-sm hover:bg-blue-700 transition-all shadow-sm disabled:opacity-50"
                                >
                                    {{ isEditing ? 'Update' : 'Simpan' }}
                                </button>
                                <button 
                                    v-if="isEditing" 
                                    @click="cancelEdit"
                                    type="button"
                                    class="px-4 py-2 rounded-xl font-semibold text-sm border border-slate-200 text-slate-600 hover:bg-slate-50 transition-all"
                                >
                                    Batal
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Table -->
                <div class="md:col-span-2">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr>
                                    <th class="text-left px-5 py-3 font-semibold text-slate-600">Nama Kategori</th>
                                    <th class="text-left px-5 py-3 font-semibold text-slate-600">Slug</th>
                                    <th class="px-5 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="kat in kategori" :key="kat.id" class="hover:bg-slate-50 transition-colors">
                                    <td class="px-5 py-3 font-medium text-slate-900">{{ kat.nama }}</td>
                                    <td class="px-5 py-3 text-slate-500 font-mono text-xs">{{ kat.slug }}</td>
                                    <td class="px-5 py-3 text-right">
                                        <div class="flex items-center justify-end gap-3">
                                            <button @click="editKategori(kat)" class="text-blue-600 hover:underline font-semibold">Edit</button>
                                            <button 
                                                @click="form.delete(route('admin.web.kategori-berita.destroy', kat.hashid))" 
                                                class="text-red-500 hover:underline font-semibold"
                                            >
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!kategori.length">
                                    <td colspan="3" class="px-5 py-10 text-center text-slate-400 italic">Belum ada kategori.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AdminWebLayout>
</template>
