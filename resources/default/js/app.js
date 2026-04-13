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
