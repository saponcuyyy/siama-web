<script setup>
import AdminWebLayout from '@/Layouts/AdminWebLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    pengumuman: Object,
});

const form = useForm({
    judul: props.pengumuman?.judul || '',
    konten: props.pengumuman?.konten || '',
    prioritas: props.pengumuman?.prioritas || 'normal',
    tanggal_mulai: props.pengumuman?.tanggal_mulai || '',
    tanggal_selesai: props.pengumuman?.tanggal_selesai || '',
    status: props.pengumuman?.status || 'aktif',
});

const submit = () => {
    if (props.pengumuman) {
        form.put(route('admin.web.pengumuman.update', props.pengumuman.hashid));
    } else {
        form.post(route('admin.web.pengumuman.store'));
    }
};
</script>

<template>
    <AdminWebLayout>
        <Head :title="pengumuman ? 'Edit Pengumuman' : 'Tambah Pengumuman'" />

        <div class="max-w-4xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-black text-slate-900">{{ pengumuman ? 'Edit Pengumuman' : 'Tambah Pengumuman' }}</h1>
                    <p class="text-slate-500 text-sm">Formulir untuk mempublikasikan pengumuman sekolah.</p>
                </div>
                <Link :href="route('admin.web.pengumuman.index')" class="text-sm font-semibold text-slate-500 hover:text-slate-700">
                    &larr; Kembali
                </Link>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1 md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700">Judul Pengumuman</label>
                            <input v-model="form.judul" type="text" class="w-full border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500" required placeholder="Contoh: Libur Hari Raya...">
                            <p v-if="form.errors.judul" class="text-xs text-red-500">{{ form.errors.judul }}</p>
                        </div>

                        <div class="space-y-1 md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700">Isi Pengumuman</label>
                            <textarea v-model="form.konten" rows="6" class="w-full border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500" required placeholder="Tulis rincian pengumuman..."></textarea>
                            <p v-if="form.errors.konten" class="text-xs text-red-500">{{ form.errors.konten }}</p>
                        </div>

                        <div class="space-y-1">
                            <label class="block text-sm font-semibold text-slate-700">Tingkat Prioritas</label>
                            <select v-model="form.prioritas" class="w-full border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500">
                                <option value="rendah">Rendah (Info Biasa)</option>
                                <option value="normal">Normal</option>
                                <option value="tinggi">Tinggi (Penting & Mendesak)</option>
                            </select>
                            <p v-if="form.errors.prioritas" class="text-xs text-red-500">{{ form.errors.prioritas }}</p>
                        </div>

                        <div class="space-y-1">
                            <label class="block text-sm font-semibold text-slate-700">Status</label>
                            <select v-model="form.status" class="w-full border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500">
                                <option value="aktif">Aktif (Tampil)</option>
                                <option value="nonaktif">Nonaktif (Sembunyikan)</option>
                            </select>
                            <p v-if="form.errors.status" class="text-xs text-red-500">{{ form.errors.status }}</p>
                        </div>

                        <div class="space-y-1">
                            <label class="block text-sm font-semibold text-slate-700">Tanggal Mulai Berlaku (Opsional)</label>
                            <input v-model="form.tanggal_mulai" type="date" class="w-full border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500">
                            <p v-if="form.errors.tanggal_mulai" class="text-xs text-red-500">{{ form.errors.tanggal_mulai }}</p>
                        </div>

                        <div class="space-y-1">
                            <label class="block text-sm font-semibold text-slate-700">Tanggal Selesai Berlaku (Opsional)</label>
                            <input v-model="form.tanggal_selesai" type="date" class="w-full border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500">
                            <p v-if="form.errors.tanggal_selesai" class="text-xs text-red-500">{{ form.errors.tanggal_selesai }}</p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                        <Link :href="route('admin.web.pengumuman.index')" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors">Batal</Link>
                        <button type="submit" :disabled="form.processing" class="bg-green-600 text-white px-5 py-2.5 rounded-xl font-semibold text-sm hover:bg-green-700 transition-all shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Pengumuman' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminWebLayout>
</template>
