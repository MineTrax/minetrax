<script setup>
import EChart from '@/Components/Dashboard/EChart.vue';
import { useAxios } from '@vueuse/integrations/useAxios';
const { graphData, isFinished, isLoading } = useAxios(route('admin.graph.online-players'));
import {ref} from 'vue';

console.log(graphData);
console.log({isFinished});
console.log({isLoading});

let option = ref({});

let data = [
    [1627660800000, 1, 5],
    [1627660900000, 1, 6],
    [1627660910000, 1, 5],
    [1627660920000, 1, 2],
    [1627660930000, 1, 3],
    [1627660940000, 1, 1],
];

option = {
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
        boundaryGap: true
    },
    yAxis: {
        type: 'value',
        boundaryGap: [0, '100%']
    },
    dataZoom: [
        {
            type: 'inside',
        },
        {}
    ],
    series: [
        {
            name: 'Fake Data',
            type: 'line',
            smooth: true,
            symbol: 'none',
            data: data,
        },
    ]
};

</script>

<template>
  <div class="bg-white dark:bg-cool-gray-800 rounded w-full h-full space-y-2 p-3 shadow">
    <h3 class="font-extrabold text-gray-800 dark:text-gray-200 flex items-center">
      {{ __("Player Activity") }}
    </h3>
    <EChart
      :options="option"
      :height="350"
    />
  </div>
</template>
