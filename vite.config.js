import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/sync-status.js',
            ],
            refresh: [
                'resources/views/**/*.blade.php',
                'routes/**/*.php',
            ],
        }),
    ],

    server: {
        host: '0.0.0.0',
        port: 5173,

        hmr: {
            // host: '192.168.100.18',
            host: '10.0.1.39',
        },
    },
});