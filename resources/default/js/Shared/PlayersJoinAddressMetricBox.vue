<script setup>
import { onMounted, ref } from "vue";
import axios from "axios";
import Chart from "@/Components/Dashboard/Chart.vue";
import { Card, CardHeader, CardTitle, CardContent } from "@/Components/ui/card";

const props = defineProps({
    servers: {
        type: Array,
        required: false,
    },
    chartHeight: {
        type: String,
        default: "350px",
    },
    topCount: {
        type: Number,
        default: null,
    },
});

let option = ref({});
let graphData = ref(null);
let isLoading = ref(true);

onMounted(async () => {
    const params = {};
    if (props.servers && props.servers.length > 0) {
        params["servers"] = props.servers;
    }

    if (props.topCount) {
        params["top"] = props.topCount;
    }

    const response = await axios.get(route("admin.graph.player-join-addresses", params));
    isLoading.value = false;
    graphData.value = response.data ?? [];

    option.value = {
        tooltip: {
            trigger: "axis",
            axisPointer: {
                type: "shadow",
            },
        },
        toolbox: {
            feature: {
                saveAsImage: {},
                dataZoom: {
                    yAxisIndex: "none",
                },
                restore: {},
                dataView: { readOnly: true },
            },
        },
        xAxis: {
            type: "category",
            data: graphData.value.map((item) => item.name),
        },
        yAxis: {
            type: "value",
        },
        series: [
            {
                name: "Players",
                type: "bar",
                barWidth: "60%",
                data: graphData.value.map((item) => item.value),
            },
        ],
    };
});
</script>

<template>
    <Card class="w-full h-full">
        <CardHeader>
            <CardTitle>{{ __("Top Join Addresses") }}</CardTitle>
        </CardHeader>
        <CardContent class="pt-0">
            <Chart :options="option" :height="chartHeight" :loading="isLoading" :autoresize="true" />
        </CardContent>
    </Card>
</template>
