import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig(({ mode }) => {
    const { VITE_DEV_SERVER_HOST } = loadEnv(mode, process.cwd(), 'VITE_');

    return {
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

            ...(VITE_DEV_SERVER_HOST
                ? {
                    origin: `http://${VITE_DEV_SERVER_HOST}:5173`,
                    hmr: {
                        host: VITE_DEV_SERVER_HOST,
                    },
                }
                : {}),
        },
    };
});
