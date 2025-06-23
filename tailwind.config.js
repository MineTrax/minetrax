const { fontFamily } = require("tailwindcss/defaultTheme");
const colors = require("tailwindcss/colors");

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./vendor/laravel/jetstream/**/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/**/views/**/*.blade.php",
        "./resources/**/js/**/*.vue",
        "./formkit.theme.mjs",
    ],
    darkMode: "class",
    theme: {
        extend: {
            keyframes: {
                scale: {
                    "0%": {
                        transform: "scale(1,1)",
                    },
                    "50%": {
                        transform: "scale(1.05,1.05)",
                    },
                    "100%": {
                        transform: "scale(1,1)",
                    },
                },
            },
            fontFamily: {
                sans: ["var(--font-sans)", ...fontFamily.sans],
                serif: ["var(--font-serif)", ...fontFamily.serif],
                mono: ["var(--font-mono)", ...fontFamily.mono],
            },
            colors: {
                // OLD COLORS. For backward compatibility only. Don't use these.
                // primary: "var(--primary)",
                // secondary: colors.gray,
                success: colors.green,
                warning: colors.yellow,
                error: colors.red,
                info: colors.cyan,
                purple: colors.purple,
                surface: colors.gray,
                // Old colors ends.

                // NEW TAILWIND THEMING
                border: "hsl(var(--border))",
                input: "hsl(var(--input))",
                ring: "hsl(var(--ring))",
                background: "hsl(var(--background))",
                foreground: "hsl(var(--foreground))",
                primary: {
                    DEFAULT: "hsl(var(--primary))",
                    foreground: "hsl(var(--primary-foreground))",
                },
                secondary: {
                    DEFAULT: "hsl(var(--secondary))",
                    foreground: "hsl(var(--secondary-foreground))",
                },
                destructive: {
                    DEFAULT: "hsl(var(--destructive))",
                    foreground: "hsl(var(--destructive-foreground))",
                },
                muted: {
                    DEFAULT: "hsl(var(--muted))",
                    foreground: "hsl(var(--muted-foreground))",
                },
                accent: {
                    DEFAULT: "hsl(var(--accent))",
                    foreground: "hsl(var(--accent-foreground))",
                },
                popover: {
                    DEFAULT: "hsl(var(--popover))",
                    foreground: "hsl(var(--popover-foreground))",
                },
                card: {
                    DEFAULT: "hsl(var(--card))",
                    foreground: "hsl(var(--card-foreground))",
                },
                chart: {
                    1: "hsl(var(--chart-1))",
                    2: "hsl(var(--chart-2))",
                    3: "hsl(var(--chart-3))",
                    4: "hsl(var(--chart-4))",
                    5: "hsl(var(--chart-5))",
                },
            },
            borderRadius: {
                lg: "var(--radius)",
                md: "calc(var(--radius) - 2px)",
                sm: "calc(var(--radius) - 4px)",
            },
            borderColor: {
                DEFAULT: "hsl(var(--border))",
            },
            ringColor: { DEFAULT: "hsl(var(--ring))" },
            boxShadow: {
                "2xs": "var(--shadow-2xs)",
                xs: "var(--shadow-xs)",
                sm: "var(--shadow-sm)",
                DEFAULT: "var(--shadow)",
                md: "var(--shadow-md)",
                lg: "var(--shadow-lg)",
                xl: "var(--shadow-xl)",
                "2xl": "var(--shadow-2xl)",
            },
            maxHeight: {
                0: "0",
                "1/4": "25%",
                "1/2": "50%",
                "3/4": "75%",
            },

            typography: (theme) => ({
                dark: {
                    css: {
                        color: theme("colors.foreground"),
                        a: {
                            color: theme("colors.primary.DEFAULT"),
                            "&:hover": {
                                color: theme("colors.primary.DEFAULT"),
                            },
                        },

                        h1: {
                            color: theme("colors.foreground"),
                        },
                        h2: {
                            color: theme("colors.foreground"),
                        },
                        h3: {
                            color: theme("colors.foreground"),
                        },
                        h4: {
                            color: theme("colors.foreground"),
                        },
                        h5: {
                            color: theme("colors.foreground"),
                        },
                        h6: {
                            color: theme("colors.foreground"),
                        },

                        strong: {
                            color: theme("colors.foreground"),
                        },

                        code: {
                            color: theme("colors.foreground"),
                        },

                        figcaption: {
                            color: theme("colors.foreground"),
                        },
                    },
                },
            }),
        },
    },

    variants: {
        extend: {
            opacity: ["disabled"],
            typography: ["dark"],
        },
    },

    plugins: [
        // require("@tailwindcss/forms"), // Removed, coz adding blue border to input fields.
        require("@tailwindcss/typography"),
        // Plugin to expose semantic colors as CSS custom properties
        function ({ addBase, theme }) {
            addBase({
                ":root": {
                    // Primary colors
                    "--color-primary-50": theme("colors.primary.50"),
                    "--color-primary-100": theme("colors.primary.100"),
                    "--color-primary-200": theme("colors.primary.200"),
                    "--color-primary-300": theme("colors.primary.300"),
                    "--color-primary-400": theme("colors.primary.400"),
                    "--color-primary-500": theme("colors.primary.500"),
                    "--color-primary-600": theme("colors.primary.600"),
                    "--color-primary-700": theme("colors.primary.700"),
                    "--color-primary-800": theme("colors.primary.800"),
                    "--color-primary-900": theme("colors.primary.900"),

                    // Secondary colors
                    "--color-secondary-50": theme("colors.secondary.50"),
                    "--color-secondary-100": theme("colors.secondary.100"),
                    "--color-secondary-200": theme("colors.secondary.200"),
                    "--color-secondary-300": theme("colors.secondary.300"),
                    "--color-secondary-400": theme("colors.secondary.400"),
                    "--color-secondary-500": theme("colors.secondary.500"),
                    "--color-secondary-600": theme("colors.secondary.600"),
                    "--color-secondary-700": theme("colors.secondary.700"),
                    "--color-secondary-800": theme("colors.secondary.800"),
                    "--color-secondary-900": theme("colors.secondary.900"),

                    // Surface colors
                    "--color-surface-50": theme("colors.surface.50"),
                    "--color-surface-100": theme("colors.surface.100"),
                    "--color-surface-200": theme("colors.surface.200"),
                    "--color-surface-300": theme("colors.surface.300"),
                    "--color-surface-400": theme("colors.surface.400"),
                    "--color-surface-500": theme("colors.surface.500"),
                    "--color-surface-600": theme("colors.surface.600"),
                    "--color-surface-700": theme("colors.surface.700"),
                    "--color-surface-800": theme("colors.surface.800"),
                    "--color-surface-900": theme("colors.surface.900"),

                    // Status colors
                    "--color-success-50": theme("colors.success.50"),
                    "--color-success-100": theme("colors.success.100"),
                    "--color-success-200": theme("colors.success.200"),
                    "--color-success-300": theme("colors.success.300"),
                    "--color-success-400": theme("colors.success.400"),
                    "--color-success-500": theme("colors.success.500"),
                    "--color-success-600": theme("colors.success.600"),
                    "--color-success-700": theme("colors.success.700"),
                    "--color-success-800": theme("colors.success.800"),
                    "--color-success-900": theme("colors.success.900"),

                    "--color-warning-50": theme("colors.warning.50"),
                    "--color-warning-100": theme("colors.warning.100"),
                    "--color-warning-200": theme("colors.warning.200"),
                    "--color-warning-300": theme("colors.warning.300"),
                    "--color-warning-400": theme("colors.warning.400"),
                    "--color-warning-500": theme("colors.warning.500"),
                    "--color-warning-600": theme("colors.warning.600"),
                    "--color-warning-700": theme("colors.warning.700"),
                    "--color-warning-800": theme("colors.warning.800"),
                    "--color-warning-900": theme("colors.warning.900"),

                    "--color-error-50": theme("colors.error.50"),
                    "--color-error-100": theme("colors.error.100"),
                    "--color-error-200": theme("colors.error.200"),
                    "--color-error-300": theme("colors.error.300"),
                    "--color-error-400": theme("colors.error.400"),
                    "--color-error-500": theme("colors.error.500"),
                    "--color-error-600": theme("colors.error.600"),
                    "--color-error-700": theme("colors.error.700"),
                    "--color-error-800": theme("colors.error.800"),
                    "--color-error-900": theme("colors.error.900"),

                    "--color-info-50": theme("colors.info.50"),
                    "--color-info-100": theme("colors.info.100"),
                    "--color-info-200": theme("colors.info.200"),
                    "--color-info-300": theme("colors.info.300"),
                    "--color-info-400": theme("colors.info.400"),
                    "--color-info-500": theme("colors.info.500"),
                    "--color-info-600": theme("colors.info.600"),
                    "--color-info-700": theme("colors.info.700"),
                    "--color-info-800": theme("colors.info.800"),
                    "--color-info-900": theme("colors.info.900"),
                },
            });
        },
        require("tailwindcss-animate"),
    ],
};
