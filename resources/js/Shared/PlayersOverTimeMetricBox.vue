<script setup>
import {onMounted, ref} from 'vue';
import axios from 'axios';
import Chart from '@/Components/Dashboard/Chart.vue';

let option = ref({});
let graphData = ref(null);
let isLoading = ref(true);

onMounted(async () => {
    const response = await axios.get(route('admin.graph.online-players'));
    isLoading.value = false;
    graphData.value = response.data;
    option.value = {
        tooltip: {
            trigger: 'axis',
            position: function (pt) {
                return [pt[0], '10%'];
            }
        },
        legend: {},
        toolbox: {
            feature: {
                dataZoom: {
                    yAxisIndex: 'none'
                },
                restore: {},
                saveAsImage: {}
            }
        },
        xAxis: {
            type: 'time',
        },
        yAxis: {
            type: 'value',
            boundaryGap: [0, '10%']
        },
        dataZoom: [
            {
                type: 'inside',
                start: 90,
                end: 100,
                zoomLock: true,
            },
            {
                start: 90,
                end: 100,
            }
        ],
        series: graphData.value.servers.map(
            (serverName, index) => {
                return {
                    name: serverName,
                    type: 'line',
                    smooth: true,
                    symbol: 'none',
                    seriesLayoutBy: 'column',
                    encode: {
                        y: index + 1
                    },
                    emphasis: {
                        focus: 'series'
                    },
                };
            }
        ),
        dataset: {
            source: graphData.value.data
        }
    };
});
</script>

<template>
  <div class="bg-white dark:bg-cool-gray-800 rounded w-full h-full space-y-2 p-3 shadow">
    <h3 class="font-extrabold text-gray-800 dark:text-gray-200 flex items-center">
      {{ __("Online Players") }}
    </h3>
    <Chart
      :options="option"
      height="350px"
      :loading="isLoading"
      :autoresize="true"
    />
  </div>
</template>
