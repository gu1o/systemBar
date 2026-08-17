import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        // O container sobe o Vite com --host 0.0.0.0, e o laravel-vite-plugin grava
        // esse mesmo 0.0.0.0 no public/hot — endereço que o navegador no Windows não
        // conecta. Fixar o host anunciado faz o hot sair como http://localhost:5174.
        hmr: { host: 'localhost' },
    },
});
