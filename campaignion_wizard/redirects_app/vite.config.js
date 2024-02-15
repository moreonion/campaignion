/// <reference types="vitest" />
import copy from 'rollup-plugin-copy'
import { defineConfig } from 'vite'
import { fileURLToPath, URL } from 'node:url'
import vue2 from '@vitejs/plugin-vue2'
import vitePluginImp from 'vite-plugin-imp'

const vueDocsPlugin = {
  name: 'vue-docs',
  transform (code, id) {
    if (!/vue&type=docs/.test(id)) return
    return 'export default \'\''
  }
}

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
            { src: 'dist/assets/main.*.js', dest: '../js/redirects_app', rename: 'redirects_app.vue.min.js' },
            { src: 'dist/assets/main.*.css', dest: '../css/redirects_app', rename: 'redirects_app.css' }
          ]
        })
      ]
    }
  },
  define: {
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
    vueDocsPlugin,
    vitePluginImp()
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
      vue: 'vue/dist/vue.esm.js' // With runtime compiler included
    }
  },
  server: {
    port: 8080,
    proxy: {
      '/api': {
        target: 'http://localhost:8081',
        changeOrigin: true,
        rewrite: (path) => path.replace(/^\/api/, '')
      }
    }
  },
  test: {
    globals: true,
    setupFiles: fileURLToPath(new URL('./test/unit/setup.js', import.meta.url))
  }
})
