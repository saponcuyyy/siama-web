<script setup>
import AdminWebLayout from '@/Layouts/AdminWebLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';
import { onMounted, ref, nextTick, watch } from 'vue';

const props = defineProps({
    settings: Object,
});

const activeTab = ref('identitas');
const mapInstance = ref(null);
const markerInstance = ref(null);

const setTab = (tabName) => {
    activeTab.value = tabName;
    if (tabName === 'lokasi') {
        nextTick(() => {
            setTimeout(() => {
                if (mapInstance.value) {
                    mapInstance.value.invalidateSize();
                }
            }, 200);
        });
    }
};

const form = useForm({
    nama_sekolah: props.settings?.nama_sekolah || '',
    tagline: props.settings?.tagline || '',
    meta_description: props.settings?.meta_description || '',
    tahun_berdiri: props.settings?.tahun_berdiri || '',
    alamat: props.settings?.alamat || '',
    telepon: props.settings?.telepon || '',
    email: props.settings?.email || '',
    website: props.settings?.website || '',
    facebook: props.settings?.facebook || '',
    instagram: props.settings?.instagram || '',
    youtube: props.settings?.youtube || '',
    tiktok: props.settings?.tiktok || '',
    kepala_sekolah: props.settings?.kepala_sekolah || '',
    nip_kepala: props.settings?.nip_kepala || '',
    npsn: props.settings?.npsn || '',
    akreditasi: props.settings?.akreditasi || '',
    jam_operasional: props.settings?.jam_operasional || 'Senin - Jumat, 07:30 - 15:30',
    map_latitude: props.settings?.map_latitude || '-6.200000',
    map_longitude: props.settings?.map_longitude || '106.816666',
    map_zoom: props.settings?.map_zoom || '15',
    map_label: props.settings?.map_label || 'Lokasi Sekolah',
});

onMounted(() => {
    // Fix Leaflet marker icon issue
    delete L.Icon.Default.prototype._getIconUrl;
    L.Icon.Default.mergeOptions({
        iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png',
        iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon-2x.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
    });

    nextTick(() => {
        const container = document.getElementById('map-preview');
        if (container) {
            mapInstance.value = L.map(container).setView([parseFloat(form.map_latitude), parseFloat(form.map_longitude)], parseInt(form.map_zoom));

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(mapInstance.value);

            markerInstance.value = L.marker([parseFloat(form.map_latitude), parseFloat(form.map_longitude)], {
                draggable: true
            }).addTo(mapInstance.value);

            markerInstance.value.on('dragend', (e) => {
                const pos = e.target.getLatLng();
                form.map_latitude = pos.lat.toFixed(6);
                form.map_longitude = pos.lng.toFixed(6);
            });

            mapInstance.value.on('click', (e) => {
                const pos = e.latlng;
                form.map_latitude = pos.lat.toFixed(6);
                form.map_longitude = pos.lng.toFixed(6);
                markerInstance.value.setLatLng(pos);
            });

            setTimeout(() => {
                if (mapInstance.value) mapInstance.value.invalidateSize();
            }, 500);
        }
    });
});

// Sync marker when form inputs change
watch(() => [form.map_latitude, form.map_longitude], ([lat, lng]) => {
    if (markerInstance.value && mapInstance.value) {
        const newPos = [parseFloat(lat), parseFloat(lng)];
        markerInstance.value.setLatLng(newPos);
        mapInstance.value.panTo(newPos);
    }
});

watch(() => form.map_zoom, (zoom) => {
    if (mapInstance.value) {
        mapInstance.value.setZoom(parseInt(zoom));
    }
});

const submit = () => {
    form.put(route('admin.web.setting.update'));
};
</script>

