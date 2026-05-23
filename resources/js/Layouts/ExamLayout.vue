<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

const page = usePage();
const user = page.props.auth.user;
const isLogoutOpen = ref(false);

function onDocClick(e) {
    if (isLogoutOpen.value) isLogoutOpen.value = false;
}
onMounted(() => document.addEventListener('click', onDocClick));
onUnmounted(() => document.removeEventListener('click', onDocClick));
</script>

<template>
    <div class="min-h-screen bg-slate-50 flex flex-col">
        <!-- Minimal Top Bar -->
        <header class="h-14 bg-white border-b border-slate-200 flex items-center justify-between px-4 md:px-6 flex-shrink-0">
            <div class="flex items-center gap-3">
                <img src="/images/logo.png" alt="Logo" class="w-8 h-8 object-contain" />
                <span class="font-bold text-slate-800 text-sm tracking-tight">SIAMA — Ujian Online</span>
            </div>

            <div class="flex items-center gap-3">
                <span class="text-xs font-semibold text-slate-500 hidden sm:block">{{ user.name }}</span>
                <div class="relative" @click.stop>
                    <button
                        @click="isLogoutOpen = !isLogoutOpen"
                        class="w-8 h-8 bg-indigo-100 rounded-full overflow-hidden border border-indigo-200 hover:border-indigo-400 transition-colors"
                    >
                        <img :src="`https://ui-avatars.com/api/?name=${user.name}&background=6366f1&color=fff`" alt="" class="w-full h-full object-cover" />
                    </button>
                    <div
                        v-if="isLogoutOpen"
                        class="absolute right-0 mt-2 w-44 bg-white rounded-xl shadow-xl border border-slate-100 py-1.5 z-50"
                    >
                        <div class="px-4 py-1.5 border-b border-slate-50 mb-1">
                            <p class="text-xs font-bold text-slate-500">{{ user.name }}</p>
                            <p class="text-[10px] text-indigo-600 font-semibold uppercase">{{ user.roles[0] }}</p>
                        </div>
                        <Link :href="route('ujian.index')" class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Daftar Ujian
                        </Link>
                        <div class="border-t border-slate-100 my-1"></div>
                        <Link :href="route('ujian.logout')" method="post" as="button" class="w-full flex items-center gap-2 px-4 py-2 text-sm font-bold text-rose-600 hover:bg-rose-50 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Keluar
                        </Link>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 overflow-y-auto p-4 md:p-8">
            <slot />
        </main>
    </div>
</template>
