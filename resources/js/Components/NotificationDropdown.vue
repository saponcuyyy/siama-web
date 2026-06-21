<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { Bell, CheckCheck, Clock, Info, AlertTriangle, CheckCircle, ExternalLink } from 'lucide-vue-next';

const page = usePage();

const unreadCount = computed(() => page.props.notifications?.unread_count ?? 0);

const isOpen = ref(false);
const notifications = ref([]);
const loading = ref(false);

const toggleDropdown = () => {
    if (!isOpen.value) {
        fetchNotifications();
    }
    isOpen.value = !isOpen.value;
};

const fetchNotifications = async () => {
    loading.value = true;
    try {
        const url = route('admin.notifications.fetch');
        const res = await fetch(url);
        const data = await res.json();
        notifications.value = data.notifications;
    } catch {
        notifications.value = [];
    } finally {
        loading.value = false;
    }
};

const markAsRead = async (notification) => {
    try {
        await axios.post(route('admin.notifications.read', notification.id));
        notification.read_at = new Date().toISOString();
        router.reload({ only: ['notifications'], preserveState: true, preserveScroll: true });
    } catch {
        //
    }
};

const markAllRead = async () => {
    try {
        await axios.post(route('admin.notifications.read-all'));
        notifications.value.forEach(n => n.read_at = new Date().toISOString());
        router.reload({ only: ['notifications'], preserveState: true, preserveScroll: true });
    } catch {
        //
    }
};

const getNotifIcon = (type) => {
    if (type.includes('Alert') || type.includes('Warning')) return AlertTriangle;
    if (type.includes('Success')) return CheckCircle;
    return Info;
};

const getNotifColor = (type) => {
    if (type.includes('Alert') || type.includes('Warning')) return 'text-rose-500 bg-rose-50';
    if (type.includes('Success')) return 'text-emerald-500 bg-emerald-50';
    return 'text-indigo-500 bg-indigo-50';
};

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    const now = new Date();
    const diff = now - d;
    const mins = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);

    if (mins < 1) return 'Baru saja';
    if (mins < 60) return `${mins} menit lalu`;
    if (hours < 24) return `${hours} jam lalu`;
    if (days < 7) return `${days} hari lalu`;

    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
};

const parseData = (data) => {
    try {
        return typeof data === 'string' ? JSON.parse(data) : data;
    } catch {
        return {};
    }
};

const handleClickOutside = (e) => {
    if (isOpen.value && !e.target.closest('.notification-dropdown')) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div class="notification-dropdown relative">
        <button
            @click.stop="toggleDropdown"
            class="p-2 text-slate-400 hover:text-indigo-600 transition-colors relative"
        >
            <Bell class="w-5 h-5" />
            <span
                v-if="unreadCount > 0"
                class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] flex items-center justify-center bg-rose-500 text-white text-[10px] font-black rounded-full px-1 border-2 border-white shadow-sm"
            >
                {{ unreadCount > 99 ? '99+' : unreadCount }}
            </span>
        </button>

        <div
            v-if="isOpen"
            class="absolute right-0 mt-3 w-[380px] bg-white rounded-2xl shadow-2xl shadow-slate-200/50 border border-slate-100 z-50 overflow-hidden"
        >
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-black text-slate-900">Notifikasi</h3>
                    <p v-if="unreadCount > 0" class="text-[11px] font-semibold text-slate-400 mt-0.5">
                        {{ unreadCount }} belum dibaca
                    </p>
                </div>
                <button
                    v-if="unreadCount > 0"
                    @click="markAllRead"
                    class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors"
                >
                    <CheckCheck class="w-3.5 h-3.5" />
                    Baca Semua
                </button>
            </div>

            <div class="max-h-[400px] overflow-y-auto custom-scrollbar">
                <div v-if="loading" class="flex items-center justify-center py-12">
                    <div class="w-6 h-6 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin" />
                </div>

                <template v-if="!loading && notifications.length === 0">
                    <div class="flex flex-col items-center justify-center py-12 text-center px-6">
                        <Bell class="w-10 h-10 text-slate-200 mb-3" />
                        <p class="text-sm font-bold text-slate-400">Belum ada notifikasi</p>
                        <p class="text-xs text-slate-300 mt-1">Notifikasi akan muncul di sini</p>
                    </div>
                </template>

                <template v-if="!loading && notifications.length > 0">
                    <div
                        v-for="notif in notifications"
                        :key="notif.id"
                        @click="!notif.read_at && markAsRead(notif)"
                        class="flex items-start gap-3 px-5 py-4 transition-colors cursor-pointer border-b border-slate-50 last:border-0"
                        :class="notif.read_at ? 'hover:bg-slate-50' : 'bg-indigo-50/30 hover:bg-indigo-50/60'"
                    >
                        <div
                            class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5"
                            :class="getNotifColor(notif.type)"
                        >
                            <component :is="getNotifIcon(notif.type)" class="w-[18px] h-[18px]" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p
                                class="text-sm"
                                :class="notif.read_at ? 'text-slate-600' : 'text-slate-900 font-bold'"
                            >
                                {{ parseData(notif.data).title || 'Notifikasi' }}
                            </p>
                            <p class="text-xs mt-0.5 leading-relaxed" :class="notif.read_at ? 'text-slate-400' : 'text-slate-500'">
                                {{ parseData(notif.data).message || '' }}
                            </p>
                            <div class="flex items-center gap-3 mt-1.5">
                                <span class="text-[10px] font-semibold text-slate-400 flex items-center gap-1">
                                    <Clock class="w-3 h-3" />
                                    {{ formatDate(notif.created_at) }}
                                </span>
                                <a
                                    v-if="parseData(notif.data).url"
                                    :href="parseData(notif.data).url"
                                    class="text-[10px] font-bold text-indigo-600 hover:text-indigo-700 flex items-center gap-0.5"
                                >
                                    <ExternalLink class="w-3 h-3" />
                                    Lihat
                                </a>
                            </div>
                        </div>
                        <div v-if="!notif.read_at" class="w-2 h-2 bg-indigo-500 rounded-full flex-shrink-0 mt-2" />
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
</style>
