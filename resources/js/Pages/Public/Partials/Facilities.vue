<script setup>
defineProps({
    fasilitas: Array
});
</script>

<template>
    <section id="facilities" class="py-24 bg-slate-50">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center max-w-3xl mx-auto mb-20 space-y-4">
                <span class="text-blue-600 font-black uppercase tracking-[0.3em] text-xs">Sarana & Prasarana</span>
                <h2 class="text-4xl md:text-5xl font-black text-slate-900 leading-tight">
                    Fasilitas Lengkap Untuk Mendukung Pembelajaran
                </h2>
                <p class="text-slate-500 font-medium text-lg">
                    Kami menyediakan berbagai fasilitas modern untuk memastikan setiap siswa dapat mengeksplorasi potensi terbaik mereka.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                <div v-for="f in fasilitas" :key="f.id" class="group bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm hover:shadow-2xl hover:shadow-blue-100 hover:-translate-y-2 transition-all duration-500">
                    <div class="w-full aspect-video bg-slate-50 text-blue-600 rounded-2xl flex items-center justify-center text-3xl mb-8 group-hover:bg-blue-600 group-hover:text-white transition-all duration-500 overflow-hidden">
                        <!-- Icon placeholder or image -->
                        <img v-if="f.image_url" :src="f.image_url" 
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <span v-else>🏫</span>
                    </div>
                    <h3 class="text-xl font-black text-slate-900 mb-3">{{ f.nama }}</h3>
                    <p class="text-slate-500 font-medium text-sm leading-relaxed">{{ f.deskripsi || 'Fasilitas unggulan sekolah.' }}</p>
                </div>
            </div>

            <!-- More Section -->
            <div class="mt-20 flex flex-wrap justify-center items-center gap-10 opacity-50 grayscale hover:grayscale-0 transition-all duration-500">
                <div class="text-center">
                    <p class="text-2xl font-black">18</p>
                    <p class="text-[10px] font-black uppercase tracking-widest">Ruang Kelas</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-black">4</p>
                    <p class="text-[10px] font-black uppercase tracking-widest">Laboratorium</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-black">1</p>
                    <p class="text-[10px] font-black uppercase tracking-widest">Perpustakaan</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-black">1</p>
                    <p class="text-[10px] font-black uppercase tracking-widest">Lapangan Olahraga</p>
                </div>
            </div>
        </div>
    </section>
</template>
