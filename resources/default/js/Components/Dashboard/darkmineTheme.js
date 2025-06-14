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

const contrastColor = getThemeColor("--color-secondary-300", "#cbd5e1");
const backgroundColor = getThemeColor("--color-surface-800", "#1f2937");
const axisCommon = function () {
    return {
        axisLine: {
            lineStyle: {
                color: contrastColor,
            },
        },
        splitLine: {
            lineStyle: {
                color: getThemeColor("--color-secondary-600", "#475569"),
            },
        },
        splitArea: {
            areaStyle: {
                color: ["rgba(255,255,255,0.02)", "rgba(255,255,255,0.05)"],
            },
        },
        minorSplitLine: {
            lineStyle: {
                color: getThemeColor("--color-secondary-700", "#334155"),
            },
        },
    };
};

const colorPalette = [
    getThemeColor("--color-primary-500", "#8b5cf6"),
    getThemeColor("--color-success-500", "#10b981"),
    getThemeColor("--color-warning-500", "#f59e0b"),
    getThemeColor("--color-error-500", "#ef4444"),
    getThemeColor("--color-info-500", "#06b6d4"),
    getThemeColor("--color-primary-600", "#a855f7"),
    getThemeColor("--color-success-600", "#059669"),
    getThemeColor("--color-warning-600", "#d97706"),
    getThemeColor("--color-error-600", "#dc2626"),
];
const theme = {
    darkMode: true,

    color: colorPalette,
    backgroundColor: backgroundColor,
    axisPointer: {
        lineStyle: {
            color: getThemeColor("--color-secondary-500", "#64748b"),
        },
        crossStyle: {
            color: getThemeColor("--color-secondary-500", "#64748b"),
        },
        label: {
            // TODO Contrast of label backgorundColor
            color: "#fff",
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
            color: "#EEF1FA",
        },
        subtextStyle: {
            color: getThemeColor("--color-secondary-400", "#94a3b8"),
        },
    },
    toolbox: {
        iconStyle: {
            borderColor: contrastColor,
        },
    },
    dataZoom: {
        borderColor: "#71708A",
        textStyle: {
            color: contrastColor,
        },
        brushStyle: {
            color: "rgba(135,163,206,0.3)",
        },
        handleStyle: {
            color: "#353450",
            borderColor: "#C5CBE3",
        },
        moveHandleStyle: {
            color: "#B0B6C3",
            opacity: 0.3,
        },
        fillerColor: "rgba(135,163,206,0.2)",
        emphasis: {
            handleStyle: {
                borderColor: "#91B7F2",
                color: "#4D587D",
            },
            moveHandleStyle: {
                color: "#636D9A",
                opacity: 0.7,
            },
        },
        dataBackground: {
            lineStyle: {
                color: "#71708A",
                width: 1,
            },
            areaStyle: {
                color: "#71708A",
            },
        },
        selectedDataBackground: {
            lineStyle: {
                color: "#87A3CE",
            },
            areaStyle: {
                color: "#87A3CE",
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
                color: [[1, "rgba(207,212,219,0.2)"]],
            },
        },
        axisLabel: {
            color: contrastColor,
        },
        detail: {
            color: "#EEF1FA",
        },
    },
    candlestick: {
        itemStyle: {
            color: "#f64e56",
            color0: "#54ea92",
            borderColor: "#f64e56",
            borderColor0: "#54ea92",
            // borderColor: '#ca2824',
            // borderColor0: '#09a443'
        },
    },
};
theme.categoryAxis.splitLine.show = false;

export default theme;
