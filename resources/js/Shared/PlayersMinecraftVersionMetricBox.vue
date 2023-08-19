<script setup>
import {onMounted, ref} from 'vue';
import axios from 'axios';
import Chart from '@/Components/Dashboard/Chart.vue';

let option = ref({});
let graphData = ref(null);
let isLoading = ref(true);

onMounted(async () => {
    const response = await axios.get(route('admin.graph.player-minecraft-versions'));
    isLoading.value = false;
    graphData.value = response.data ?? [];

    option.value = {
        tooltip: {
            trigger: 'axis',
            axisPointer: {
                type: 'shadow'
            }
        },
        toolbox: {
            feature: {
                saveAsImage: {}
            }
        },
        xAxis: {
            type: 'category',
            data: graphData.value.map((item) => item.name),
        },
        yAxis: {
            type: 'value'
        },
        series: [
            {
                name: 'Players',
                type: 'bar',
                barWidth: '60%',
                data: graphData.value.map((item) => item.value)
            }
        ]
    };
});
</script>

<template>
  <div class="bg-white dark:bg-cool-gray-800 rounded w-full h-full space-y-2 p-3 shadow">
    <h3 class="font-extrabold text-gray-800 dark:text-gray-200 flex items-center">
      {{ __("Player Client Versions") }}
    </h3>
    <Chart
      :options="option"
      height="350px"
      :loading="isLoading"
      :autoresize="true"
    />
  </div>
</template>
