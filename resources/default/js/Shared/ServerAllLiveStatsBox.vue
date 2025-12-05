<script setup>
import Icon from '@/Components/Icon.vue';
import { useHelpers } from '@/Composables/useHelpers';
import { useTranslations } from '@/Composables/useTranslations';
import Chart from '@/Components/Dashboard/Chart.vue';
import { Card, CardContent } from '@/Components/ui/card';
import { ref } from 'vue';

const { __ } = useTranslations();
const { formatToDayDateString } = useHelpers();

const props = defineProps({
    serverId: {
        type: Number,
        default: null
    },
    liveInfo: {
        type: Array,
        default: null
    }
});

const options = ref({
    xAxis: {
        type: 'category',
        boundaryGap: false
    },
    yAxis: {
        type: 'value'
    },
    tooltip: {
        trigger: 'axis'
    },
    legend: {},
    series: [
        {
            name: __('Online Players'),
            type: 'line',
            symbol: 'none',
        },
        {
            name: __('CPU Load'),
            type: 'line',
            symbol: 'none',
        },
        {
            name: __('Memory Usage'),
            type: 'line',
            symbol: 'none',
        },
        {
            name: __('TPS'),
            type: 'line',
            symbol: 'none',
        },
        {
            name: __('Chunks Loaded'),
            type: 'line',
            symbol: 'none',
        }
    ],
    dataset: {
        source: props.liveInfo.map(data => {
            return {
                [__('Date')]: formatToDayDateString(data.created_at),
                [__('Online Players')]: data.online_players,
                [__('Cpu Load')]: data.cpu_load,
                [__('Memory Usage')]: Math.round(data.used_memory / (1024)),
                [__('TPS')]: data.tps,
                [__('Chunks Loaded')]: data.chunks_loaded,
            };
        })
    },
});
</script>

<template>
  <Card class="w-full h-full">
    <CardContent class="p-4 space-y-2">
      <h3 class="font-extrabold text-foreground flex items-center">
        <Icon
          name="server"
          class="w-6 mr-2"
        />
        {{ __("Server Performance") }}
      </h3>
      <Chart
        :options="options"
        height="300px"
      />
    </CardContent>
  </Card>
</template>
