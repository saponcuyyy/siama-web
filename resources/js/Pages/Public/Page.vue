<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    page: Object,
    settings: Object,
});
</script>

<template>
    <GuestLayout :settings="settings">
        <Head>
            <title>{{ page.meta_title || page.title }}</title>
            <meta v-if="page.meta_description" name="description" :content="page.meta_description" />
        </Head>

        <section class="py-20 bg-white min-h-screen">
            <div class="max-w-4xl mx-auto px-6">
                <!-- Header -->
                <div class="mb-12 border-b border-slate-100 pb-8 text-center">
                    <h1 class="text-3xl md:text-5xl font-black text-slate-900 leading-tight">{{ page.title }}</h1>
                    <div class="mt-4 flex items-center justify-center gap-4 text-xs font-bold uppercase tracking-widest text-slate-400">
                        <span>Diperbarui pada {{ new Date(page.updated_at).toLocaleDateString('id-ID', {day:'numeric', month:'long', year:'numeric'}) }}</span>
                    </div>
                </div>

                <!-- Content -->
                <div class="prose prose-lg prose-slate max-w-none" v-html="page.content"></div>
            </div>
        </section>
    </GuestLayout>
</template>
