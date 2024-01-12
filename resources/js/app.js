import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { plugin as formKitPlugin, defaultConfig as formKitDefaultConfig } from '@formkit/vue';
import formKitConfig from '/formkit.config.js';
import { createInertiaApp, Head, Link } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';

import VueTippy from 'vue-tippy';
import translations from '@/Mixins/translations.js';
import confirmDirective from './Directives/confirm.js';
import Swal from 'sweetalert2';
import Particles from 'vue3-particles';
import AppHead from '@/Components/AppHead.vue';

createInertiaApp({
    title: (title) => `${title}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue')    // Note: use import.meta.globEager to disable chunking behavior
        ),
    setup({ el, App, props, plugin }) {
        const VueApp = createApp({ render: () => h(App, props) })
            .use(plugin)
            // eslint-disable-next-line no-undef
            .use(ZiggyVue, Ziggy)
            .use(formKitPlugin, formKitDefaultConfig(formKitConfig));

        VueApp.use(VueTippy, {
            defaultProps: {
                arrow: true,
                animation: 'perspective',
            },
        });

        VueApp.component('InertiaHead', Head);
        VueApp.component('InertiaLink', Link);
        VueApp.component('AppHead', AppHead);

        // eslint-disable-next-line no-undef
        VueApp.mixin(translations);

        VueApp.directive('confirm', confirmDirective);

        // remove the global loader with id site-global-loader
        document.getElementById('site-global-loader').remove();

        VueApp.use(Particles);

        return VueApp.mount(el);
    },
    progress: {
        color: '#29d',
    },
});

window.Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    showCloseButton: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
    },
});