<template>
    <AdminWebLayout>
        <Head title="Pengaturan Website" />

        <div class="max-w-6xl mx-auto space-y-6">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-5">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">⚙️ Pengaturan Website</h1>
                    <p class="text-slate-500 text-sm mt-1">Konfigurasi identitas, informasi kontak, SEO, dan lokasi peta sekolah.</p>
                </div>
            </div>

            <!-- Tabbed Form Grid -->
            <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <!-- Left Sidebar Navigation -->
                <div class="lg:col-span-3 space-y-2 lg:sticky lg:top-6">
                    <button
                        type="button"
                        @click="setTab('identitas')"
                        :class="[
                            'w-full flex items-center gap-3 px-4 py-3.5 rounded-2xl text-sm font-bold transition-all text-left',
                            activeTab === 'identitas'
                                ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20'
                                : 'bg-white text-slate-700 border border-slate-200/80 hover:bg-slate-50 hover:border-slate-300'
                        ]"
                    >
                        <span class="text-lg">🏫</span> Identitas Sekolah
                    </button>
                    
                    <button
                        type="button"
                        @click="setTab('kepala')"
                        :class="[
                            'w-full flex items-center gap-3 px-4 py-3.5 rounded-2xl text-sm font-bold transition-all text-left',
                            activeTab === 'kepala'
                                ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20'
                                : 'bg-white text-slate-700 border border-slate-200/80 hover:bg-slate-50 hover:border-slate-300'
                        ]"
                    >
                        <span class="text-lg">👤</span> Kepala Sekolah
                    </button>

                    <button
                        type="button"
                        @click="setTab('kontak')"
                        :class="[
                            'w-full flex items-center gap-3 px-4 py-3.5 rounded-2xl text-sm font-bold transition-all text-left',
                            activeTab === 'kontak'
                                ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20'
                                : 'bg-white text-slate-700 border border-slate-200/80 hover:bg-slate-50 hover:border-slate-300'
                        ]"
                    >
                        <span class="text-lg">📍</span> Kontak & Sosmed
                    </button>

                    <button
                        type="button"
                        @click="setTab('lokasi')"
                        :class="[
                            'w-full flex items-center gap-3 px-4 py-3.5 rounded-2xl text-sm font-bold transition-all text-left',
                            activeTab === 'lokasi'
                                ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20'
                                : 'bg-white text-slate-700 border border-slate-200/80 hover:bg-slate-50 hover:border-slate-300'
                        ]"
                    >
                        <span class="text-lg">🗺️</span> Lokasi & Peta
                    </button>

                    <button
                        type="button"
                        @click="setTab('seo')"
                        :class="[
                            'w-full flex items-center gap-3 px-4 py-3.5 rounded-2xl text-sm font-bold transition-all text-left',
                            activeTab === 'seo'
                                ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20'
                                : 'bg-white text-slate-700 border border-slate-200/80 hover:bg-slate-50 hover:border-slate-300'
                        ]"
                    >
                        <span class="text-lg">🌐</span> SEO & Meta
                    </button>
                </div>

                <!-- Right Form Contents Card -->
                <div class="lg:col-span-9 bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col min-h-[500px]">
                    <div class="p-6 md:p-8 flex-1 space-y-6">
                        
                        <!-- TAB: Identitas Sekolah -->
                        <div v-show="activeTab === 'identitas'" class="space-y-6 animate-fade-in">
                            <div class="border-b border-slate-100 pb-3">
                                <h3 class="text-lg font-extrabold text-slate-900">🏫 Identitas Sekolah</h3>
                                <p class="text-slate-400 text-xs">Informasi dasar operasional dan nama resmi sekolah.</p>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="space-y-1.5 md:col-span-2">
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-400">Nama Sekolah</label>
                                    <div class="relative rounded-2xl">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-base">
                                            🏫
                                        </div>
                                        <input v-model="form.nama_sekolah" type="text" class="w-full pl-11 pr-4 py-3 bg-slate-50/50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white text-sm font-semibold text-slate-800 transition-all duration-200" required>
                                    </div>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-400">Tagline / Slogan</label>
                                    <div class="relative rounded-2xl">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-base">
                                            ✨
                                        </div>
                                        <input v-model="form.tagline" type="text" class="w-full pl-11 pr-4 py-3 bg-slate-50/50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white text-sm font-semibold text-slate-800 transition-all duration-200" placeholder="Mencetak generasi unggul dan berkarakter">
                                    </div>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-400">Tahun Berdiri</label>
                                    <div class="relative rounded-2xl">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-base">
                                            📅
                                        </div>
                                        <input v-model="form.tahun_berdiri" type="number" min="1900" max="2100" class="w-full pl-11 pr-4 py-3 bg-slate-50/50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white text-sm font-semibold text-slate-800 transition-all duration-200" placeholder="2004">
                                    </div>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-400">NPSN</label>
                                    <div class="relative rounded-2xl">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-base">
                                            🆔
                                        </div>
                                        <input v-model="form.npsn" type="text" class="w-full pl-11 pr-4 py-3 bg-slate-50/50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white text-sm font-semibold text-slate-800 transition-all duration-200">
                                    </div>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-400">Akreditasi</label>
                                    <div class="relative rounded-2xl">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-base">
                                            🏆
                                        </div>
                                        <input v-model="form.akreditasi" type="text" class="w-full pl-11 pr-4 py-3 bg-slate-50/50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white text-sm font-semibold text-slate-800 transition-all duration-200" placeholder="A">
                                    </div>
                                </div>
                                <div class="space-y-1.5 md:col-span-2">
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-400">Jam Operasional</label>
                                    <div class="relative rounded-2xl">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-base">
                                            ⏰
                                        </div>
                                        <input v-model="form.jam_operasional" type="text" class="w-full pl-11 pr-4 py-3 bg-slate-50/50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white text-sm font-semibold text-slate-800 transition-all duration-200" placeholder="Senin - Jumat, 07:30 - 15:30">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB: Kepala Sekolah -->
                        <div v-show="activeTab === 'kepala'" class="space-y-6 animate-fade-in">
                            <div class="border-b border-slate-100 pb-3">
                                <h3 class="text-lg font-extrabold text-slate-900">👤 Kepala Sekolah</h3>
                                <p class="text-slate-400 text-xs">Informasi pimpinan lembaga sekolah yang aktif.</p>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-400">Nama Kepala Sekolah</label>
                                    <div class="relative rounded-2xl">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-base">
                                            👨‍🏫
                                        </div>
                                        <input v-model="form.kepala_sekolah" type="text" class="w-full pl-11 pr-4 py-3 bg-slate-50/50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white text-sm font-semibold text-slate-800 transition-all duration-200">
                                    </div>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-400">NIP Kepala Sekolah</label>
                                    <div class="relative rounded-2xl">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-base">
                                            💳
                                        </div>
                                        <input v-model="form.nip_kepala" type="text" class="w-full pl-11 pr-4 py-3 bg-slate-50/50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white text-sm font-semibold text-slate-800 transition-all duration-200">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB: Kontak & Sosmed -->
                        <div v-show="activeTab === 'kontak'" class="space-y-6 animate-fade-in">
                            <div class="border-b border-slate-100 pb-3">
                                <h3 class="text-lg font-extrabold text-slate-900">📍 Kontak & Sosial Media</h3>
                                <p class="text-slate-400 text-xs">Kontak resmi sekolah dan tautan jejaring sosial media.</p>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="space-y-1.5 md:col-span-2">
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-400">Alamat Lengkap</label>
                                    <div class="relative rounded-2xl">
                                        <div class="absolute top-3 left-4 pointer-events-none text-base">
                                            📍
                                        </div>
                                        <textarea v-model="form.alamat" rows="2" class="w-full pl-11 pr-4 py-3 bg-slate-50/50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white text-sm font-semibold text-slate-800 transition-all duration-200" required></textarea>
                                    </div>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-400">No. Telepon / WhatsApp</label>
                                    <div class="relative rounded-2xl">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-base">
                                            📞
                                        </div>
                                        <input v-model="form.telepon" type="text" class="w-full pl-11 pr-4 py-3 bg-slate-50/50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white text-sm font-semibold text-slate-800 transition-all duration-200">
                                    </div>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-400">Email Resmi</label>
                                    <div class="relative rounded-2xl">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-base">
                                            ✉️
                                        </div>
                                        <input v-model="form.email" type="email" class="w-full pl-11 pr-4 py-3 bg-slate-50/50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white text-sm font-semibold text-slate-800 transition-all duration-200">
                                    </div>
                                </div>
                                <div class="space-y-1.5 md:col-span-2">
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-400">Website Resmi (URL)</label>
                                    <div class="relative rounded-2xl">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-base">
                                            🌐
                                        </div>
                                        <input v-model="form.website" type="url" class="w-full pl-11 pr-4 py-3 bg-slate-50/50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white text-sm font-semibold text-slate-800 transition-all duration-200" placeholder="https://sman2perbaungan.sch.id">
                                    </div>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-400">URL Facebook</label>
                                    <div class="relative rounded-2xl">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-base">
                                            📘
                                        </div>
                                        <input v-model="form.facebook" type="url" class="w-full pl-11 pr-4 py-3 bg-slate-50/50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white text-sm font-semibold text-slate-800 transition-all duration-200" placeholder="https://facebook.com/sekolah">
                                    </div>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-400">URL Instagram</label>
                                    <div class="relative rounded-2xl">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-base">
                                            📸
                                        </div>
                                        <input v-model="form.instagram" type="url" class="w-full pl-11 pr-4 py-3 bg-slate-50/50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white text-sm font-semibold text-slate-800 transition-all duration-200" placeholder="https://instagram.com/sekolah">
                                    </div>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-400">URL YouTube</label>
                                    <div class="relative rounded-2xl">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-base">
                                            🎥
                                        </div>
                                        <input v-model="form.youtube" type="url" class="w-full pl-11 pr-4 py-3 bg-slate-50/50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white text-sm font-semibold text-slate-800 transition-all duration-200" placeholder="https://youtube.com/channel">
                                    </div>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-400">URL TikTok</label>
                                    <div class="relative rounded-2xl">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-base">
                                            🎵
                                        </div>
                                        <input v-model="form.tiktok" type="url" class="w-full pl-11 pr-4 py-3 bg-slate-50/50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white text-sm font-semibold text-slate-800 transition-all duration-200" placeholder="https://tiktok.com/@sekolah">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB: Lokasi & Peta -->
                        <div v-show="activeTab === 'lokasi'" class="space-y-6 animate-fade-in">
                            <div class="border-b border-slate-100 pb-3">
                                <h3 class="text-lg font-extrabold text-slate-900">🗺️ Lokasi & Peta (Leaflet)</h3>
                                <p class="text-slate-400 text-xs">Atur titik geografis sekolah pada peta OpenStreetMap.</p>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-400">Latitude</label>
                                    <div class="relative rounded-2xl">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-base">
                                            🌐
                                        </div>
                                        <input v-model="form.map_latitude" type="number" step="0.000001" class="w-full pl-11 pr-4 py-3 bg-slate-50/50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white text-sm font-semibold text-slate-800 transition-all duration-200" required>
                                    </div>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-400">Longitude</label>
                                    <div class="relative rounded-2xl">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-base">
                                            🌐
                                        </div>
                                        <input v-model="form.map_longitude" type="number" step="0.000001" class="w-full pl-11 pr-4 py-3 bg-slate-50/50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white text-sm font-semibold text-slate-800 transition-all duration-200" required>
                                    </div>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-400">Zoom Level (1-19)</label>
                                    <div class="relative rounded-2xl">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-base">
                                            🔍
                                        </div>
                                        <input v-model="form.map_zoom" type="number" min="1" max="19" class="w-full pl-11 pr-4 py-3 bg-slate-50/50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white text-sm font-semibold text-slate-800 transition-all duration-200" required>
                                    </div>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-400">Label Marker</label>
                                    <div class="relative rounded-2xl">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-base">
                                            🏷️
                                        </div>
                                        <input v-model="form.map_label" type="text" class="w-full pl-11 pr-4 py-3 bg-slate-50/50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white text-sm font-semibold text-slate-800 transition-all duration-200" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="space-y-2 pt-2 border-t border-slate-100">
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-400">Preview Peta Interaktif</label>
                                <div id="map-preview" class="h-[350px] rounded-2xl border border-slate-200 overflow-hidden shadow-inner bg-slate-50 z-0"></div>
                                <p class="text-[11px] text-slate-400 font-bold italic">💡 Tips: Klik area peta atau drag pin marker untuk mengambil koordinat secara presisi.</p>
                            </div>
                        </div>

                        <!-- TAB: SEO & Meta -->
                        <div v-show="activeTab === 'seo'" class="space-y-6 animate-fade-in">
                            <div class="border-b border-slate-100 pb-3">
                                <h3 class="text-lg font-extrabold text-slate-900">🌐 SEO & Meta</h3>
                                <p class="text-slate-400 text-xs">Optimalkan pencarian website di Google Search.</p>
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-400">Meta Description</label>
                                <div class="relative rounded-2xl">
                                    <div class="absolute top-3 left-4 pointer-events-none text-base">
                                        📝
                                    </div>
                                    <textarea v-model="form.meta_description" rows="4" class="w-full pl-11 pr-4 py-3 bg-slate-50/50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white text-sm font-semibold text-slate-800 transition-all duration-200" placeholder="Masukkan deskripsi sekolah singkat (maks. 160 karakter)..."></textarea>
                                </div>
                                <div class="flex justify-between items-center text-[11px] font-bold text-slate-400">
                                    <span>Gunakan kalimat yang informatif dan relevan.</span>
                                    <span>{{ form.meta_description.length }} karakter</span>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Footer Action Bar -->
                    <div class="bg-slate-50 px-6 py-4 border-t border-slate-100 flex items-center justify-between">
                        <span v-if="form.isDirty" class="text-amber-600 text-xs font-bold flex items-center gap-1.5 animate-pulse">
                            ⚠️ Ada perubahan yang belum disimpan
                        </span>
                        <span v-else class="text-slate-400 text-xs font-semibold">
                            Semua pengaturan up-to-date
                        </span>
                        
                        <button type="submit" :disabled="form.processing" class="bg-blue-600 text-white px-7 py-2.5 rounded-xl font-bold text-sm hover:bg-blue-700 transition-all shadow-md shadow-blue-200 disabled:opacity-50 flex items-center gap-2">
                            <span>{{ form.processing ? 'Menyimpan...' : '💾 Simpan Pengaturan' }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AdminWebLayout>
</template>
