<template>
  <div
    :id="_uid"
  />
</template>

<script>
import * as echarts from 'echarts';
import {debounce} from 'lodash';
import darkmineTheme from '@/Components/Dashboard/darkmineTheme';

export default {
    name: 'EChart',
    props: {
        height: {
            type: Number,
            default: 500
        },
        options: Object
    },
    data() {
        return {
            resizeChart: null
        };
    },
    mounted() {
        echarts.registerTheme('darkmine', darkmineTheme);

        let theme = window.colorMode === 'dark' ? 'darkmine' : null;
        // Initialize the echarts instance based on the prepared dom
        const chart = echarts.init(document.getElementById(this._uid), theme, {
            height: this.height
        });

        this.resizeChart = debounce(() => {
            chart.resize();
        }, 100);
        window.addEventListener('resize', this.resizeChart);

        const defaultOptions = {
            title: {
                text: 'Chart Title',
                left: 'center',
                bottom: 20,
                textStyle: {
                    color: '#878787'
                }
            },
            tooltip: {
                trigger: 'axis',
                axisPointer: {
                    type: 'shadow'
                }
            },
            legend: {
                top: '5%',
                data: ['Sales']
            },
            xAxis: {
                data: ['Shirts', 'Cardigans', 'Chiffons', 'Pants', 'Heels', 'Socks']
            },
            yAxis: {},
            series: [
                {
                    name: 'Sales',
                    type: 'bar',
                    data: [5, 20, 36, 10, 10, 20]
                }
            ]
        };

        const chartOptions = this.options ?? defaultOptions;
        // Display the chart using the configuration items and data just specified.
        chart.setOption(chartOptions);
    },
    destroyed() {
        window.removeEventListener('resize', this.resizeChart);
    }
};
</script>
