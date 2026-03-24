import path from 'node:path';
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';
import { ViteImageOptimizer } from 'vite-plugin-image-optimizer';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.jsx'],
            refresh: true,
        }),
        react(),
        tailwindcss(),
        ViteImageOptimizer({
            png: { quality: 80 },
            jpeg: { quality: 75 },
            webp: { lossy: true, quality: 75 },
            avif: { lossy: true, quality: 70 },
        }),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
        },
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('pusher-js') || id.includes('laravel-echo')) {
                        return 'vendor-realtime';
                    }

                    if (id.includes('/node_modules/react/') || id.includes('/node_modules/react-dom/') || id.includes('/node_modules/scheduler/')) {
                        return 'vendor-react';
                    }

                    if (id.includes('/node_modules/@inertiajs/')) {
                        return 'vendor-inertia';
                    }

                    if (id.includes('/node_modules/axios/')) {
                        return 'vendor-http';
                    }

                    if (id.includes('/node_modules/@dnd-kit/')) {
                        return 'vendor-dnd';
                    }

                    if (id.includes('/node_modules/recharts/')) {
                        return 'vendor-charts';
                    }

                    return undefined;
                },
            },
        },
    },
    test: {
        environment: 'jsdom',
        globals: true,
        setupFiles: './vitest.setup.js',
    },
});
