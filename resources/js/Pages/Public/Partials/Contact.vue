<script setup>
import { onMounted, ref, nextTick } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const props = defineProps({
    settings: Object
});

const mapInstance = ref(null);

onMounted(() => {
    // Check if we have coordinates
    const lat = parseFloat(props.settings?.map_latitude || -6.200000);
    const lng = parseFloat(props.settings?.map_longitude || 106.816666);
    const zoom = parseInt(props.settings?.map_zoom || 15);
    const label = props.settings?.map_label || 'Lokasi';

    // Fix Leaflet marker icon issue
    delete L.Icon.Default.prototype._getIconUrl;
    L.Icon.Default.mergeOptions({
        iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png',
        iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon-2x.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
    });

    nextTick(() => {
        const container = document.getElementById('map-viewport');
        if (container) {
            // Clean up existing instance if any
            if (mapInstance.value) {
                mapInstance.value.remove();
            }

            mapInstance.value = L.map(container, {
                scrollWheelZoom: false
            }).setView([lat, lng], zoom);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(mapInstance.value);

            L.marker([lat, lng])
                .addTo(mapInstance.value)
                .bindPopup(`<b>${label}</b>`)
                .openPopup();
            
            // Force redraw multiple times
            setTimeout(() => mapInstance.value.invalidateSize(), 500);
            setTimeout(() => mapInstance.value.invalidateSize(), 1500);
        }
    });
});
</script>

<template>
    <section id="contact" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                <!-- Info Side -->
                <div class="space-y-12">
                    <div class="space-y-4">
                        <span class="text-blue-600 font-black uppercase tracking-[0.3em] text-xs">Hubungi Kami</span>
                        <h2 class="text-4xl md:text-5xl font-black text-slate-900 leading-tight">Mari Bergabung & Tumbuh Bersama Kami</h2>
                        <p class="text-slate-500 font-medium text-lg leading-relaxed">
                            Punya pertanyaan tentang pendaftaran atau kegiatan sekolah? Jangan ragu untuk menghubungi tim administrasi kami.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Alamat</p>
                            <p class="text-slate-900 font-bold leading-relaxed">{{ settings?.alamat }}</p>
                        </div>
                        <div class="space-y-2">
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Telepon / WhatsApp</p>
                            <p class="text-slate-900 font-bold">{{ settings?.telepon }}</p>
                        </div>
                        <div class="space-y-2">
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Email Resmi</p>
                            <p class="text-slate-900 font-bold">{{ settings?.email }}</p>
                        </div>
                        <div class="space-y-2">
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Jam Operasional</p>
                            <p class="text-slate-900 font-bold">{{ settings?.jam_operasional || 'Senin - Jumat, 07:30 - 15:30' }}</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-4 pt-4">
                        <a v-if="settings?.website" :href="settings.website" target="_blank" class="w-12 h-12 bg-slate-100 text-slate-600 rounded-2xl flex items-center justify-center hover:bg-emerald-600 hover:text-white transition-all font-black text-xs" title="Website Resmi">WWW</a>
                        <a v-if="settings?.facebook" :href="settings.facebook" target="_blank" class="w-12 h-12 bg-slate-100 text-slate-600 rounded-2xl flex items-center justify-center hover:bg-blue-600 hover:text-white transition-all font-black text-xs" title="Facebook">FB</a>
                        <a v-if="settings?.instagram" :href="settings.instagram" target="_blank" class="w-12 h-12 bg-slate-100 text-slate-600 rounded-2xl flex items-center justify-center hover:bg-pink-600 hover:text-white transition-all font-black text-xs" title="Instagram">IG</a>
                        <a v-if="settings?.youtube" :href="settings.youtube" target="_blank" class="w-12 h-12 bg-slate-100 text-slate-600 rounded-2xl flex items-center justify-center hover:bg-red-600 hover:text-white transition-all font-black text-xs" title="YouTube">YT</a>
                        <a v-if="settings?.tiktok" :href="settings.tiktok" target="_blank" class="w-12 h-12 bg-slate-100 text-slate-600 rounded-2xl flex items-center justify-center hover:bg-black hover:text-white transition-all font-black text-xs" title="TikTok">TK</a>
                    </div>
                </div>

                <!-- Map Side -->
                <div class="relative group">
                    <div class="absolute -inset-4 bg-blue-100 rounded-[3rem] rotate-3 group-hover:rotate-0 transition-transform duration-500"></div>
                    <div class="relative w-full h-[500px] bg-slate-100 rounded-[2.5rem] overflow-hidden shadow-2xl border-8 border-white z-10">
                        <div id="map-viewport" class="w-full h-full" style="min-height: 500px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style>
@reference "../../../../css/app.css";

.leaflet-popup-content-wrapper {
    @apply rounded-2xl shadow-xl border-none;
}
.leaflet-popup-tip {
    @apply shadow-none;
}
#map-viewport {
    background: #f1f5f9;
}
</style>
