import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import md from 'vite-plugin-vue-markdown'
// import { code, meta, link } from "md-powerpack";
import MarkdownItAnchor from 'markdown-it-anchor'
import MarkdownItPrism from 'markdown-it-prism'
import { fileURLToPath, URL } from 'node:url'

export default defineConfig({
  plugins: [
    vue({
      include: [/\.vue$/, /\.md$/],
    }),
    md({
      // default options passed to markdown-it
      // see: https://markdown-it.github.io/markdown-it/
      markdownItOptions: {
        html: true,
        linkify: true,
        typographer: true,
      },
      // A function providing the Markdown It instance gets the ability to apply custom settings/plugins
      markdownItSetup(md) {
        // for example
        md.use(MarkdownItAnchor)
        md.use(MarkdownItPrism)
      },
      // Class names for the wrapper div
      wrapperClasses: 'markdown-body'
    }),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  publicDir: 'public',
  base: '',
  build: {
    manifest: true,
    outDir: 'dist',
    emptyOutDir: true,
    assetsDir: '__',
    rollupOptions: {
      input: {
        main: 'src/main.js'
      }
    }
  },
  server: {
    port: 3000,
    strictPort: true,
    host: true
  }
})
