<script setup>
import {onMounted, ref} from 'vue';
import axios from 'axios';
import Chart from '@/Components/Dashboard/Chart.vue';
import { useChartTheme } from '@/Composables/useChartTheme.js';

const {
    getMapColorPalette,
    getTooltipStyle,
    getToolboxStyle,
    getMapStyle,
    getNoDataColor
} = useChartTheme();

let option = ref({});
let graphData = ref(null);
let isLoading = ref(true);

const props = defineProps({
    routeName: {
        type: String,
        required: false,
        default: route('admin.graph.players-per-country'),
    },
    mapHeight: {
        type: String,
        required: false,
        default: '410px',
    }
});

onMounted(async () => {
    const response = await axios.get(props.routeName);

    isLoading.value = false;
    graphData.value = response.data;

        // Get theme-aware styling
    const isDark = window.colorMode === 'dark';
    const tooltipStyle = getTooltipStyle();
    const toolboxStyle = getToolboxStyle();
    const mapStyle = getMapStyle();
    const mapColors = getMapColorPalette(isDark);
    const noDataColor = getNoDataColor(isDark);

    option.value = {
        tooltip: {
            formatter: function(params) {
                const { name, value } = params.data;
                const image = params.data.image;
                return `
                    <div class="flex flex-col">
                        <div class="flex flex-row items-center">
                            <img :alt="name" src="${image}" class="w-8 h-8 mr-2" />
                            <span class="font-bold">${name}</span>
                        </div>
                        <div class="flex flex-row justify-center items-center">
                            <span class="font-bold">${value || 0}</span>
                            <span class="ml-1">players</span>
                        </div>
                    </div>`;
            },
            ...tooltipStyle
        },

        toolbox: {
            feature: {
                restore: {},
                saveAsImage: {},
                dataView: { readOnly: true },
            },
            ...toolboxStyle
        },
        visualMap: {
            min: 0,
            max: graphData.value.max,
            left: 'left',
            top: 'bottom',
            text: ['High', 'Low'],
            textStyle: {
                color: tooltipStyle.textStyle.color
            },
            calculable: true,
            inRange: {
                color: mapColors
            },
            // Handle areas with no data
            outOfRange: {
                color: noDataColor
            }
        },
        series: [
            {
                name: 'Players',
                type: 'map',
                mapType: 'world',
                roam: true,
                itemStyle: {
                    normal: {
                        // Areas with no data will use background color
                        areaColor: noDataColor,
                        borderColor: isDark ? 'rgba(100, 116, 139, 0.3)' : 'rgba(148, 163, 184, 0.3)',
                        borderWidth: 0.5
                    },
                    emphasis: {
                        areaColor: isDark ? 'rgba(100, 116, 139, 0.6)' : 'rgba(148, 163, 184, 0.6)',
                        borderColor: mapColors[mapColors.length - 1],
                        borderWidth: 1
                    },
                },
                label: {
                    show: false,
                    emphasis: {
                        textStyle: {
                            color: tooltipStyle.textStyle.color,
                        },
                    },
                },
                data: graphData.value.data,
            },
        ],
    };
});
</script>

<template>
  <div class="bg-white dark:bg-surface-800 rounded w-full h-full space-y-2 p-3 shadow">
    <h3 class="font-extrabold text-foreground dark:text-foreground flex items-center">
      {{ __("Player's Country") }}
    </h3>
    <Chart
      :autoresize="true"
      :options="option"
      :height="mapHeight"
      :loading="isLoading"
    />
  </div>
</template>
