import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import FullReload from 'vite-plugin-full-reload';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: false, // matikan default refresh
        }),
        FullReload([
            'resources/views/**/*.blade.php',
            'app/**/*.php',
            'routes/**/*.php',
        ]),
    ],
});
