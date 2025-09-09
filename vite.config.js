import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';
import { securityPlugin } from './vite-security-plugin.js';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue(),
        tailwindcss(),
        process.env.NODE_ENV !== 'production' ? securityPlugin({
            enableXssCheck: true,
            enableSqlInjectionCheck: true,
            enableUnsafeApiCheck: true,
            enableDependencyCheck: true,
            failOnWarning: false,
            excludePatterns: [/node_modules/, /\.min\.js$/, /vendor/]
        }) : null,
    ].filter(Boolean),
    resolve: {
        alias: {
            '@': '/resources/js',
        }
    },
    server: {
        host: 'localhost',
        hmr: {
            host: 'localhost',
        },
    },
    build: {
        outDir: 'public/build',
        rollupOptions: {
            output: {
                manualChunks: {
                    'vendor': ['vue', 'vue-router', 'pinia', 'axios', '@heroicons/vue/24/outline', '@heroicons/vue/24/solid']
                }
            }
        },
        minify: 'esbuild',
        chunkSizeWarningLimit: 800
    },
    assetsInclude: ['**/*.jpg', '**/*.png', '**/*.gif', '**/*.svg', '**/*.ttf', '**/*.woff', '**/*.woff2'],
    publicDir: 'resources/assets'
});
