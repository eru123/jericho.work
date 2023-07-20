import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { fileURLToPath, URL } from 'node:url'

export default defineConfig({
  plugins: [vue()],
  // server: {
  //   port: 3000,
  //   strictPort: true
  // },
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  publicDir: 'public',
  base: '/cdn',
  build: {
    manifest: true,
    outDir: 'dist',
    emptyOutDir: true,
    assetsDir: 'cdn/__',
    rollupOptions: {
      input: {
        main: 'src/main.js'
      }
    }
  },
})
