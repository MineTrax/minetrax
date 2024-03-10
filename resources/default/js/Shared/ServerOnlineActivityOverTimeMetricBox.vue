<script setup>
import { computed, onMounted, ref } from 'vue';
import axios from 'axios';
import Chart from '@/Components/Dashboard/Chart.vue';
import DatePicker from 'vue-datepicker-next';
import {
    endOfDay,
    endOfMonth,
    startOfDay,
    startOfMonth,
    startOfYear,
    subDays,
    subMonths,
} from 'date-fns';
import { useTranslations } from '@/Composables/useTranslations';
import { ArrowTrendingUpIcon } from '@heroicons/vue/24/outline';
const { __ } = useTranslations();

let option = ref({});
let graphData = ref(null);
let isLoading = ref(true);
let dateRange = ref(null);

const props = defineProps({
    servers: {
        type: Array,
        required: false,
    },
});

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
        route('admin.graph.server-online-activity', params)
    );

    isLoading.value = false;
    graphData.value = response.data;
    option.value = {
        tooltip: {
            trigger: 'axis',
            position: function (pt) {
                return [pt[0], '10%'];
            },
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
        xAxis: {
            type: 'time',
        },
        yAxis: {
            type: 'value',
            boundaryGap: [0, '10%'],
        },
        dataZoom: [
            {
                type: 'inside',
                start: 90,
                end: 100,
                zoomLock: true,
            },
            {
                start: 90,
                end: 100,
            },
        ],
        series: graphData.value.labels.map((labelCode, index) => {
            return {
                name: labelCode,
                type: 'line',
                smooth: true,
                symbol: 'none',
                seriesLayoutBy: 'column',
                encode: {
                    y: index + 1,
                },
                emphasis: {
                    focus: 'series',
                },
            };
        }),
        dataset: {
            source: graphData.value.data,
        },
    };
}

onMounted(async () => {
    await fetchData();
});

// Note: Make this common if we reuse it in future.
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
  <div
    class="w-full h-full p-3 space-y-8 bg-white rounded shadow dark:bg-cool-gray-800"
  >
    <div class="flex justify-between">
      <h3 class="font-extrabold text-gray-800 dark:text-gray-200 flex items-center">
        <ArrowTrendingUpIcon
          class="w-6 mr-1"
        />
        {{ __("Server Online Activity") }}
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
      height="350px"
      :loading="isLoading"
      :autoresize="true"
    />
  </div>
</template>
