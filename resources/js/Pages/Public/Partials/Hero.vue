<script setup>
const props = defineProps({
    sliders: Array,
    settings: Object
});
</script>

<template>
    <section class="relative min-h-screen bg-[#f3f8ff] overflow-hidden pt-28 pb-16 flex flex-col justify-between">
        
        <!-- WAVY / FLUID BACKGROUND SHAPES (Tailored to match the original image aesthetics) -->
        <div class="absolute inset-0 pointer-events-none z-0">
            <!-- Left top organic blue blob -->
            <svg class="absolute top-0 left-0 w-[45%] h-[55%] text-blue-200/40 transform -translate-x-12 -translate-y-12" viewBox="0 0 100 100" fill="currentColor" preserveAspectRatio="none">
                <path d="M0,0 C30,0 60,20 50,55 C40,90 20,100 0,100 Z" />
            </svg>
            
            <!-- Right top abstract soft blue gradient -->
            <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-gradient-to-br from-blue-300/20 to-transparent rounded-full filter blur-3xl opacity-80"></div>
            
            <!-- Left bottom deep blue wavy shape -->
            <svg class="absolute bottom-0 left-0 w-[35%] h-[40%] text-blue-100/50" viewBox="0 0 100 100" fill="currentColor" preserveAspectRatio="none">
                <path d="M0,50 C40,50 30,80 50,100 L0,100 Z" />
            </svg>

            <!-- Right side organic soft shapes -->
            <svg class="absolute right-0 bottom-12 w-[40%] h-[60%] text-blue-200/30" viewBox="0 0 100 100" fill="currentColor" preserveAspectRatio="none">
                <path d="M100,0 C70,10 65,40 80,70 C90,90 95,100 100,100 Z" />
            </svg>

            <!-- Decorative Dots Matrix (Top Right) -->
            <div class="absolute top-16 right-16 opacity-30 grid grid-cols-6 gap-2">
                <div v-for="i in 24" :key="i" class="w-1.5 h-1.5 bg-blue-600 rounded-full"></div>
            </div>
        </div>

        <!-- HERO CONTAINER -->
        <div class="max-w-7xl mx-auto px-6 w-full relative z-10 my-auto grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            
            <!-- LEFT CONTENT COLUMN -->
            <div class="lg:col-span-7 space-y-8 text-left animate-fade-in-up">
                <!-- Branding badge -->
                <div class="inline-flex items-center gap-2 px-5 py-2 bg-blue-50 border border-blue-100 text-blue-600 rounded-full shadow-sm hover:scale-105 transition-transform duration-300">
                    <span class="w-2 h-2 bg-blue-600 rounded-full animate-pulse"></span>
                    <span class="text-xs md:text-sm font-extrabold uppercase tracking-wider">{{ settings?.nama_sekolah || 'SMA Negeri 2 Perbaungan' }}</span>
                </div>

                <!-- Headline -->
                <div class="space-y-4">
                    <h1 class="text-4xl md:text-6xl font-black text-slate-900 leading-[1.1] tracking-tight">
                        Berkarakter, Berprestasi,<br />
                        <span class="text-blue-600 bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">Siap Menghadapi<br />Masa Depan</span>
                    </h1>
                    <!-- Gold Accent Line -->
                    <div class="w-20 h-1.5 bg-[#f59e0b] rounded-full shadow-sm"></div>
                </div>

                <!-- Paragraph text -->
                <p class="text-slate-600 text-base md:text-lg font-medium leading-relaxed max-w-xl">
                    {{ settings?.tagline || 'Kami berkomitmen memberikan pendidikan berkualitas untuk membentuk generasi unggul yang berakhlak mulia, berilmu, berprestasi, dan siap bersaing di era global.' }}
                </p>

                <!-- Action Button -->
                <div class="flex flex-wrap gap-4 pt-2">
                    <a href="#about" class="bg-blue-600 text-white px-8 py-4 rounded-2xl font-black hover:bg-blue-700 transition-all shadow-lg shadow-blue-600/20 transform hover:-translate-y-0.5 active:translate-y-0">
                        Pelajari Lebih Lanjut
                    </a>
                    <a href="#kontak" class="bg-white text-slate-800 border border-slate-200 px-8 py-4 rounded-2xl font-black hover:bg-slate-50 transition-all transform hover:-translate-y-0.5 active:translate-y-0">
                        Hubungi Kami
                    </a>
                </div>
            </div>

            <!-- RIGHT ILLUSTRATION COLUMN -->
            <div class="lg:col-span-5 flex justify-center lg:justify-end relative">
                <!-- Floating blobs behind the illustration -->
                <div class="absolute -inset-4 bg-gradient-to-tr from-blue-400/10 to-indigo-400/15 rounded-full filter blur-2xl pointer-events-none"></div>
                
                <!-- Main 3D clay-style illustration container -->
                <div class="relative w-[320px] sm:w-[400px] lg:w-[460px] aspect-square flex items-center justify-center float-illustration">
                    <img 
                        src="/images/hero_education_illustration.png" 
                        class="w-full h-full object-contain relative z-10 filter drop-shadow-[0_20px_40px_rgba(37,99,235,0.12)]" 
                        alt="SMA Negeri 2 Perbaungan"
                    />

                    <!-- Interactive floating dots/decorations -->
                    <span class="absolute top-10 left-8 w-4 h-4 bg-yellow-400 rounded-full opacity-60 animate-ping"></span>
                    <span class="absolute bottom-16 right-6 w-3 h-3 bg-blue-400 rounded-full opacity-70 animate-bounce"></span>
                </div>
            </div>
        </div>

        <!-- BOTTOM PILL CONTAINER (Core Features Bar) -->
        <div class="max-w-7xl mx-auto px-6 w-full relative z-20 mt-12 lg:mt-16">
            <div class="bg-white/95 backdrop-blur-xl border border-blue-50/50 shadow-[0_25px_60px_-15px_rgba(37,99,235,0.1)] rounded-[2.5rem] p-8 md:p-10">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-6 divide-y lg:divide-y-0 lg:divide-x divide-slate-100">
                    
                    <!-- PILLAR 1: Pendidikan Berkualitas -->
                    <div class="flex items-start gap-4 lg:px-4 pt-6 first:pt-0 lg:pt-0">
                        <div class="flex-shrink-0 w-12 h-12 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center shadow-md shadow-blue-100/50 hover:scale-110 transition-transform duration-300">
                            <!-- Book Open SVG Icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <div class="space-y-1 text-left">
                            <h4 class="font-extrabold text-slate-800 text-base leading-tight">Pendidikan Berkualitas</h4>
                            <p class="text-xs text-slate-500 font-medium leading-relaxed">Kurikulum terintegrasi dan pembelajaran inovatif untuk mengembangkan potensi siswa.</p>
                        </div>
                    </div>

                    <!-- PILLAR 2: Prestasi Membanggakan -->
                    <div class="flex items-start gap-4 lg:px-6 pt-6 lg:pt-0">
                        <div class="flex-shrink-0 w-12 h-12 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center shadow-md shadow-emerald-100/50 hover:scale-110 transition-transform duration-300">
                            <!-- Trophy SVG Icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                            </svg>
                        </div>
                        <div class="space-y-1 text-left">
                            <h4 class="font-extrabold text-slate-800 text-base leading-tight">Prestasi Membanggakan</h4>
                            <p class="text-xs text-slate-500 font-medium leading-relaxed">Mendorong siswa meraih prestasi akademik maupun non-akademik.</p>
                        </div>
                    </div>

                    <!-- PILLAR 3: Pengembangan Karakter -->
                    <div class="flex items-start gap-4 lg:px-6 pt-6 lg:pt-0">
                        <div class="flex-shrink-0 w-12 h-12 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center shadow-md shadow-amber-100/50 hover:scale-110 transition-transform duration-300">
                            <!-- Shield-Check/Users SVG Icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <div class="space-y-1 text-left">
                            <h4 class="font-extrabold text-slate-800 text-base leading-tight">Pengembangan Karakter</h4>
                            <p class="text-xs text-slate-500 font-medium leading-relaxed">Menanamkan nilai-nilai positif untuk membentuk pribadi yang berintegritas dan berakhlak mulia.</p>
                        </div>
                    </div>

                    <!-- PILLAR 4: Siap Menghadapi Masa Depan -->
                    <div class="flex items-start gap-4 lg:px-6 pt-6 lg:pt-0">
                        <div class="flex-shrink-0 w-12 h-12 bg-purple-100 text-purple-600 rounded-2xl flex items-center justify-center shadow-md shadow-purple-100/50 hover:scale-110 transition-transform duration-300">
                            <!-- Globe SVG Icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                            </svg>
                        </div>
                        <div class="space-y-1 text-left">
                            <h4 class="font-extrabold text-slate-800 text-base leading-tight">Siap Menghadapi Masa Depan</h4>
                            <p class="text-xs text-slate-500 font-medium leading-relaxed">Membekali keterampilan dan literasi untuk bersaing di dunia global.</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </section>
</template>

<style scoped>
/* Core premium animations */
@keyframes floatAnimation {
    0%, 100% {
        transform: translateY(0px) rotate(0deg);
    }
    50% {
        transform: translateY(-16px) rotate(1deg);
    }
}

.float-illustration {
    animation: floatAnimation 6.5s ease-in-out infinite;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(24px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation: fadeInUp 1s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>

