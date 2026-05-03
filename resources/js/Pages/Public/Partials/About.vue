<script setup>
defineProps({
    about: Object,
    visiMisi: Object
});
</script>

<template>
    <section id="about" class="py-24 bg-white overflow-hidden">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-20 items-center">
                <!-- Image Side -->
                <div class="relative">
                    <div class="aspect-[4/5] rounded-[3rem] overflow-hidden shadow-2xl relative z-10 border-8 border-white group">
                        <img src="https://images.unsplash.com/photo-1524178232363-1fb28f74b671?auto=format&fit=crop&q=80&w=1000" 
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" 
                            alt="Learning Process">
                    </div>
                    <!-- Decorative Elements -->
                    <div class="absolute -top-10 -left-10 w-40 h-40 bg-blue-100 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-pulse"></div>
                    <div class="absolute -bottom-10 -right-10 w-60 h-60 bg-purple-100 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-pulse delay-700"></div>
                    
                    <div class="absolute bottom-10 -left-10 z-20 bg-white p-6 rounded-3xl shadow-xl border border-slate-50 flex items-center gap-4 animate-in fade-in slide-in-from-left-4 duration-1000">
                        <div class="w-14 h-14 bg-blue-600 text-white rounded-2xl flex items-center justify-center font-black text-2xl">A</div>
                        <div>
                            <p class="text-3xl font-black text-slate-900 leading-none">A</p>
                            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">Akreditasi</p>
                        </div>
                    </div>
                </div>

                <!-- Text Side -->
                <div class="space-y-8">
                    <div class="space-y-4">
                        <span class="text-blue-600 font-black uppercase tracking-[0.3em] text-xs">Tentang Kami</span>
                        <h2 class="text-4xl md:text-5xl font-black text-slate-900 leading-tight">
                            Membangun Masa Depan Gemilang Sejak Tahun 2004
                        </h2>
                    </div>
                    <div class="prose prose-slate prose-lg max-w-none text-slate-600 font-medium leading-relaxed" v-html="about?.content || 'SMA Negeri 2 Perbaungan berkomitmen memberikan pendidikan terbaik.'"></div>
                    
                    <!-- Stats Grid -->
                    <div class="grid grid-cols-3 gap-8 pt-6 border-t border-slate-100">
                        <div class="text-center">
                            <p class="text-3xl font-black text-slate-900 leading-none">1200+</p>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">Siswa</p>
                        </div>
                        <div class="text-center">
                            <p class="text-3xl font-black text-slate-900 leading-none">85+</p>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">Guru</p>
                        </div>
                        <div class="text-center">
                            <p class="text-3xl font-black text-slate-900 leading-none">20+</p>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">Fasilitas</p>
                        </div>
                    </div>

                    <div class="pt-4">
                        <a href="#vision" class="inline-flex items-center gap-3 text-blue-600 font-black uppercase tracking-widest text-sm hover:translate-x-2 transition-transform">
                            Visi & Misi Kami <span>→</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Visi Misi Section -->
    <section id="vision" class="py-24 bg-slate-50">
        <div class="max-w-7xl mx-auto px-6">
            <div class="bg-slate-900 rounded-[3rem] p-12 md:p-20 relative overflow-hidden shadow-2xl">
                <!-- Decoration -->
                <div class="absolute top-0 right-0 w-96 h-96 bg-blue-600/20 rounded-full blur-[100px]"></div>
                
                <div class="relative z-10 grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                    <div>
                        <span class="text-blue-400 font-black uppercase tracking-widest text-xs">Visi & Misi</span>
                        <h2 class="text-4xl font-black text-white mt-4 leading-tight">
                            Visi Kami Adalah Menjadi Pusat Pendidikan yang Mandiri.
                        </h2>
                    </div>
                    <div class="prose prose-invert prose-lg max-w-none text-slate-300 font-medium" v-html="visiMisi?.content || 'Mewujudkan lulusan yang cerdas dan kompetitif.'"></div>
                </div>
            </div>
        </div>
    </section>
</template>
