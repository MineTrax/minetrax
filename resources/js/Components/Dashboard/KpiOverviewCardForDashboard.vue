<script setup>
import millify from 'millify';
import KpiOverviewCard from '@/Components/Dashboard/KpiOverviewCard.vue';

defineProps({
    icon: {
        type: [Object, Function],
        required: true,
    },
    iconClass: {
        type: [Array, String],
        required: false,
        default: 'text-gray-500 bg-gray-100 dark:bg-gray-500 dark:text-white'
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
    description: {
        type: [String, Number],
        required: false,
        default: null,
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
    changeDesc: {
        type: [String, Number],
        required: false,
        default: null,
    },
});

</script>

<template>
  <KpiOverviewCard
    class="flex-1"
    :title="title"
    :value="millify(value)"
    :sub-value="`(+${subValue})`"
    :sub-value-class="[
      subValue > 0 ? 'text-green-500' : 'text-gray-400',
    ]"
    :change="`+${millify(change, {precision: 2})}%`"
    :change-class="[
      change > 0 ? 'text-green-500 bg-green-100' : 'text-gray-400 bg-gray-100',
    ]"
    :change-desc="changeDesc"
    :icon="icon"
    :icon-class="iconClass"
    :description="description"
  />
</template>
