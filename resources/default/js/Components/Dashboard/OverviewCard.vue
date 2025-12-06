<script setup>
import Icon from "@/Components/Icon.vue";
import { Card, CardHeader, CardTitle, CardContent } from "@/Components/ui/card";

defineProps({
    title: {
        type: String,
        required: true,
    },
    value: {
        type: String,
        required: true,
    },
    desc: {
        type: String,
        required: false,
        default: null,
    },
    color: {
        type: String,
        required: false,
        default: "",
    },
    icon: {
        type: String,
        required: false,
        default: null,
    },
});
</script>

<template>
    <Card>
        <CardContent class="p-4 flex space-x-4 justify-between items-center">
            <div>
                <h3 class="text-sm font-semibold mb-2" :class="color">
                    {{ title }}
                </h3>
                <p class="text-2xl font-bold text-card-foreground">
                    {{ value }}
                </p>
                <p v-if="desc" class="text-xs text-muted-foreground">
                    {{ desc }}
                </p>
            </div>
            <div class="flex justify-center items-center">
                <icon v-if="icon" class="w-16" :class="color" :name="icon" />
            </div>
        </CardContent>
    </Card>
</template>
