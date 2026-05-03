<script setup>
import AdminWebLayout from '@/Layouts/AdminWebLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';
import { onMounted, ref, nextTick, watch } from 'vue';

const props = defineProps({
    settings: Object,
});

const mapInstance = ref(null);
const markerInstance = ref(null);

const form = useForm({
    nama_sekolah: props.settings?.nama_sekolah || '',
    tagline: props.settings?.tagline || '',
    alamat: props.settings?.alamat || '',
    telepon: props.settings?.telepon || '',
    email: props.settings?.email || '',
    facebook: props.settings?.facebook || '',
    instagram: props.settings?.instagram || '',
    youtube: props.settings?.youtube || '',
    kepala_sekolah: props.settings?.kepala_sekolah || '',
    nip_kepala: props.settings?.nip_kepala || '',
    npsn: props.settings?.npsn || '',
    akreditasi: props.settings?.akreditasi || '',
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

            setTimeout(() => mapInstance.value.invalidateSize(), 500);
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

        <div class="max-w-4xl mx-auto space-y-6">
            <div>
                <h1 class="text-xl font-black text-slate-900">Pengaturan Website</h1>
                <p class="text-slate-500 text-sm">Konfigurasi identitas, informasi kontak, dan peta sekolah.</p>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-10">
                <div class="p-6 space-y-8">
                    <!-- ... Identitas Sekolah ... -->
                    <section class="space-y-6">
                        <h2 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-2">🏫 Identitas Sekolah</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-1">
                                <label class="block text-sm font-semibold text-slate-700">Nama Sekolah</label>
                                <input v-model="form.nama_sekolah" type="text" class="w-full border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500" required>
                            </div>
                            <div class="space-y-1">
                                <label class="block text-sm font-semibold text-slate-700">Tagline / Slogan</label>
                                <input v-model="form.tagline" type="text" class="w-full border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div class="space-y-1">
                                <label class="block text-sm font-semibold text-slate-700">NPSN</label>
                                <input v-model="form.npsn" type="text" class="w-full border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div class="space-y-1">
                                <label class="block text-sm font-semibold text-slate-700">Akreditasi</label>
                                <input v-model="form.akreditasi" type="text" class="w-full border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </section>

                    <section class="space-y-6">
                        <h2 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-2">👤 Kepala Sekolah</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-1">
                                <label class="block text-sm font-semibold text-slate-700">Nama Kepala Sekolah</label>
                                <input v-model="form.kepala_sekolah" type="text" class="w-full border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div class="space-y-1">
                                <label class="block text-sm font-semibold text-slate-700">NIP</label>
                                <input v-model="form.nip_kepala" type="text" class="w-full border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </section>

                    <section class="space-y-6">
                        <h2 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-2">📍 Kontak & Sosial Media</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-1 md:col-span-2">
                                <label class="block text-sm font-semibold text-slate-700">Alamat Lengkap</label>
                                <textarea v-model="form.alamat" rows="2" class="w-full border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500" required></textarea>
                            </div>
                            <div class="space-y-1">
                                <label class="block text-sm font-semibold text-slate-700">No. Telepon</label>
                                <input v-model="form.telepon" type="text" class="w-full border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div class="space-y-1">
                                <label class="block text-sm font-semibold text-slate-700">Email Resmi</label>
                                <input v-model="form.email" type="email" class="w-full border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div class="space-y-1">
                                <label class="block text-sm font-semibold text-slate-700">URL Facebook</label>
                                <input v-model="form.facebook" type="url" class="w-full border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div class="space-y-1">
                                <label class="block text-sm font-semibold text-slate-700">URL Instagram</label>
                                <input v-model="form.instagram" type="url" class="w-full border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div class="space-y-1">
                                <label class="block text-sm font-semibold text-slate-700">URL YouTube</label>
                                <input v-model="form.youtube" type="url" class="w-full border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </section>

                    <section class="space-y-6">
                        <h2 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-2">🗺️ Lokasi & Peta (Leaflet)</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-1">
                                <label class="block text-sm font-semibold text-slate-700">Latitude</label>
                                <input v-model="form.map_latitude" type="number" step="0.000001" class="w-full border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500" required>
                            </div>
                            <div class="space-y-1">
                                <label class="block text-sm font-semibold text-slate-700">Longitude</label>
                                <input v-model="form.map_longitude" type="number" step="0.000001" class="w-full border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500" required>
                            </div>
                            <div class="space-y-1">
                                <label class="block text-sm font-semibold text-slate-700">Zoom Level (1-19)</label>
                                <input v-model="form.map_zoom" type="number" min="1" max="19" class="w-full border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500" required>
                            </div>
                            <div class="space-y-1">
                                <label class="block text-sm font-semibold text-slate-700">Label Marker</label>
                                <input v-model="form.map_label" type="text" class="w-full border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500" required>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-slate-700">Preview Peta</label>
                            <div id="map-preview" class="h-[400px] rounded-2xl border border-slate-200 overflow-hidden shadow-inner bg-slate-50"></div>
                            <p class="text-xs text-slate-500 italic">💡 Klik pada peta atau geser marker untuk memperbarui koordinat secara otomatis.</p>
                        </div>
                    </section>
                </div>

                <div class="bg-slate-50 px-6 py-4 flex justify-end">
                    <button type="submit" :disabled="form.processing" class="bg-blue-600 text-white px-8 py-3 rounded-xl font-bold text-sm hover:bg-blue-700 transition-all shadow-lg shadow-blue-200 disabled:opacity-50">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Semua Pengaturan' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminWebLayout>
</template>
