import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';
import themeInheritance from './vite-plugin-theme-fallback.js';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');

    const theme = env.APP_THEME || 'default';
    const outDir = 'public/build/' + theme;
    const buildDirectory = 'build/' + theme;
    return {
        plugins: [
            themeInheritance({ theme, root: process.cwd() }),
            tailwindcss(),
            laravel({
                input:  `/resources/${theme}/js/app.js`,
                buildDirectory: buildDirectory,
                ssr: `/resources/${theme}/js/ssr.js`,
                refresh: true,
            }),
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                    compilerOptions: {
                        isCustomElement: (tag) => ['marquee'].includes(tag),
                    }
                },
            }),
        ],
        build: {
            chunkSizeWarningLimit: 2000,
            outDir: outDir,
        },
        resolve: {
            alias: {
                // Always point @ to default — the theme-inheritance plugin (enforce: 'pre')
                // resolves @/ imports to the active theme first, falling back to default.
                // This alias serves as a safety net for Vite's dep-scan and other passes
                // that may not invoke plugin resolveId hooks.
                '@': '/resources/default/js',
                'ziggy-js': '/vendor/tightenco/ziggy',
            },
        },
    };
});
