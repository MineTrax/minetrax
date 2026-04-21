<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ThemeCreateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'theme:create {name : The name of the theme (lowercase, hyphens and underscores allowed)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scaffold a new theme with minimal override structure that inherits from the default theme.';

    public function handle(): int
    {
        $name = $this->argument('name');

        if (! preg_match('/^[a-z0-9][a-z0-9\-_]*$/', $name)) {
            $this->error('Theme name must be lowercase alphanumeric with hyphens or underscores, and cannot start with a hyphen or underscore.');

            return self::FAILURE;
        }

        if ($name === 'default') {
            $this->error('Cannot use "default" as a custom theme name — it is reserved for the base theme.');

            return self::FAILURE;
        }

        $themeDir = resource_path($name);

        if (File::isDirectory($themeDir)) {
            $this->error("Theme directory already exists: resources/{$name}/");

            return self::FAILURE;
        }

        $this->info("Creating theme: {$name}");

        // Create directory structure
        $directories = [
            "{$themeDir}/js/Pages",
            "{$themeDir}/js/Components",
            "{$themeDir}/js/Shared",
            "{$themeDir}/js/Layouts",
            "{$themeDir}/css",
            "{$themeDir}/views",
            "{$themeDir}/markdown",
        ];

        foreach ($directories as $dir) {
            File::makeDirectory($dir, 0755, true);
            File::put("{$dir}/.gitkeep", '');
        }

        // Scaffold app.js
        File::put("{$themeDir}/js/app.js", $this->getAppJsContent());

        // Scaffold ssr.js
        File::put("{$themeDir}/js/ssr.js", $this->getSsrJsContent());

        // Scaffold bootstrap.js (re-export default's)
        File::put("{$themeDir}/js/bootstrap.js", $this->getBootstrapJsContent());

        // Scaffold css/app.css
        File::put("{$themeDir}/css/app.css", $this->getAppCssContent($name));

        // Remove .gitkeep from directories that now have files
        @unlink("{$themeDir}/js/.gitkeep");
        @unlink("{$themeDir}/css/.gitkeep");

        $this->newLine();
        $this->info('Theme scaffolded successfully!');
        $this->newLine();
        $this->line('  Next steps:');
        $this->line("  1. Set <comment>APP_THEME={$name}</comment> in your <comment>.env</comment> file");
        $this->line('  2. Run <comment>npm run dev</comment> to start the dev server');
        $this->line("  3. Add page/component overrides in <comment>resources/{$name}/js/</comment>");
        $this->line("  4. Add Blade view overrides in <comment>resources/{$name}/views/</comment>");
        $this->line("  5. Customize styles in <comment>resources/{$name}/css/app.css</comment>");
        $this->newLine();
        $this->line('  Files you create will override the default theme.');
        $this->line('  Everything else falls back to <comment>resources/default/</comment> automatically.');
        $this->newLine();
        $this->line('  For more details, visit: <href=https://minetrax.github.io/docs/development/develop-custom-themes>https://minetrax.github.io/docs/development/develop-custom-themes</>');

        return self::SUCCESS;
    }

    protected function getAppJsContent(): string
    {
        return <<<'JS'
import "./bootstrap";
import "../css/app.css";

import { createApp, h } from "vue";
import {
    plugin as formKitPlugin,
    defaultConfig as formKitDefaultConfig,
} from "@formkit/vue";
import formKitConfig from "/formkit.config.js";
import { createInertiaApp, Head, Link } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { ZiggyVue } from "ziggy-js";
import themePages from "virtual:theme-pages";

import VueTippy from "vue-tippy";
import translations from "@/Mixins/translations.js";
import confirmDirective from "@/Directives/confirm.js";
import Swal from "sweetalert2";
import Particles from "@tsparticles/vue3";
import AppHead from "@/Components/AppHead.vue";
import { loadFull } from "tsparticles";

createInertiaApp({
    title: (title) => `${title}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            themePages
        ),
    setup({ el, App, props, plugin }) {
        const VueApp = createApp({ render: () => h(App, props) })
            .use(plugin)

            .use(ZiggyVue)
            .use(formKitPlugin, formKitDefaultConfig(formKitConfig));

        VueApp.use(VueTippy, {
            defaultProps: {
                arrow: true,
                animation: "perspective",
            },
        });

        VueApp.component("InertiaHead", Head);
        VueApp.component("InertiaLink", Link);
        VueApp.component("AppHead", AppHead);

        VueApp.mixin(translations);

        VueApp.directive("confirm", confirmDirective);

        // remove the global loader with id site-global-loader
        document.getElementById("site-global-loader").remove();

        VueApp.use(Particles, {
            init: async (engine) => {
                await loadFull(engine);
            },
        });

        return VueApp.mount(el);
    },
    progress: {
        color: "var(--primary)",
    },
});

window.Toast = Swal.mixin({
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    showCloseButton: true,
    didOpen: (toast) => {
        toast.addEventListener("mouseenter", Swal.stopTimer);
        toast.addEventListener("mouseleave", Swal.resumeTimer);
    },
});
JS;
    }

    protected function getSsrJsContent(): string
    {
        return <<<'JS'
import { createSSRApp, h } from "vue";
import { renderToString } from "@vue/server-renderer";
import { createInertiaApp } from "@inertiajs/vue3";
import createServer from "@inertiajs/vue3/server";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { ZiggyVue } from "ziggy-js";
import themePages from "virtual:theme-pages";

const appName = "Laravel";

createServer((page) =>
    createInertiaApp({
        page,
        render: renderToString,
        title: (title) => `${title} - ${appName}`,
        resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, themePages),
        setup({ App, props, plugin }) {
            return createSSRApp({ render: () => h(App, props) })
                .use(plugin)
                .use(ZiggyVue, {
                    ...page.props.ziggy,
                    location: new URL(page.props.ziggy.location),
                });
        },
    })
);
JS;
    }

    protected function getBootstrapJsContent(): string
    {
        return <<<'JS'
// Re-export the default theme's bootstrap configuration.
// Override this file if you need to customize Axios, Echo, or other bootstrap settings.
export * from "../../default/js/bootstrap.js";
import "../../default/js/bootstrap.js";
JS;
    }

    protected function getAppCssContent(string $themeName): string
    {
        return <<<CSS
/* Theme: {$themeName}
 * This imports all styles from the default theme.
 * Add your overrides below the import, or replace the import entirely
 * for a fully custom stylesheet.
 */
@import '../../default/css/app.css';

/* Your theme overrides go here */
CSS;
    }
}
