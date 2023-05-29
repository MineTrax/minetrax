<script setup>
import {onMounted, ref} from 'vue';
import axios from 'axios';
import Chart from '@/Components/Dashboard/Chart.vue';

let option = ref({});
let graphData = ref(null);
let isLoading = ref(true);

onMounted(async () => {
    const response = await axios.get(route('admin.graph.players-per-country'));

    isLoading.value = false;
    graphData.value = response.data;

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
                            <span class="font-bold">${value}</span>
                            <span class="ml-1">players</span>
                        </div>
                    </div>`;
            },
        },

        toolbox: {
            feature: {
                restore: {},
                saveAsImage: {},
            },
        },
        visualMap: {
            min: 0,
            max: graphData.value.max,
            left: 'left',
            top: 'bottom',
            text: ['High', 'Low'],
            calculable: true,
            inRange: {
                color: window.colorMode === 'dark'
                    ? ['#e7f1ff', '#89baff', '#5999ff', '#3385ff']
                    : ['#ffffff', '#718cde', '#1c46c7', '#123395']
            }
        },
        series: [
            {
                name: 'Players',
                type: 'map',
                mapType: 'world',
                roam: true,
                label: {
                    show: false,
                    emphasis: {
                        textStyle: {
                            color: window.colorMode === 'dark' ? '#fff' : '#d7d7d7',
                        },
                    },
                },
                itemStyle: {
                    normal: {
                        areaColor: '#fff',
                    },
                    emphasis: {
                        areaColor: '#2a333d',
                    },
                },
                data: graphData.value.data,
            },
        ],
    };
});
</script>

<template>
  <div class="bg-white dark:bg-cool-gray-800 rounded w-full h-full space-y-2 p-3 shadow">
    <h3 class="font-extrabold text-gray-800 dark:text-gray-200 flex items-center">
      {{ __("Player's Country") }}
    </h3>
    <Chart
      :autoresize="true"
      :options="option"
      height="400px"
      :loading="isLoading"
    />
  </div>
</template>
