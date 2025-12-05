<script setup>
import { Card, CardHeader, CardTitle, CardContent } from "@/Components/ui/card";

defineProps({
    icon: {
        type: [Object, Function],
        required: true,
    },
    iconClass: {
        type: [Array, String],
        required: false,
        default: "text-muted-foreground bg-muted",
    },
    title: {
        type: [String, Number],
        required: true,
    },
    subValue: {
        type: [String, Number],
        required: false,
        default: null,
    },
    subValueClass: {
        type: [Array, String],
        required: false,
        default: "text-foreground",
    },
    description: {
        type: [String, Number],
        required: false,
        default: null,
    },
    descriptionClass: {
        type: [Array, String],
        required: false,
        default: "text-foreground",
    },
    value: {
        type: [String, Number],
        required: true,
    },
    change: {
        type: [String, Number],
        required: false,
        default: null,
    },
    changeClass: {
        type: [Array, String],
        required: false,
        default: "text-muted-foreground",
    },
    changeDesc: {
        type: [String, Number],
        required: false,
        default: null,
    },
});
</script>

<template>
    <Card>
        <CardHeader class="flex flex-row items-center space-x-2 pb-2">
            <component :is="icon" class="w-6 h-6 p-1 rounded-full" :class="iconClass" />
            <CardTitle class="text-sm font-medium">
                {{ __(title) }}
            </CardTitle>
        </CardHeader>
        <CardContent>
            <div class="flex items-end justify-between space-x-4">
                <div>
                    <p class="text-4xl font-bold text-card-foreground">
                        {{ value }}
                        <span v-if="subValue" class="text-sm font-semibold" :class="subValueClass">
                            {{ subValue }}
                        </span>
                    </p>

                    <p v-if="description" class="text-xs" :class="descriptionClass">
                        {{ __(description) }}
                    </p>
                </div>
                <div v-if="change" class="text-end">
                    <span class="text-sm w-12 font-bold rounded-full px-2 py-0.5" :class="changeClass">
                        {{ change }}
                    </span>
                    <p class="text-xs text-muted-foreground whitespace-nowrap">
                        {{ __(changeDesc) }}
                    </p>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
