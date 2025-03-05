import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
  plugins: [vue()],
  publicDir: false, // Disable copying of a public directory
  build: {
    outDir: 'public/vendor/mailweb', // Output to public/vendor/mailweb
    emptyOutDir: true, // Clear the directory on each build
    cssCodeSplit: false, // Ensure all CSS is in one file
    rollupOptions: {
      input: 'resources/js/app.js',
      output: {
        entryFileNames: 'mailweb.js', // Single compiled JS file
        chunkFileNames: 'mailweb-[hash].js',
        assetFileNames: (assetInfo) => {
          if (/\.css$/.test(assetInfo.name)) {
            return 'mailweb.css'; // Force CSS into a single file named mailweb.css
          }
          return 'mailweb-[ext].[hash][extname]';
        }
      }
    }
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'resources/js')
    }
  }
});