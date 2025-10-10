import { defineConfig, loadEnv } from 'vite'
import tailwindcss from '@tailwindcss/vite'
import laravel from 'laravel-vite-plugin'
import { wordpressPlugin, wordpressThemeJson } from '@roots/vite-plugin'

export default ({ mode }) => {
  // Load env variables
  const env = loadEnv(mode, process.cwd(), '')

  return defineConfig({
    // ✅ This is the correct base path for WordPress themes on Hostinger/live servers
    base: '/wp-content/themes/heygirlsbkk/public/',

    server: {
      host: 'localhost',
      port: 3000,
      strictPort: true,
      hmr: {
        host: 'heygirlsbkk.local',
      },
    },

    build: {
      // ✅ Output compiled files into public/build
      outDir: 'public/build',
      emptyOutDir: true,
      manifest: true,
      rollupOptions: {
        input: [
          'resources/css/app.css',
          'resources/js/app.js',
          'resources/css/editor.css',
          'resources/js/editor.js',
          'resources/js/image-optimization.js',
        ],
      },
    },

    plugins: [
      // ✅ TailwindCSS (native Vite integration)
      tailwindcss(),

      // ✅ Laravel Vite plugin (for automatic manifest + refresh)
      laravel({
        input: [
          'resources/css/app.css',
          'resources/js/app.js',
          'resources/css/editor.css',
          'resources/js/editor.js',
          'resources/js/image-optimization.js',
        ],
        refresh: true,
      }),

      // ✅ WordPress integration for Sage
      wordpressPlugin(),

      // ✅ Automatically generate theme.json (keeps Gutenberg + Tailwind synced)
      wordpressThemeJson({
        disableTailwindColors: false,
        disableTailwindFonts: false,
        disableTailwindFontSizes: false,
      }),
    ],

    resolve: {
      alias: {
        '@scripts': '/resources/js',
        '@styles': '/resources/css',
        '@fonts': '/resources/fonts',
        '@images': '/resources/images',
      },
    },
  })
}
