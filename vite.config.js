import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/layout.css',
                'resources/css/theme.css',
                'resources/css/sidebar.css',
                'resources/css/empleados.css',
                'resources/css/zonas.css',
                'resources/css/ciclos.css',
                'resources/js/app.js',
                'resources/js/sidebar.js',
                'resources/js/empleados.js',
                'resources/js/zonas.js',
                'resources/js/ciclos.js',
                'resources/js/theme.js'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
