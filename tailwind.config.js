const defaultTheme = require("tailwindcss/defaultTheme");
const colors = require("tailwindcss/colors");

// Define theme color palettes
const themeColorPalettes = {
    "light-blue": {
        primary: colors.sky,
        secondary: colors.gray,
        surface: colors.gray,
    },
    purple: {
        primary: colors.violet,
        secondary: colors.slate,
        surface: colors.slate,
    },
    green: {
        primary: colors.emerald,
        secondary: colors.gray,
        surface: colors.stone,
    },
    orange: {
        primary: colors.orange,
        secondary: colors.neutral,
        surface: colors.neutral,
    },
};

// Define font families
const themeFonts = {
    Nunito: ["Nunito", ...defaultTheme.fontFamily.sans],
    Ubuntu: ["Ubuntu", ...defaultTheme.fontFamily.sans],
    Inter: ["Inter", ...defaultTheme.fontFamily.sans],
    Poppins: ["Poppins", ...defaultTheme.fontFamily.sans],
};

// Helper function to generate color variables
function generateColorVariables(colorPalette, name) {
    const variables = {};
    Object.entries(colorPalette).forEach(([shade, value]) => {
        variables[`--color-${name}-${shade}`] = value;
    });
    return variables;
}

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
                // Dynamic font family using CSS custom properties
                sans: ["var(--font-primary)", ...defaultTheme.fontFamily.sans],
                secondary: [
                    "var(--font-secondary)",
                    ...defaultTheme.fontFamily.sans,
                ],
            },
            colors: {
                // Semantic colors using CSS custom properties for dynamic theming
                primary: {
                    50: "var(--color-primary-50)",
                    100: "var(--color-primary-100)",
                    200: "var(--color-primary-200)",
                    300: "var(--color-primary-300)",
                    400: "var(--color-primary-400)",
                    500: "var(--color-primary-500)",
                    600: "var(--color-primary-600)",
                    700: "var(--color-primary-700)",
                    800: "var(--color-primary-800)",
                    900: "var(--color-primary-900)",
                },
                secondary: {
                    50: "var(--color-secondary-50)",
                    100: "var(--color-secondary-100)",
                    200: "var(--color-secondary-200)",
                    300: "var(--color-secondary-300)",
                    400: "var(--color-secondary-400)",
                    500: "var(--color-secondary-500)",
                    600: "var(--color-secondary-600)",
                    700: "var(--color-secondary-700)",
                    800: "var(--color-secondary-800)",
                    900: "var(--color-secondary-900)",
                },
                surface: {
                    50: "var(--color-surface-50)",
                    100: "var(--color-surface-100)",
                    200: "var(--color-surface-200)",
                    300: "var(--color-surface-300)",
                    400: "var(--color-surface-400)",
                    500: "var(--color-surface-500)",
                    600: "var(--color-surface-600)",
                    700: "var(--color-surface-700)",
                    800: "var(--color-surface-800)",
                    900: "var(--color-surface-900)",
                },

                // Status colors (static)
                success: colors.green,
                warning: colors.yellow,
                error: colors.red,
                info: colors.cyan,
                purple: colors.purple,

                // Legacy color aliases (keep for compatibility)
                "light-blue": colors.sky,
                "cool-gray": colors.gray,
                orange: colors.orange,
                lime: colors.lime,
                teal: colors.teal,
                amber: colors.amber,
                rose: colors.rose,
                "theme-gray": {
                    100: "#dee8ea",
                    200: "#c4cfd1",
                },
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
                        color: "var(--color-secondary-300)",
                        a: {
                            color: "var(--color-primary-400)",
                            "&:hover": {
                                color: "var(--color-primary-200)",
                            },
                        },

                        h1: {
                            color: "var(--color-secondary-300)",
                        },
                        h2: {
                            color: "var(--color-secondary-300)",
                        },
                        h3: {
                            color: "var(--color-secondary-300)",
                        },
                        h4: {
                            color: "var(--color-secondary-300)",
                        },
                        h5: {
                            color: "var(--color-secondary-300)",
                        },
                        h6: {
                            color: "var(--color-secondary-300)",
                        },

                        strong: {
                            color: "var(--color-secondary-300)",
                        },

                        code: {
                            color: "var(--color-secondary-300)",
                        },

                        figcaption: {
                            color: "var(--color-secondary-500)",
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
        require("@tailwindcss/forms"),
        require("@tailwindcss/typography"),
        // Dynamic theme plugin
        function ({ addBase }) {
            // Generate CSS custom properties for each theme
            const themeStyles = {};

            // Add default theme (fallback)
            const defaultPalette = themeColorPalettes["light-blue"];
            themeStyles[":root"] = {
                // Default colors
                ...generateColorVariables(defaultPalette.primary, "primary"),
                ...generateColorVariables(
                    defaultPalette.secondary,
                    "secondary"
                ),
                ...generateColorVariables(defaultPalette.surface, "surface"),
                // Default fonts
                "--font-primary": themeFonts["Nunito"].join(", "),
                "--font-secondary": themeFonts["Nunito"].join(", "),
            };

            // Generate theme-specific styles
            Object.entries(themeColorPalettes).forEach(
                ([themeName, palette]) => {
                    themeStyles[`[data-theme="${themeName}"]`] = {
                        ...generateColorVariables(palette.primary, "primary"),
                        ...generateColorVariables(
                            palette.secondary,
                            "secondary"
                        ),
                        ...generateColorVariables(palette.surface, "surface"),
                    };
                }
            );

            // Generate font-specific styles
            Object.entries(themeFonts).forEach(([fontName, fontFamily]) => {
                themeStyles[`[data-primary-font="${fontName}"]`] = {
                    "--font-primary": fontFamily.join(", "),
                };
                themeStyles[`[data-secondary-font="${fontName}"]`] = {
                    "--font-secondary": fontFamily.join(", "),
                };
            });

            addBase(themeStyles);
        },
    ],
};
