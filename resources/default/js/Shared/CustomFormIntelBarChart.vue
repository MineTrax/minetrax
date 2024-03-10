<script setup>
import {ref} from 'vue';
import Chart from '@/Components/Dashboard/Chart.vue';

let option = ref({});
let graphData = ref(null);

const props = defineProps({
    title: {
        type: String,
    },
    data: {
        type: Object,
    }
});
const result = Object.entries(props.data).map(([name, value]) => ({ name, value }));
graphData.value = result;
option.value = {
    tooltip: {
        trigger: 'axis',
        axisPointer: {
            type: 'shadow'
        }
    },
    toolbox: {
        feature: {
            saveAsImage: {},
            dataZoom: {
                yAxisIndex: 'none',
            },
            restore: {},
            dataView: { readOnly: true },
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
            name: 'Count',
            type: 'bar',
            barWidth: '60%',
            data: graphData.value.map((item) => item.value)
        }
    ]
};
</script>

<template>
  <div class="bg-white dark:bg-cool-gray-800 rounded w-full h-full space-y-2 p-3 shadow">
    <h3 class="font-extrabold text-gray-800 dark:text-gray-200 flex items-center">
      {{ title }}
    </h3>
    <Chart
      :options="option"
      height="350px"
      :autoresize="true"
    />
  </div>
</template>
