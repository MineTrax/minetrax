<script setup>
import {provide} from 'vue';
import VChart, {THEME_KEY} from 'vue-echarts';
import * as echarts from 'echarts';
import darkmineTheme from '@/Components/Dashboard/darkmineTheme';
import lightmineTheme from '@/Components/Dashboard/lightmineTheme';
import wordMap from '@/Data/Maps/world.json';

echarts.registerTheme('darkmine', darkmineTheme);
echarts.registerTheme('lightmine', lightmineTheme);
echarts.registerMap('world', wordMap);

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
        default: '500px'
    },
});

if (window.colorMode === 'dark') {
    provide(THEME_KEY, 'darkmine');
} else {
    provide(THEME_KEY, 'lightmine');
}

// Get CSS custom properties for dynamic theming
const getThemeColor = (property) => {
    return getComputedStyle(document.documentElement).getPropertyValue(property).trim();
};

const loadingOptions = {
    text: 'Loading...',
    color: getThemeColor('--color-primary-500') || '#8b5cf6',
    textColor: window.colorMode === 'dark' ? '#fff' : '#000',
    maskColor: 'transparent',
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
