import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');

    const outDir = 'public/build/' + (env.APP_THEME || 'default');
    const buildDirectory = 'build/' + (env.APP_THEME || 'default');
    return {
        plugins: [
            laravel({
                input: 'resources/js/app.js',
                buildDirectory: buildDirectory,
                ssr: 'resources/js/ssr.js',
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
        }
    };
});
