<script setup>
import VChart, {THEME_KEY} from 'vue-echarts';
import {onMounted, ref, provide} from 'vue';
import axios from 'axios';

let option = ref({});
let graphData = ref(null);
let isLoading = ref(true);

if (window.colorMode === 'dark') {
    provide(THEME_KEY, 'darkmine');
}
onMounted(async () => {
    const response = await axios.get(route('admin.graph.players-per-server'));
    isLoading.value = false;
    graphData.value = response.data;

    option.value = {
        tooltip: {
            trigger: 'item'
        },
        toolbox: {
            feature: {
                saveAsImage: {}
            }
        },
        legend: {
            top: '2%',
            left: 'center'
        },
        series: [
            {
                name: 'Unique Players',
                type: 'pie',
                radius: ['40%', '70%'],
                avoidLabelOverlap: false,
                itemStyle: {
                    borderRadius: 7,
                    borderColor: '#fff',
                    borderWidth: 2
                },
                label: {
                    show: false,
                    position: 'center'
                },
                emphasis: {
                    label: {
                        show: true,
                        fontSize: 40,
                        fontWeight: 'bold'
                    }
                },
                labelLine: {
                    show: false
                },
                data: graphData.value
            }
        ]
    };
});
</script>

<template>
  <div class="bg-white dark:bg-cool-gray-800 rounded w-full h-full space-y-2 p-3 shadow">
    <h3 class="font-extrabold text-gray-800 dark:text-gray-200 flex items-center">
      {{ __("Players per server") }}
    </h3>
    <VChart
      class="chart"
      :loading="isLoading"
      :option="option"
      autoresize
    />
  </div>
</template>

<style scoped>
.chart {
    height: 350px;
}
</style>
