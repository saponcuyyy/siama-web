<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    sliders: Array
});

const current = ref(0);
let timer = null;

const next = () => {
    current.value = (current.value + 1) % (props.sliders?.length || 1);
};

const prev = () => {
    current.value = (current.value - 1 + (props.sliders?.length || 1)) % (props.sliders?.length || 1);
};

onMounted(() => {
    if (props.sliders?.length > 1) {
        timer = setInterval(next, 5000);
    }
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
});
</script>

<template>
    <section class="relative h-[90vh] min-h-[600px] bg-slate-900 overflow-hidden">
        <div v-for="(slide, index) in sliders" :key="slide.id" 
            :class="[
                'absolute inset-0 transition-all duration-1000 ease-in-out',
                index === current ? 'opacity-100 scale-100' : 'opacity-0 scale-110 pointer-events-none'
            ]"
        >
            <!-- Image Overlay -->
            <div class="absolute inset-0 bg-gradient-to-r from-slate-900 via-slate-900/60 to-transparent z-10"></div>
            
            <!-- Background Image -->
            <img :src="slide.image_url" 
                class="w-full h-full object-cover" alt="">

            <!-- Content -->
            <div class="absolute inset-0 z-20 flex items-center">
                <div class="max-w-7xl mx-auto px-6 w-full">
                    <div class="max-w-2xl space-y-6">
                        <span class="inline-block px-4 py-1.5 bg-blue-600 text-white text-[10px] font-black uppercase tracking-widest rounded-full animate-in fade-in slide-in-from-left-4 duration-700 delay-300">
                            Penerimaan Siswa Baru 2026/2027
                        </span>
                        <h2 class="text-5xl md:text-7xl font-black text-white leading-[1.1] animate-in fade-in slide-in-from-left-8 duration-700 delay-500">
                            {{ slide.judul }}
                        </h2>
                        <p class="text-lg text-slate-300 font-medium leading-relaxed animate-in fade-in slide-in-from-left-12 duration-700 delay-700">
                            {{ slide.subjudul }}
                        </p>
                        <div class="flex flex-wrap gap-4 pt-4 animate-in fade-in slide-in-from-left-16 duration-700 delay-1000">
                            <a :href="slide.link_url || '#'" class="bg-white text-slate-900 px-8 py-4 rounded-2xl font-black hover:bg-blue-600 hover:text-white transition-all transform hover:scale-105 active:scale-95 shadow-xl shadow-black/20">
                                {{ slide.link_text || 'Pelajari Lebih Lanjut' }}
                            </a>
                            <a href="#about" class="bg-white/10 backdrop-blur-md text-white border border-white/20 px-8 py-4 rounded-2xl font-black hover:bg-white/20 transition-all">
                                Profil Sekolah
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Controls -->
        <div v-if="sliders?.length > 1" class="absolute bottom-10 left-1/2 -translate-x-1/2 z-30 flex items-center gap-3">
            <button v-for="(_, index) in sliders" :key="index" @click="current = index"
                :class="[
                    'h-1.5 transition-all duration-300 rounded-full',
                    index === current ? 'w-8 bg-blue-600' : 'w-3 bg-white/30'
                ]"
            ></button>
        </div>

        <!-- Scroll Indicator -->
        <div class="absolute bottom-10 right-10 z-30 hidden md:block animate-bounce">
            <div class="w-6 h-10 border-2 border-white/30 rounded-full flex justify-center p-1">
                <div class="w-1 h-2 bg-white rounded-full"></div>
            </div>
        </div>
    </section>
</template>
