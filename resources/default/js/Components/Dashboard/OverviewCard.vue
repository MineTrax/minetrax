<script setup>
import Icon from "@/Components/Icon.vue";

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
    <div class="bg-card text-card-foreground p-4 rounded-lg border flex space-x-4 justify-between shadow">
        <div id="left">
            <h3 class="text-card-foreground text-sm font-bold" :class="color">
                {{ title }}
            </h3>
            <p class="text-2xl font-bold text-card-foreground">
                {{ value }}
            </p>
            <p v-if="desc" class="text-xs text-muted-foreground">
                {{ desc }}
            </p>
        </div>
        <div id="right" class="flex justify-center items-center">
            <icon v-if="icon" class="w-16" :class="color" :name="icon" />
        </div>
    </div>
</template>
