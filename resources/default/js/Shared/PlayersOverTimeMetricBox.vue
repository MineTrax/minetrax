<script setup>
import { onMounted, ref } from "vue";
import axios from "axios";
import Chart from "@/Components/Dashboard/Chart.vue";
import { Card, CardHeader, CardTitle, CardContent } from "@/Components/ui/card";

let option = ref({});
let graphData = ref(null);
let isLoading = ref(true);

onMounted(async () => {
    const response = await axios.get(route("admin.graph.online-players"));
    isLoading.value = false;
    graphData.value = response.data;
    option.value = {
        tooltip: {
            trigger: "axis",
            position: function (pt) {
                return [pt[0], "10%"];
            },
        },
        legend: {},
        toolbox: {
            feature: {
                dataZoom: {
                    yAxisIndex: "none",
                },
                restore: {},
                saveAsImage: {},
            },
        },
        xAxis: {
            type: "time",
        },
        yAxis: {
            type: "value",
            boundaryGap: [0, "10%"],
        },
        dataZoom: [
            {
                type: "inside",
                start: 90,
                end: 100,
                zoomLock: true,
            },
            {
                start: 90,
                end: 100,
            },
        ],
        series: graphData.value.servers.map((serverName, index) => {
            return {
                name: serverName,
                type: "line",
                smooth: true,
                symbol: "none",
                seriesLayoutBy: "column",
                encode: {
                    y: index + 1,
                },
                emphasis: {
                    focus: "series",
                },
            };
        }),
        dataset: {
            source: graphData.value.data,
        },
    };
});
</script>

<template>
    <Card class="w-full h-full">
        <CardHeader>
            <CardTitle>{{ __("Online Players") }}</CardTitle>
        </CardHeader>
        <CardContent class="pt-0">
            <Chart :options="option" height="350px" :loading="isLoading" :autoresize="true" />
        </CardContent>
    </Card>
</template>
