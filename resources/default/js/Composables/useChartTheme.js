// Helper to resolve CSS color strings (e.g. modern hsl() syntax) to hex
// for ECharts compatibility. ECharts/zrender cannot parse modern CSS color
// functions like `hsl(217 91% 60%)` (space-separated), so we use a canvas
// context which normalizes any valid CSS color to hex.
let _colorResolverCtx = null;
function resolveCssColor(cssColor) {
    if (
        !cssColor ||
        cssColor === "transparent" ||
        cssColor.startsWith("#") ||
        cssColor.startsWith("rgb")
    ) {
        return cssColor;
    }
    try {
        if (typeof document === "undefined") return cssColor;
        if (!_colorResolverCtx) {
            _colorResolverCtx = document
                .createElement("canvas")
                .getContext("2d");
        }
        _colorResolverCtx.fillStyle = "#000000";
        _colorResolverCtx.fillStyle = cssColor;
        return _colorResolverCtx.fillStyle;
    } catch {
        return cssColor;
    }
}

// Dynamic theme utilities for charts as a composable
export function useChartTheme() {
    // Dynamic theme colors using CSS custom properties
    // Returns resolved hex color for ECharts compatibility
    const getThemeColor = (property, fallback) => {
        if (typeof document !== "undefined") {
            const raw = getComputedStyle(document.documentElement)
                .getPropertyValue(property)
                .trim();
            if (!raw) return fallback;
            return resolveCssColor(raw);
        }
        return fallback;
    };

    // Get theme-aware color palettes for map visualMap gradient
    // Uses --color-chart-* variables which provide distinct shades
    const getMapColorPalette = (isDark = false) => {
        return isDark
            ? [
                getThemeColor("--color-muted-foreground", "#374151"), // Subtle no-data color
                getThemeColor("--color-chart-1", "#c7d2fe"),
                getThemeColor("--color-chart-2", "#818cf8"),
                getThemeColor("--color-chart-3", "#4f46e5"),
                getThemeColor("--color-chart-5", "#3730a3"),
            ]
            : [
                getThemeColor("--color-muted", "#f8fafc"), // Light no-data color
                getThemeColor("--color-chart-1", "#e0e7ff"),
                getThemeColor("--color-chart-2", "#a5b4fc"),
                getThemeColor("--color-chart-3", "#6366f1"),
                getThemeColor("--color-chart-5", "#4338ca"),
            ];
    };

    // Get chart color palette (for line charts, bar charts, etc.)
    const getChartColorPalette = () => {
        return [
            getThemeColor("--color-primary", "#6366f1"),
            getThemeColor("--color-success", "#10b981"),
            getThemeColor("--color-yellow-500", "#f59e0b"),
            getThemeColor("--color-destructive", "#ef4444"),
            getThemeColor("--color-info", "#06b6d4"),
            getThemeColor("--color-purple-600", "#4f46e5"),
            getThemeColor("--color-emerald-600", "#059669"),
            getThemeColor("--color-amber-600", "#d97706"),
            getThemeColor("--color-pink-600", "#dc2626"),
        ];
    };

    // Get theme-aware tooltip styling
    const getTooltipStyle = () => {
        return {
            backgroundColor: getThemeColor("--color-card", "#1f2937"),
            textStyle: {
                color: getThemeColor("--color-foreground", "#e5e7eb"),
            },
            borderColor: getThemeColor("--color-foreground", "#4b5563"),
            borderWidth: 1,
        };
    };

    // Get theme-aware axis styling
    const getAxisStyle = () => {
        return {
            axisLine: {
                lineStyle: {
                    color: getThemeColor("--color-foreground", "#9ca3af"),
                },
            },
            splitLine: {
                lineStyle: {
                    color: getThemeColor("--color-foreground", "#4b5563"),
                },
            },
            axisLabel: {
                color: getThemeColor("--color-foreground", "#d1d5db"),
            },
        };
    };

    // Get theme-aware legend styling
    const getLegendStyle = () => {
        return {
            textStyle: {
                color: getThemeColor("--color-foreground", "#d1d5db"),
            },
        };
    };

    // Get theme-aware toolbox styling
    const getToolboxStyle = () => {
        return {
            iconStyle: {
                borderColor: getThemeColor("--color-foreground", "#9ca3af"),
            },
        };
    };

    // Get theme-aware map styling
    const getMapStyle = () => {
        return {
            itemStyle: {
                normal: {
                    areaColor: getThemeColor("--color-muted", "#ffffff"),
                    borderColor: getThemeColor(
                        "--color-foreground",
                        "#d1d5db"
                    ),
                    borderWidth: 0.5,
                },
                emphasis: {
                    areaColor: getThemeColor(
                        "--color-foreground",
                        "#e5e7eb"
                    ),
                    borderColor: getThemeColor("--color-primary", "#6366f1"),
                    borderWidth: 1,
                },
            },
            label: {
                show: false,
                emphasis: {
                    textStyle: {
                        color: getThemeColor(
                            "--color-foreground",
                            "#f3f4f6"
                        ),
                    },
                },
            },
        };
    };

    // Get color for areas with no data that blends with background
    const getNoDataColor = (isDark = false) => {
        return isDark
            ? getThemeColor("--color-card", "#1e293b")
            : getThemeColor("--color-white", "#ffffff");
    };

    // Get complete theme configuration for ECharts
    const getChartTheme = (isDark = false) => {
        return {
            color: getChartColorPalette(),
            backgroundColor: getThemeColor("--color-background", "#111827"),
            textStyle: {
                color: getThemeColor("--color-foreground", "#d1d5db"),
            },
            title: {
                textStyle: {
                    color: getThemeColor("--color-foreground", "#f3f4f6"),
                },
            },
            tooltip: getTooltipStyle(),
            legend: getLegendStyle(),
            toolbox: getToolboxStyle(),
            axisPointer: {
                lineStyle: {
                    color: getThemeColor("--color-foreground", "#6b7280"),
                },
            },
        };
    };

    return {
        getThemeColor,
        getMapColorPalette,
        getChartColorPalette,
        getTooltipStyle,
        getAxisStyle,
        getLegendStyle,
        getToolboxStyle,
        getMapStyle,
        getNoDataColor,
        getChartTheme,
    };
}
