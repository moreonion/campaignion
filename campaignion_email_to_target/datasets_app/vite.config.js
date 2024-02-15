/// <reference types="vitest" />
import copy from 'rollup-plugin-copy'
import { defineConfig } from 'vite'
import { fileURLToPath, URL } from 'node:url'
import { resolve } from 'path'
import vue2 from '@vitejs/plugin-vue2'
import vitePluginImp from 'vite-plugin-imp'

// https://vitejs.dev/config/
export default defineConfig({
  build: {
    assetsInlineLimit: 32000,
    rollupOptions: {
      output: {
        manualChunks: {
          main: ['src/main.js'],
          drupalFixture: ['drupal-fixture.js']
        }
      },
      plugins: [
        copy({
          targets: [
            { src: 'dist/assets/main.*.js', dest: '../js/datasets_app', rename: 'datasets_app.vue.min.js' },
            { src: 'dist/assets/main.*.css', dest: '../css/datasets_app', rename: 'datasets_app.css' }
          ]
        })
      ]
    }
  },
  define: {
    'process.env.E2T_API_URL': process.env.E2T_API_TOKEN ? '"https://e2t-api.more-onion.com/v2"' : '"http://localhost:8081/api"',
    'process.env.E2T_API_TOKEN': process.env.E2T_API_TOKEN ? '"' + process.env.E2T_API_TOKEN + '"' : '"xxx"',
    'process.env.NODE_ENV': JSON.stringify(process.env.NODE_ENV)
  },
  plugins: [
    vue2({
      template: {
        compilerOptions: {
          whitespace: 'preserve'
        }
      }
    }),
    vitePluginImp()
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    }
  },
  server: {
    port: 8080
  },
  test: {
    setupFiles: fileURLToPath(new URL('./test/unit/setup.js', import.meta.url))
  }
})
