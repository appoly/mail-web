import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
  plugins: [vue()],
  publicDir: false,
  build: {
    outDir: 'public/vendor/mailweb',
    emptyOutDir: true,
    cssCodeSplit: false,
    rollupOptions: {
      input: 'resources/js/app.js',
      output: {
        entryFileNames: 'mailweb.[hash].js',
        chunkFileNames: 'mailweb-chunk.[hash].js',
        assetFileNames: (assetInfo) => {
          if (/\.css$/.test(assetInfo.name)) {
            return 'mailweb.[hash].css';
          }
          return 'mailweb-[hash][extname]';
        }
      }
    },
    manifest: true,
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'resources/js')
    }
  }
});