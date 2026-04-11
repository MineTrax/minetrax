import js from "@eslint/js";
import pluginVue from "eslint-plugin-vue";
import globals from "globals";

export default [
    js.configs.recommended,
    ...pluginVue.configs["flat/recommended"],
    {
        languageOptions: {
            ecmaVersion: 2022,
            sourceType: "module",
            globals: {
                ...globals.browser,
                ...globals.node,
                Echo: "readonly",
                Pusher: "readonly",
                axios: "readonly",
                _: "readonly",
                route: "readonly",
                _translations: "readonly",
                Toast: "readonly",
            },
        },
        rules: {
            indent: ["error", 4],
            "linebreak-style": 0,
            quotes: ["error", "double"],
            semi: ["error", "always"],
            "vue/multi-word-component-names": "off",
            "vue/require-default-prop": "off",
            "vue/no-reserved-component-names": "off",
        },
    },
];
