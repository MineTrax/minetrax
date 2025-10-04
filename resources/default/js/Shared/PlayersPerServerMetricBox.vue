<script setup>
import { onMounted, ref } from "vue";
import axios from "axios";
import Chart from "@/Components/Dashboard/Chart.vue";
import { Card, CardHeader, CardTitle, CardContent } from "@/Components/ui/card";

let option = ref({});
let graphData = ref(null);
let isLoading = ref(true);

onMounted(async () => {
    const response = await axios.get(route("admin.graph.players-per-server"));
    isLoading.value = false;
    graphData.value = response.data;

    option.value = {
        tooltip: {
            trigger: "item",
        },
        toolbox: {
            feature: {
                saveAsImage: {},
            },
        },
        legend: {
            top: "2%",
            left: "center",
        },
        series: [
            {
                name: "Unique Players",
                type: "pie",
                radius: ["40%", "70%"],
                avoidLabelOverlap: false,
                itemStyle: {
                    borderRadius: 7,
                    borderColor: window.colorMode === "dark" ? "#1f2937" : "#fff",
                    borderWidth: 2,
                },
                label: {
                    show: false,
                    position: "center",
                },
                emphasis: {
                    label: {
                        show: true,
                        fontSize: 40,
                        fontWeight: "bold",
                    },
                },
                labelLine: {
                    show: false,
                },
                data: graphData.value,
            },
        ],
    };
});
</script>

<template>
    <Card class="w-full h-full">
        <CardHeader>
            <CardTitle>{{ __("Players per server") }}</CardTitle>
        </CardHeader>
        <CardContent class="pt-0">
            <Chart :options="option" height="350px" :loading="isLoading" :autoresize="true" />
        </CardContent>
    </Card>
</template>
