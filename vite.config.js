// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        // Plugin do Laravel: faz o Vite conversar com o Blade e recarregar no browser
        laravel({
            input: [
                // CSS e JS principais
                'resources/css/app.css',
                'resources/js/app.js',

                // CSS e JS específicos do Kanban (opcional, se quiser compilar separados)
                'resources/css/kanban.css',
                'resources/js/kanban.js',
            ],
            refresh: true, // recarrega automaticamente quando arquivos Blade, JS ou CSS mudam
        }),

        // Plugin oficial do Tailwind para Vite (carrega o JIT e purge automaticamente)
        tailwindcss(),
    ],

    // ⚙️ Configurações adicionais (opcional)
    build: {
        outDir: 'public/build', // destino padrão de saída
        emptyOutDir: true,      // limpa build antiga a cada nova
        manifest: true,         // necessário para @vite funcionar no Blade
    },
});
