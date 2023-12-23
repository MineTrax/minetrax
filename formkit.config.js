import { fr, en } from '@formkit/i18n';
import { defaultConfig } from '@formkit/vue';
import { rootClasses } from './formkit.theme';

export default defaultConfig({
    locales: { fr, en },
    locale: document.querySelector('html').getAttribute('lang'),
    config: {
        rootClasses,
    },
});
