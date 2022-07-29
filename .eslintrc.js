// eslint-disable-next-line no-undef
module.exports = {
    'env': {
        'browser': true,
        'es2021': true,
        'node': true
    },
    'extends': [
        'eslint:recommended',
        'plugin:vue/vue3-recommended'
    ],
    'parserOptions': {
        'ecmaVersion': 13,
        'sourceType': 'module'
    },
    'plugins': [
        'vue'
    ],
    'rules': {
        'indent': [
            'error',
            4
        ],
        'linebreak-style': 0,
        'quotes': [
            'error',
            'single'
        ],
        'semi': [
            'error',
            'always'
        ],
        'vue/multi-word-component-names': 'off',
        'vue/require-default-prop': 'off'
    },
    globals: {
        'Echo': true,
        'Pusher': true,
        'axios': true,
        '_': true,
        'route': true,
        '_translations': true,
        'Toast': true,
    }
};
