import { useChartTheme } from "@/Composables/useChartTheme.js";

// Use the composable to get theme utilities
const { getThemeColor, getChartColorPalette } = useChartTheme();

const contrastColor = getThemeColor("--color-secondary-700", "#374151");
const backgroundColor = getThemeColor("--color-white", "#ffffff");
const axisCommon = function () {
    return {
        axisLine: {
            lineStyle: {
                color: contrastColor,
            },
        },
        splitLine: {
            lineStyle: {
                color: getThemeColor("--color-secondary-200", "#e5e7eb"),
            },
        },
        splitArea: {
            areaStyle: {
                color: ["rgba(0,0,0,0.02)", "rgba(0,0,0,0.05)"],
            },
        },
        minorSplitLine: {
            lineStyle: {
                color: getThemeColor("--color-secondary-100", "#f3f4f6"),
            },
        },
    };
};

// Use the composable's color palette or define custom one
const colorPalette = getChartColorPalette();

const theme = {
    darkMode: false,

    color: colorPalette,
    backgroundColor: backgroundColor,
    axisPointer: {
        lineStyle: {
            color: getThemeColor("--color-secondary-400", "#9ca3af"),
        },
        crossStyle: {
            color: getThemeColor("--color-secondary-400", "#9ca3af"),
        },
        label: {
            color: "#000",
        },
    },
    legend: {
        textStyle: {
            color: contrastColor,
        },
    },
    textStyle: {
        color: contrastColor,
    },
    title: {
        textStyle: {
            color: "#1f2937",
        },
        subtextStyle: {
            color: getThemeColor("--color-secondary-500", "#6b7280"),
        },
    },
    toolbox: {
        iconStyle: {
            borderColor: contrastColor,
        },
    },
    dataZoom: {
        borderColor: "#9ca3af",
        textStyle: {
            color: contrastColor,
        },
        brushStyle: {
            color: "rgba(59,130,246,0.3)",
        },
        handleStyle: {
            color: "#f3f4f6",
            borderColor: "#6b7280",
        },
        moveHandleStyle: {
            color: "#9ca3af",
            opacity: 0.3,
        },
        fillerColor: "rgba(59,130,246,0.2)",
        emphasis: {
            handleStyle: {
                borderColor: "#3b82f6",
                color: "#e5e7eb",
            },
            moveHandleStyle: {
                color: "#6b7280",
                opacity: 0.7,
            },
        },
        dataBackground: {
            lineStyle: {
                color: "#9ca3af",
                width: 1,
            },
            areaStyle: {
                color: "#9ca3af",
            },
        },
        selectedDataBackground: {
            lineStyle: {
                color: "#3b82f6",
            },
            areaStyle: {
                color: "#3b82f6",
            },
        },
    },
    visualMap: {
        textStyle: {
            color: contrastColor,
        },
    },
    timeline: {
        lineStyle: {
            color: contrastColor,
        },
        label: {
            color: contrastColor,
        },
        controlStyle: {
            color: contrastColor,
            borderColor: contrastColor,
        },
    },
    calendar: {
        itemStyle: {
            color: backgroundColor,
        },
        dayLabel: {
            color: contrastColor,
        },
        monthLabel: {
            color: contrastColor,
        },
        yearLabel: {
            color: contrastColor,
        },
    },
    timeAxis: axisCommon(),
    logAxis: axisCommon(),
    valueAxis: axisCommon(),
    categoryAxis: axisCommon(),

    line: {
        symbol: "circle",
    },
    graph: {
        color: colorPalette,
    },
    gauge: {
        title: {
            color: contrastColor,
        },
        axisLine: {
            lineStyle: {
                color: [[1, "rgba(156,163,175,0.2)"]],
            },
        },
        axisLabel: {
            color: contrastColor,
        },
        detail: {
            color: "#1f2937",
        },
    },
    candlestick: {
        itemStyle: {
            color: "#ef4444",
            color0: "#22c55e",
            borderColor: "#ef4444",
            borderColor0: "#22c55e",
        },
    },
};
theme.categoryAxis.splitLine.show = false;

export default theme;
