import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'

export default defineConfig({
  plugins: [vue()],
  test: {
    environment: 'jsdom',
    globals: true,
    setupFiles: ['./tests/setup.js'],
    coverage: {
      provider: 'v8',
      reporter: ['text', 'json', 'html'],
      exclude: [
        'node_modules/',
        'tests/',
        'vendor/',
        'storage/',
        'bootstrap/',
        'config/',
        'database/',
        'public/',
        '**/*.config.js',
        'coverage/**'
      ]
    },
    include: [
      'tests/js/**/*.{test,spec}.{js,mjs,cjs,ts,mts,cts,jsx,tsx}',
      'resources/js/**/*.{test,spec}.{js,mjs,cjs,ts,mts,cts,jsx,tsx}'
    ],
    exclude: [
      'node_modules/',
      'dist/',
      'vendor/',
      'storage/',
      'bootstrap/',
      '.nuxt/',
      '.output/',
      '.vercel/',
      '.next/',
      'coverage/**'
    ]
  },
  resolve: {
    alias: {
      '@': resolve(__dirname, './resources/js'),
      '~': resolve(__dirname, './resources'),
    }
  },
  define: {
    __VUE_OPTIONS_API__: false,
    __VUE_PROD_DEVTOOLS__: false,
  }
})