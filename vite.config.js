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
                'resources/css/clone-modal.css',
                'resources/css/ciclos.css',
                'resources/css/productos.css',
                'resources/css/historial.css',
                'resources/css/dashboard.css',
                'resources/css/bricks.css',
                'resources/js/app.js',
                'resources/js/sidebar.js',
                'resources/js/empleados.js',
                'resources/js/zonas.js',
                'resources/js/ciclos.js',
                'resources/js/productos.js',
                'resources/js/historial.js',
                'resources/js/dashboard.js',
                'resources/js/bricks-reasignacion.js',
                'resources/js/geosegmentos.js',
                'resources/css/bricks-reasignacion.css',
                'resources/css/bricks-alerts.css'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    build: {
        // Deshabilitar source maps en producción
        sourcemap: false,
        // Minificar código
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true, // Eliminar console.log en producción
                drop_debugger: true, // Eliminar debugger en producción
            },
        },
        // Optimizar chunks
        rollupOptions: {
            output: {
                manualChunks: undefined,
            },
        },
    },
});
