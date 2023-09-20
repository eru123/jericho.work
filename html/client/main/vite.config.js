import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";
import md from "unplugin-vue-markdown/vite";
// import { code, meta, link } from "md-powerpack";
import MarkdownItAnchor from "markdown-it-anchor";
import MarkdownItPrism from "markdown-it-prism";
import { fileURLToPath, URL } from "node:url";

export default defineConfig({
  plugins: [
    vue({
      include: [/\.vue$/, /\.md$/],
    }),
    md({
      // see: https://markdown-it.github.io/markdown-it/
      markdownItOptions: {
        html: true,
        linkify: true,
        typographer: true,
      },
      markdownItSetup(md) {
        md.use(MarkdownItAnchor);
        md.use(MarkdownItPrism);
      },
      wrapperClasses: "markdown-body",
    }),
  ],
  optimizeDeps: {
    exclude: ['oh-vue-icons/icons'],
  },
  resolve: {
    alias: {
      "@": fileURLToPath(new URL("./src", import.meta.url)),
    },
    preserveSymlinks: false,
  },
  publicDir: "public",
  base: "",
  build: {
    manifest: true,
    outDir: "dist",
    emptyOutDir: true,
    assetsDir: "__",
    rollupOptions: {
      input: {
        main: "src/main.js",
      },
    },
    assetInlineLimit: 0,
  },
  server: {
    port: 3000,
    strictPort: true,
    host: true,
  }
});
