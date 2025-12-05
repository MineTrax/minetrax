// Dynamic theme utilities for charts as a composable
export function useChartTheme() {
    // Dynamic theme colors using CSS custom properties
    const getThemeColor = (property, fallback) => {
        if (typeof document !== "undefined") {
            return (
                getComputedStyle(document.documentElement)
                    .getPropertyValue(property)
                    .trim() || fallback
            );
        }
        return fallback;
    };

    // Get theme-aware color palettes
    const getMapColorPalette = (isDark = false) => {
        return isDark
            ? [
                  getThemeColor("--color-surface-700", "#374151"), // Start with subtle background-like color
                  getThemeColor("--color-primary", "#c7d2fe"),
                  getThemeColor("--color-primary", "#818cf8"),
                  getThemeColor("--color-primary", "#4f46e5"),
                  getThemeColor("--color-primary", "#3730a3"),
              ]
            : [
                  getThemeColor("--color-foreground", "#f8fafc"), // Start with very light background-like color
                  getThemeColor("--color-primary", "#e0e7ff"),
                  getThemeColor("--color-primary", "#a5b4fc"),
                  getThemeColor("--color-primary", "#6366f1"),
                  getThemeColor("--color-primary", "#4338ca"),
              ];
    };

    // Get chart color palette (for line charts, bar charts, etc.)
    const getChartColorPalette = () => {
        return [
            getThemeColor("--color-primary", "#6366f1"),
            getThemeColor("--color-green-500", "#10b981"),
            getThemeColor("--color-yellow-500", "#f59e0b"),
            getThemeColor("--color-red-500", "#ef4444"),
            getThemeColor("--color-cyan-500", "#06b6d4"),
            getThemeColor("--color-purple-600", "#4f46e5"),
            getThemeColor("--color-emerald-600", "#059669"),
            getThemeColor("--color-amber-600", "#d97706"),
            getThemeColor("--color-pink-600", "#dc2626"),
        ];
    };

    // Get theme-aware tooltip styling
    const getTooltipStyle = () => {
        return {
            backgroundColor: getThemeColor("--color-surface-800", "#1f2937"),
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
                    areaColor: getThemeColor("--color-surface-50", "#ffffff"),
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
            ? getThemeColor("--color-surface-800", "#1e293b")
            : getThemeColor("--color-white", "#ffffff");
    };

    // Get complete theme configuration for ECharts
    const getChartTheme = (isDark = false) => {
        return {
            color: getChartColorPalette(),
            backgroundColor: getThemeColor("--color-surface-900", "#111827"),
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
