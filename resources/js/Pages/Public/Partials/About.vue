<script setup>
import { sanitize } from '@/sanitize';

defineProps({
    about: Object,
    visiMisi: Object,
    settings: Object,
    total_siswa: Number,
    total_guru: Number,
    total_fasilitas: Number
});
</script>

<template>
    <section id="about" class="py-24 bg-white overflow-hidden">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-20 items-center">
                <!-- Image Side -->
                <div class="relative">
                    <div class="aspect-[4/5] rounded-[3rem] overflow-hidden shadow-2xl relative z-10 border-8 border-white group bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 flex items-center justify-center">
                        <img src="/images/about_school_illustration.png" 
                            class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-700 p-4" 
                            alt="Proses Belajar SMA Negeri 2 Perbaungan">
                    </div>
                    <!-- Decorative Elements -->
                    <div class="absolute -top-10 -left-10 w-40 h-40 bg-blue-100 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-pulse"></div>
                    <div class="absolute -bottom-10 -right-10 w-60 h-60 bg-purple-100 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-pulse delay-700"></div>
                    
                    <!-- Premium Gold-Medal Accreditation Card -->
                    <div class="absolute -bottom-6 -left-8 z-20 bg-white/95 backdrop-blur-md px-6 py-5 rounded-[2rem] shadow-[0_20px_50px_rgba(37,99,235,0.12)] border border-blue-50/80 flex items-center gap-5 hover:scale-105 hover:-translate-y-2 transition-all duration-500 float-badge cursor-default">
                        
                        <!-- Shiny Gold Medal Icon Wrapper -->
                        <div class="w-14 h-14 bg-gradient-to-tr from-amber-500 via-yellow-400 to-amber-600 rounded-2xl flex items-center justify-center shadow-lg shadow-amber-500/30 border-2 border-yellow-200/50 relative overflow-hidden group">
                            <!-- Shimmer light reflection effect -->
                            <div class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/40 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                            
                            <!-- Certificate Trophy/Check SVG Icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-white filter drop-shadow-[0_2px_4px_rgba(0,0,0,0.1)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                            </svg>
                        </div>

                        <!-- Details -->
                        <div class="space-y-1 text-left">
                            <div class="flex items-center gap-1.5">
                                <span class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-400">Terakreditasi</span>
                                <!-- Verified Check Seal -->
                                <span class="flex-shrink-0 w-3.5 h-3.5 bg-blue-500 text-white rounded-full flex items-center justify-center shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-2 h-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                </span>
                            </div>
                            
                            <div class="flex items-baseline gap-2">
                                <span class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-amber-600 via-amber-500 to-yellow-500 leading-none">
                                    {{ settings?.akreditasi || 'A' }}
                                </span>
                                <span class="text-[10px] font-extrabold text-slate-700 uppercase tracking-wider">Peringkat Unggul</span>
                            </div>
                            
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider leading-none">BAN-PDM Kemendikbud</p>
                        </div>
                    </div>
                </div>

                <!-- Text Side -->
                <div class="space-y-8">
                    <div class="space-y-4">
                        <span class="text-blue-600 font-black uppercase tracking-[0.3em] text-xs">Tentang Kami</span>
                        <h2 class="text-4xl md:text-5xl font-black text-slate-900 leading-tight">
                            Membangun Masa Depan Gemilang Sejak Tahun {{ settings?.tahun_berdiri || '2004' }}
                        </h2>
                    </div>
                    <div class="prose prose-slate prose-lg max-w-none text-slate-600 font-medium leading-relaxed" v-html="sanitize(about?.content || 'SMA Negeri 2 Perbaungan berkomitmen memberikan pendidikan terbaik.')"></div>
                    
                    <!-- Stats Grid -->
                    <div class="grid grid-cols-3 gap-8 pt-6 border-t border-slate-100">
                        <div class="text-center">
                            <p class="text-3xl font-black text-slate-900 leading-none">{{ total_siswa || 0 }}+</p>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">Siswa</p>
                        </div>
                        <div class="text-center">
                            <p class="text-3xl font-black text-slate-900 leading-none">{{ total_guru || 0 }}+</p>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">Guru</p>
                        </div>
                        <div class="text-center">
                            <p class="text-3xl font-black text-slate-900 leading-none">{{ total_fasilitas || 0 }}+</p>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">Fasilitas</p>
                        </div>
                    </div>

                    <!-- Kepala Sekolah & NPSN Card -->
                    <div v-if="settings?.kepala_sekolah || settings?.npsn" class="bg-slate-50 border border-slate-100 rounded-3xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 mt-6">
                        <div v-if="settings?.kepala_sekolah" class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-blue-600/10 text-blue-600 flex items-center justify-center font-black text-lg">
                                👨‍🏫
                            </div>
                            <div>
                                <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Kepala Sekolah</p>
                                <p class="text-slate-900 font-bold text-sm leading-tight mt-0.5">{{ settings.kepala_sekolah }}</p>
                                <p v-if="settings.nip_kepala" class="text-slate-400 text-xs mt-0.5">NIP. {{ settings.nip_kepala }}</p>
                            </div>
                        </div>
                        <div v-if="settings?.npsn" class="text-left sm:text-right border-t sm:border-t-0 sm:border-l border-slate-200/80 pt-4 sm:pt-0 sm:pl-6 flex-shrink-0">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">NPSN Sekolah</p>
                            <p class="text-slate-950 font-black text-sm tracking-wider mt-0.5">{{ settings.npsn }}</p>
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
                    <div class="prose prose-invert prose-lg max-w-none text-slate-300 font-medium" v-html="sanitize(visiMisi?.content || 'Mewujudkan lulusan yang cerdas dan kompetitif.')"></div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
@keyframes floatBadge {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-8px);
    }
}

.float-badge {
    animation: floatBadge 5s ease-in-out infinite;
}
</style>
