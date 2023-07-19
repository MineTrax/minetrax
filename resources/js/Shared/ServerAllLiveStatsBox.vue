<template>
  <div class="bg-white dark:bg-cool-gray-800 rounded w-full h-full space-y-2 p-3 shadow">
    <h3 class="font-extrabold text-gray-800 dark:text-gray-200 flex items-center">
      <icon
        name="server"
        class="w-6 mr-1"
      />
      {{ __("Server Performance") }}
    </h3>
    <Chart
      :options="options"
      height="300px"
    />
  </div>
</template>

<script>
import Icon from '@/Components/Icon.vue';
import { useHelpers } from '@/Composables/useHelpers';
import Chart from '@/Components/Dashboard/Chart.vue';

export default {
    name: 'ServerAllLiveStatsBox',
    components: {Chart, Icon},
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
            legend: {},
            series: [
                {
                    name: this.__('Online Players'),
                    type: 'line',
                    symbol: 'none',
                },
                {
                    name: this.__('CPU Load'),
                    type: 'line',
                    symbol: 'none',
                },
                {
                    name: this.__('Memory Usage'),
                    type: 'line',
                    symbol: 'none',
                },
                {
                    name: this.__('TPS'),
                    type: 'line',
                    symbol: 'none',
                },
                {
                    name: this.__('Chunks Loaded'),
                    type: 'line',
                    symbol: 'none',
                }
            ],
            dataset: {
                source: this.liveInfo.map(data => {
                    return {
                        [this.__('Date')]: this.formatToDayDateString(data.created_at),
                        [this.__('Online Players')]: data.online_players,
                        [this.__('Cpu Load')]: data.cpu_load,
                        [this.__('Memory Usage')]: Math.round(data.used_memory / (1024)),
                        [this.__('TPS')]: data.tps,
                        [this.__('Chunks Loaded')]: data.chunks_loaded,
                    };
                })
            },
        };
    }
};
</script>
