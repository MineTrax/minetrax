<script setup>
import {provide} from 'vue';
import VChart, {THEME_KEY} from 'vue-echarts';
import * as echarts from 'echarts';
import darkmineTheme from '@/Components/Dashboard/darkmineTheme';
import wordMap from '@/Data/Maps/world.json';

echarts.registerTheme('darkmine', darkmineTheme);
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
}

const loadingOptions = {
    text: 'Loading...',
    color: '#39b9f1',
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
