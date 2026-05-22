<script setup>
import AdminWebLayout from '@/Layouts/AdminWebLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({ pesan: Object });

const confirmDelete = () => {
    if (confirm('Yakin hapus pesan ini?')) {
        router.delete(route('admin.web.pesan.destroy', props.pesan.hashid));
    }
};
</script>

<template>
    <AdminWebLayout>
        <Head title="Baca Pesan" />

        <div class="max-w-3xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-black text-slate-900">Detail Pesan</h1>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('admin.web.pesan.index')" class="text-sm font-semibold text-slate-500 hover:text-slate-700">
                        &larr; Kembali
                    </Link>
                    <button @click="confirmDelete" class="bg-red-50 text-red-600 px-3 py-1.5 rounded-lg text-xs font-semibold hover:bg-red-100 transition-colors">
                        Hapus Pesan
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <!-- Message Header -->
                <div class="p-6 border-b border-slate-100 bg-slate-50 flex items-start justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold text-xl uppercase">
                            {{ pesan.nama.charAt(0) }}
                        </div>
                        <div>
                            <p class="font-bold text-slate-900 text-lg">{{ pesan.nama }}</p>
                            <p class="text-sm text-slate-500 flex items-center gap-2 mt-0.5">
                                <a :href="'mailto:' + pesan.email" class="hover:text-blue-600">{{ pesan.email }}</a>
                                <span v-if="pesan.telepon" class="text-slate-300">•</span>
                                <span v-if="pesan.telepon">{{ pesan.telepon }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-slate-500">{{ new Date(pesan.created_at).toLocaleString('id-ID') }}</p>
                        <span :class="['text-[10px] font-bold px-2 py-0.5 rounded-full mt-2 inline-block', 
                            pesan.status === 'dibalas' ? 'bg-green-100 text-green-700' : 'bg-slate-200 text-slate-600']">
                            {{ pesan.status.replace('_', ' ').toUpperCase() }}
                        </span>
                    </div>
                </div>

                <!-- Message Body -->
                <div class="p-6">
                    <h2 class="font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100">{{ pesan.subjek }}</h2>
                    <div class="prose prose-sm max-w-none text-slate-700 whitespace-pre-wrap leading-relaxed">
                        {{ pesan.pesan }}
                    </div>
                </div>

                <!-- Reply Section (Placeholder) -->
                <div class="bg-slate-50 p-6 border-t border-slate-100">
                    <h3 class="font-bold text-slate-800 mb-3 text-sm flex items-center gap-2"><span>✉️</span> Balas via Email</h3>
                    <p class="text-xs text-slate-500 mb-4">Fitur balasan langsung dari sistem sedang dalam pengembangan. Saat ini, silakan balas menggunakan email klien (contoh: Gmail).</p>
                    <a :href="'mailto:' + pesan.email + '?subject=Re: ' + pesan.subjek" class="inline-block bg-blue-600 text-white px-5 py-2 rounded-xl text-sm font-semibold hover:bg-blue-700 transition-colors shadow-sm">
                        Buka Aplikasi Email
                    </a>
                </div>
            </div>
        </div>
    </AdminWebLayout>
</template>
