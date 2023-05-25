<template>
  <div class="bg-white dark:bg-cool-gray-800 rounded w-full h-full space-y-2 p-3 shadow">
    <h3 class="font-extrabold text-gray-800 dark:text-gray-200 flex items-center">
      <icon
        name="cpu"
        class="w-6 mr-1"
      />
      {{ __("CPU Load") }}
    </h3>
    <e-chart
      :options="options"
      :height="300"
    />
  </div>
</template>

<script>
import EChart from '@/Components/Dashboard/EChart.vue';
import Icon from '@/Components/Icon.vue';
import { useHelpers } from '@/Composables/useHelpers';

export default {
    name: 'ServerCpuMetricBox',
    components: {Icon, EChart},
    props: {
        serverId: {
            type: Number,
            default: null
        },
        liveInfo: {
            type: Array,
            default: null
        }
    },
    setup() {
        const {formatToDayDateString} = useHelpers();
        return {formatToDayDateString};
    },
    beforeMount() {
        this.options = {
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
                source: this.liveInfo.map(data => {
                    return {
                        [this.__('Date')]: this.formatToDayDateString(data.created_at),
                        [this.__('Cpu Load')]: data.cpu_load,
                    };
                })
            },
        };
    }
};
</script>
