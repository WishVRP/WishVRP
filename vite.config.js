import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

// Kein Tailwind für die öffentliche Website: eigenes, token-basiertes CSS
// (resources/css). Schriften kommen lokal aus @fontsource – kein Google-/Bunny-CDN.
export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
