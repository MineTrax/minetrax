<script setup>
import darkmineTheme from "@/Components/Dashboard/darkmineTheme";
import lightmineTheme from "@/Components/Dashboard/lightmineTheme";
import wordMap from "@/Data/Maps/world.json";
import * as echarts from "echarts";
import { provide } from "vue";
import VChart, { THEME_KEY } from "vue-echarts";

echarts.registerTheme("darkmine", darkmineTheme);
echarts.registerTheme("lightmine", lightmineTheme);
echarts.registerMap("world", wordMap);

defineProps({
    options: {
        type: Object,
        required: true
    },
    autoresize: {
        type: Boolean,
        default: true
    },
    height: {
        type: String,
        default: "500px"
    },
});

if (window.colorMode === "dark") {
    provide(THEME_KEY, "darkmine");
} else {
    provide(THEME_KEY, "lightmine");
}

// Get CSS custom properties for dynamic theming
// Resolves to hex for ECharts compatibility (modern CSS hsl syntax is not supported by zrender)
const getThemeColor = (property) => {
    const raw = getComputedStyle(document.documentElement).getPropertyValue(property).trim();
    if (!raw || raw.startsWith("#") || raw.startsWith("rgb")) return raw;
    try {
        const ctx = document.createElement("canvas").getContext("2d");
        ctx.fillStyle = "#000000";
        ctx.fillStyle = raw;
        return ctx.fillStyle;
    } catch {
        return raw;
    }
};

const loadingOptions = {
    text: "Loading...",
    color: getThemeColor("--color-primary") || "#00bbff",
    textColor: window.colorMode === "dark" ? "#fff" : "#000",
    maskColor: "transparent",
};
</script>

<template>
  <VChart
    class="chart"
    :style="{ height: height }"
    :option="options"
    :autoresize="autoresize"
    :loading-options="loadingOptions"
  />
</template>
