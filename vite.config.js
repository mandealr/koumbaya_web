import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue(),
        tailwindcss(),
    ],
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
                manualChunks(id) {
                    // Chunk pour les dépendances principales
                    if (id.includes('node_modules')) {
                        if (id.includes('vue') || id.includes('pinia') || id.includes('vue-router')) {
                            return 'vue-core'
                        }
                        if (id.includes('axios') || id.includes('ky')) {
                            return 'http'
                        }
                        if (id.includes('@heroicons') || id.includes('heroicons')) {
                            return 'icons'
                        }
                        if (id.includes('tailwindcss') || id.includes('postcss')) {
                            return 'css-utils'
                        }
                        // Autres dépendances
                        return 'vendor'
                    }
                    
                    // Séparer les pages par section
                    if (id.includes('pages/merchant/')) {
                        return 'merchant-pages'
                    }
                    if (id.includes('pages/customer/')) {
                        return 'customer-pages' 
                    }
                    if (id.includes('pages/admin/')) {
                        return 'admin-pages'
                    }
                    
                    // Composants communs
                    if (id.includes('components/common/')) {
                        return 'common-components'
                    }
                    
                    // Utilitaires
                    if (id.includes('utils/') || id.includes('composables/')) {
                        return 'utils'
                    }
                }
            }
        },
        minify: 'esbuild',
        chunkSizeWarningLimit: 800
    },
    assetsInclude: ['**/*.jpg', '**/*.png', '**/*.gif', '**/*.svg', '**/*.ttf', '**/*.woff', '**/*.woff2'],
    publicDir: 'resources/assets'
});
