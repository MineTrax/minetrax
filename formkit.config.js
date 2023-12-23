import { fr, en } from '@formkit/i18n';
import { defaultConfig } from '@formkit/vue';
import { rootClasses } from './formkit.theme';
import { genesisIcons } from '@formkit/icons';

export default defaultConfig({
    locales: { fr, en },
    locale: document.querySelector('html').getAttribute('lang'),
    config: {
        rootClasses,
    },
    icons: {
        ...genesisIcons
    }
});
