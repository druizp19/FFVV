import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/layout.css',
                'resources/css/sidebar.css',
                'resources/css/modals.css',
                'resources/css/empleados.css',
                'resources/css/zonas.css',
                'resources/css/ciclos.css',
                'resources/css/productos.css',
                'resources/css/historial.css',
                'resources/css/dashboard.css',
                'resources/js/app.js',
                'resources/js/sidebar.js',
                'resources/js/empleados.js',
                'resources/js/zonas.js',
                'resources/js/ciclos.js',
                'resources/js/productos.js',
                'resources/js/historial.js',
                'resources/js/dashboard.js'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
