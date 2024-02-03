<script setup>
import Chart from '@/Components/Dashboard/Chart.vue';
import { ref } from 'vue';

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
        trigger: 'item'
    },
    toolbox: {
        feature: {
            saveAsImage: {},
            dataView: { readOnly: true },
        }
    },
    legend: {
        top: '2%',
        left: 'center'
    },
    series: [
        {
            name: 'Count',
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
