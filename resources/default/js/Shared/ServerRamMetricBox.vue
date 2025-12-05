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
    series: [
        {
            type: 'line',
            symbol: 'none',
            areaStyle: {}
        }
    ],
    dataset: {
        source: props.liveInfo.map(data => {
            return {
                [__('Date')]: formatToDayDateString(data.created_at),
                [__('Used Memory (MB)')]: Math.round(data.used_memory / 1024),
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
          name="ram"
          class="w-6 mr-2"
        />
        {{ __("Memory Usage") }}
      </h3>
      <Chart
        :options="options"
        height="300px"
      />
    </CardContent>
  </Card>
</template>
