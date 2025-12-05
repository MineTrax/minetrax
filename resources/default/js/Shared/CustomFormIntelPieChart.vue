<script setup>
import Chart from '@/Components/Dashboard/Chart.vue';
import { Card, CardContent } from '@/Components/ui/card';
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
