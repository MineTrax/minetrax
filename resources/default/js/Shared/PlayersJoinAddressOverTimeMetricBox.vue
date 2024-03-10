<script setup>
import {computed, onMounted, ref} from 'vue';
import axios from 'axios';
import Chart from '@/Components/Dashboard/Chart.vue';
import DatePicker from 'vue-datepicker-next';
import {endOfDay, endOfMonth, startOfDay, startOfMonth, startOfYear, subDays, subMonths} from 'date-fns';
import {useTranslations} from '@/Composables/useTranslations';
const { __ } = useTranslations();

const props = defineProps({
    servers: {
        type: Array,
        required: false,
    },
    chartHeight: {
        type: String,
        default: '400px',
    },
});

let option = ref({});
let dataSets = ref([]);
let seriesNames = ref([]);
let isLoading = ref(true);
let dateRange = ref(null);

const dateRangeIsEmpty = computed(() => {
    if (dateRange.value === null || dateRange.value.length <= 0) {
        return true;
    }
    if (dateRange.value[0] === null || dateRange.value[1] === null) {
        return true;
    }
    return false;
});


async function fetchData() {
    isLoading.value = true;

    let params = {};
    if (!dateRangeIsEmpty.value) {
        params['from_date'] = dateRange.value[0];
        params['to_date'] = dateRange.value[1];
    }

    if (props.servers && props.servers.length > 0) {
        params['servers'] = props.servers;
    }

    const response = await axios.get(
        route('admin.graph.player-join-addresses.timeseries', params)
    );

    isLoading.value = false;
    dataSets.value = response.data.data;
    seriesNames.value = response.data.series;

    option.value = {
        tooltip: {
            trigger: 'axis',
            axisPointer: {
                type: 'cross',
            }
        },
        legend: {},
        toolbox: {
            feature: {
                dataZoom: {
                    yAxisIndex: 'none',
                },
                restore: {},
                saveAsImage: {},
            },
        },
        dataZoom: [
            {
                type: 'inside',
                zoomLock: true,
            },
            {
            },
        ],
        xAxis: {
            type: 'time',
        },
        yAxis: {
            type: 'value',
        },
        series: seriesNames.value.map(seriesName => ({
            name: seriesName,
            type: 'bar',
            stack: 'total',
            emphasis: {
                focus: 'series'
            },
            datasetIndex: seriesNames.value.indexOf(seriesName),
        })),
        dataset: dataSets.value,
    };
}

onMounted(async () => {
    await fetchData();
});

const datePickerShortcuts = [
    {
        text: __('Today'),
        onClick() {
            const today = new Date();
            return [startOfDay(today), endOfDay(today)];
        },
    },
    {
        text: __('Yesterday'),
        onClick() {
            const yesterday = subDays(new Date(), 1);
            return [startOfDay(yesterday), endOfDay(yesterday)];
        },
    },
    {
        text: __('Last 7 Days'),
        onClick() {
            const today = new Date();
            const sub7Days = subDays(today, 7);
            return [startOfDay(sub7Days), endOfDay(today)];
        },
    },
    {
        text: __('Last 30 Days'),
        onClick() {
            const today = new Date();
            const sub30Days = subDays(today, 30);
            return [startOfDay(sub30Days), endOfDay(today)];
        },
    },
    {
        text: __('This Month'),
        onClick() {
            const today = new Date();
            const startOfThisMonth = startOfMonth(today);
            return [startOfDay(startOfThisMonth), endOfDay(today)];
        },
    },
    {
        text: __('Last Month'),
        onClick() {
            const today = new Date();
            const startOfLastMonth = startOfMonth(subMonths(today, 1));
            const endOfLastMonth = endOfMonth(subMonths(today, 1));
            return [startOfDay(startOfLastMonth), endOfDay(endOfLastMonth)];
        },
    },
    {
        text: __('This Year'),
        onClick() {
            const today = new Date();
            const startOfThisYear = startOfYear(today);
            return [startOfDay(startOfThisYear), endOfDay(today)];
        },
    },
];
</script>

<template>
  <div class="bg-white dark:bg-cool-gray-800 rounded w-full h-full space-y-2 p-3 shadow">
    <div class="flex justify-between">
      <h3 class="font-extrabold text-gray-800 dark:text-gray-200 flex items-center">
        {{ __("Join Addresses over time") }}
      </h3>

      <DatePicker
        v-model:value="dateRange"
        type="date"
        range
        :placeholder="__('View for date range')"
        input-class="block w-full p-2 text-sm border-gray-300 rounded-md focus:border-light-blue-300 focus:ring focus:ring-light-blue-200 focus:ring-opacity-50 dark:bg-cool-gray-900 dark:text-gray-300 dark:border-gray-900"
        :shortcuts="datePickerShortcuts"
        @change="fetchData()"
      />
    </div>

    <Chart
      :options="option"
      :height="chartHeight"
      :loading="isLoading"
      :autoresize="true"
    />
  </div>
</template>
