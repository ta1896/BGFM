import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import { ViteImageOptimizer } from 'vite-plugin-image-optimizer';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
        ViteImageOptimizer({
            png: { quality: 80 },
            jpeg: { quality: 75 },
            webp: { lossy: true, quality: 75 },
            avif: { lossy: true, quality: 70 },
        }),
    ],
});
