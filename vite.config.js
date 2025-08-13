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
    host: 'localhost',
    port: 5173,
    hmr: {
      host: 'localhost',  // hoặc '127.0.0.1'
      protocol: 'ws',
      port: 5173
    }
  }
});
