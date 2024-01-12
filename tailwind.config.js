const defaultTheme = require('tailwindcss/defaultTheme');
const colors = require('tailwindcss/colors');

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './formkit.theme.mjs'
    ],
    darkMode: 'class',
    theme: {
        extend: {
            keyframes: {
                'scale': {
                    '0%': {
                        transform: 'scale(1,1)',
                    },
                    '50%': {
                        transform: 'scale(1.05,1.05)',
                    },
                    '100%': {
                        transform: 'scale(1,1)',
                    },
                },
            },
            fontFamily: {
                sans: ['Nunito', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'light-blue': colors.sky,
                'cool-gray': colors.gray,
                'orange': colors.orange,
                'lime': colors.lime,
                'teal': colors.teal,
                'amber': colors.amber,
                'rose': colors.rose,
                'theme-gray': {
                    100: '#dee8ea',
                    200: '#c4cfd1'
                }
            },
            maxHeight: {
                0: '0',
                '1/4': '25%',
                '1/2': '50%',
                '3/4': '75%',
            },

            typography: (theme) => ({
                dark: {
                    css: {
                        color: theme('colors.gray.300'),
                        a: {
                            color: theme('colors.light-blue.400'),
                            '&:hover': {
                                color: theme('colors.light-blue.200'),
                            },
                        },

                        h1: {
                            color: theme('colors.gray.300'),
                        },
                        h2: {
                            color: theme('colors.gray.300'),
                        },
                        h3: {
                            color: theme('colors.gray.300'),
                        },
                        h4: {
                            color: theme('colors.gray.300'),
                        },
                        h5: {
                            color: theme('colors.gray.300'),
                        },
                        h6: {
                            color: theme('colors.gray.300'),
                        },

                        strong: {
                            color: theme('colors.gray.300'),
                        },

                        code: {
                            color: theme('colors.gray.300'),
                        },

                        figcaption: {
                            color: theme('colors.gray.500'),
                        },
                    },
                },
            }),
        },
    },

    variants: {
        extend: {
            opacity: ['disabled'],
            typography: ['dark'],
        },
    },

    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
    ],
};
