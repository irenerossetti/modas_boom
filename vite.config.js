import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    build: {
        // Ensure the final build outputs to `public/build` (no nested `build/build`)
        outDir: 'public/build',
        // Use empty assetsDir to avoid nested `build/` inside `public/build`.
        assetsDir: '' ,
        emptyOutDir: true,
    }
});