<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    album: Object,
    photos: Array,
    settings: Object,
});

const selectedPhoto = ref(null);
</script>

<template>
    <GuestLayout :settings="settings">
        <Head :title="'Album: ' + album.nama" />

        <section class="py-20 bg-white min-h-screen">
            <div class="max-w-7xl mx-auto px-6">
                <!-- Header -->
                <div class="mb-12">
                    <Link :href="route('public.galeri.index')" class="text-blue-600 font-bold text-sm hover:underline mb-4 inline-block">← Kembali ke Galeri</Link>
                    <h1 class="text-3xl md:text-5xl font-black text-slate-900 leading-tight mb-2">{{ album.nama }}</h1>
                    <p class="text-slate-500 max-w-2xl text-lg">{{ album.deskripsi || 'Album dokumentasi kegiatan sekolah.' }}</p>
                    <div class="mt-4 flex items-center gap-4 text-xs font-bold uppercase tracking-widest text-slate-400">
                        <span>{{ photos.length }} Foto</span>
                        <span>•</span>
                        <span>{{ new Date(album.created_at).toLocaleDateString('id-ID', {month:'long', year:'numeric'}) }}</span>
                    </div>
                </div>

                <!-- Photos Grid -->
                <div v-if="photos.length" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <div v-for="photo in photos" :key="photo.id" 
                        @click="selectedPhoto = photo"
                        class="aspect-square bg-slate-100 rounded-2xl overflow-hidden cursor-pointer group relative">
                        <img v-if="photo.image_url" :src="photo.image_url" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                        <div class="absolute inset-0 bg-slate-900/0 group-hover:bg-slate-900/40 transition-colors duration-300 flex items-center justify-center">
                            <span class="text-white opacity-0 group-hover:opacity-100 transform translate-y-4 group-hover:translate-y-0 transition-all duration-300 text-3xl">🔍</span>
                        </div>
                    </div>
                </div>
                
                <div v-else class="text-center py-24 bg-slate-50 rounded-3xl border border-slate-100">
                    <p class="text-5xl mb-4">📸</p>
                    <p class="text-slate-400 font-bold">Belum ada foto dalam album ini.</p>
                </div>
            </div>
        </section>

        <!-- Lightbox Modal -->
        <transition name="fade">
            <div v-if="selectedPhoto" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/95 backdrop-blur-sm p-4" @click="selectedPhoto = null">
                <div class="absolute top-6 right-6 text-white cursor-pointer text-4xl hover:text-slate-300 transition-colors">&times;</div>
                <img :src="selectedPhoto.image_url" class="max-w-full max-h-[90vh] object-contain rounded-lg shadow-2xl" @click.stop>
            </div>
        </transition>
    </GuestLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
