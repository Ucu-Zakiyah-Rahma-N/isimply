import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js',
                'resources/js/invoice/invoice_script.js',
                'resources/js/operasional/operasional.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
