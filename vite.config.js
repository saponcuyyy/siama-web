import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        tailwindcss(),
    ],
    optimizeDeps: {
        include: ['leaflet', '@vue-leaflet/vue-leaflet']
    },
    server: {
        watch: {
            usePolling: true,
            ignored: [
                '**/storage/framework/views/**',
                '**/vendor/**',
                '**/node_modules/**',
            ],
        },
    },
});
