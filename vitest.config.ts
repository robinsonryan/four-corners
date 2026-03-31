import { defineConfig } from 'vitest/config';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
  plugins: [vue()],
  test: {
    globals: true,
    environment: 'happy-dom',
    include: ['resources/js/**/*.{test,spec}.{js,ts}'],
    coverage: {
      provider: 'v8',
      reporter: ['text', 'json', 'html'],
      include: ['resources/js/**/*.{ts,vue}'],
      exclude: ['resources/js/**/*.{test,spec}.ts', 'resources/js/Types/**'],
    },
  },
  resolve: {
    alias: {
      '@': '/resources/js',
    },
  },
});
