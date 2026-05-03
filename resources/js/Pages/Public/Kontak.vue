<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import Contact from './Partials/Contact.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    menus: Array,
    settings: Object
});
</script>

<template>
    <GuestLayout :menus="menus" :settings="settings">
        <Head title="Hubungi Kami" />

        <div class="pt-32 pb-12 bg-slate-50">
            <div class="max-w-7xl mx-auto px-6 text-center">
                <h1 class="text-5xl font-black text-slate-900 mb-4">Kontak Kami</h1>
                <p class="text-slate-500 font-medium text-lg max-w-2xl mx-auto">Silakan hubungi kami untuk informasi lebih lanjut mengenai pendaftaran, kurikulum, atau kegiatan sekolah lainnya.</p>
            </div>
        </div>

        <Contact :settings="settings" />
        
        <!-- Additional FAQ or Info can go here -->
    </GuestLayout>
</template>
