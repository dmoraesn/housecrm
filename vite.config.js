import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
  plugins: [
    laravel({
      input: [
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/css/kanban.css',
        'resources/js/kanban.js',
        'vendor/orchid/platform/resources/js/app.js',
      ],
      refresh: true,
    }),
    tailwindcss(),
  ],
  build: {
    outDir: 'public/build',
    emptyOutDir: true,
    manifest: true,
  },
  resolve: {
    alias: {
      '@': '/resources/js',
      'vendor': '/vendor',
    },
  },
});