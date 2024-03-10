import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');

    const theme = env.APP_THEME || 'default';
    const outDir = 'public/build/' + theme;
    const buildDirectory = 'build/' + theme;
    return {
        plugins: [
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
                '@': '/resources/' + theme + '/js',
            },
        },
    };
});
