<script setup>
import {onMounted, ref} from 'vue';
import axios from 'axios';
import Chart from '@/Components/Dashboard/Chart.vue';

const props = defineProps({
    servers: {
        type: Array,
        required: false,
    },
    chartHeight: {
        type: String,
        default: '350px',
    },
    topCount: {
        type: Number,
        default: null,
    },
});

let option = ref({});
let graphData = ref(null);
let isLoading = ref(true);

onMounted(async () => {
    const params = {};
    if (props.servers && props.servers.length > 0) {
        params['servers'] = props.servers;
    }

    if (props.topCount) {
        params['top'] = props.topCount;
    }
    console.log('params', params);

    const response = await axios.get(route('admin.graph.player-join-addresses', params));
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
});
</script>

<template>
  <div class="bg-white dark:bg-cool-gray-800 rounded w-full h-full space-y-2 p-3 shadow">
    <h3 class="font-extrabold text-gray-800 dark:text-gray-200 flex items-center">
      {{ __("Top Join Addresses") }}
    </h3>
    <Chart
      :options="option"
      :height="chartHeight"
      :loading="isLoading"
      :autoresize="true"
    />
  </div>
</template>
