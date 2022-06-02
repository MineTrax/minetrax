<template>
  <div class="bg-white dark:bg-cool-gray-800 rounded w-full h-full space-y-2 p-3 shadow">
    <h3 class="font-extrabold text-gray-800 dark:text-gray-200 flex items-center">
      <icon
        name="server"
        class="w-6 mr-1"
      />
      Server Performance
    </h3>
    <e-chart
      :options="options"
      :height="300"
    />
  </div>
</template>

<script>
import EChart from '@/Components/Dashboard/EChart';
import {format} from 'date-fns';
import Icon from '@/Components/Icon';

export default {
    name: 'ServerAllLiveStatsBox',
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
                    name: 'Online Players',
                    type: 'line',
                    symbol: 'none',
                },
                {
                    name: 'CPU Load',
                    type: 'line',
                    symbol: 'none',
                },
                {
                    name: 'Memory Usage',
                    type: 'line',
                    symbol: 'none',
                },
                {
                    name: 'TPS',
                    type: 'line',
                    symbol: 'none',
                },
                {
                    name: 'Chunks Loaded',
                    type: 'line',
                    symbol: 'none',
                }
            ],
            dataset: {
                source: this.liveInfo.map(data => {
                    return {
                        'Date': format(new Date(data.created_at), 'do MMM yyyy, h:mm aaa'),
                        'Online Players': data.online_players,
                        'Cpu Load': data.cpu_load,
                        'Memory Usage': Math.round(data.used_memory / (1024)),
                        'TPS': data.tps,
                        'Chunks Loaded': data.chunks_loaded,
                    };
                })
            },
        };
    }
};
</script>
