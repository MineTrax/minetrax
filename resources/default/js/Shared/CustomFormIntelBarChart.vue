<script setup>
import {ref} from 'vue';
import Chart from '@/Components/Dashboard/Chart.vue';
import { Card, CardContent } from '@/Components/ui/card';

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
  <Card class="w-full h-full">
    <CardContent class="p-4 space-y-2">
      <h3 class="font-extrabold text-foreground flex items-center">
        {{ title }}
      </h3>
      <Chart
        :options="option"
        height="350px"
        :autoresize="true"
      />
    </CardContent>
  </Card>
</template>
