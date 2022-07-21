import './bootstrap';
// Import modules...
import Vue from 'vue';
import {App as InertiaApp, Head, Link, plugin as InertiaPlugin} from '@inertiajs/inertia-vue';
import PortalVue from 'portal-vue';
import {InertiaProgress} from '@inertiajs/progress';
import VueTippy, {TippyComponent} from 'vue-tippy';
import authorizable from '@/Mixins/authorizable';
import helpers from '@/Mixins/helpers';
import translations from '@/Mixins/translations';
import Fragment from 'vue-fragment';
import VuejsDialog from 'vuejs-dialog';
import AppHead from '@/Components/AppHead';

Vue.use(VuejsDialog, {
    html: true,
    loader: false,
    okText: 'Proceed',
    cancelText: 'Cancel',
    animation: 'zoom',
    backdropClose: true,
});
Vue.use(Fragment.Plugin);
Vue.use(VueTippy, {
    arrow: true
});
Vue.component('InertiaHead', Head);
Vue.component('InertiaLink', Link);
Vue.component('AppHead', AppHead);
Vue.component('Tippy', TippyComponent);

// eslint-disable-next-line no-undef
Vue.mixin({ methods: { route } });
Vue.mixin(authorizable);
Vue.mixin(helpers);
Vue.mixin(translations);
Vue.use(InertiaPlugin);
Vue.use(PortalVue);

InertiaProgress.init({
    showSpinner: true,
    color: '#29d',
});
// eslint-disable-next-line no-undef
Vue.prototype.$route = route;

const app = document.getElementById('app');

new Vue({
    mounted() {
        window.addEventListener('popstate', () => {
            this.$page.props.popstate = true;
        });
    },
    render: (h) =>
        h(InertiaApp, {
            props: {
                initialPage: JSON.parse(app.dataset.page),
                resolveComponent: (name) => require(`./Pages/${name}`).default,
            },
        }),
}).$mount(app);
