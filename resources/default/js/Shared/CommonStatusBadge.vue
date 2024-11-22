<script setup>
import Badge from '@/Components/Badge.vue';
import { computed } from 'vue';
import { startCase } from 'lodash';

const props = defineProps({
    status: {
        type: String,
        required: true
    },
});

const colorClass = computed(() => {
    switch (props.status) {
    case 'pending':
        return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 border border-yellow-300 dark:border-yellow-700';
    case 'inprogress':
    case 'running':
        return 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900 dark:text-cyan-200 border border-cyan-300 dark:border-cyan-700';
    case 'approved':
    case 'completed':
        return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 border border-green-300 dark:border-green-700';
    case 'rejected':
    case 'failed':
    case 'ban':
    case 'active':
    case 'permanent':
        return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 border border-red-300 dark:border-red-700';
    case 'onhold':
    case 'deferred':
        return 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200 border border-amber-300 dark:border-amber-700';
    case 'warn':
        return 'bg-yellow-50 text-yellow-800 dark:bg-yellow-900 dark:bg-opacity-25 dark:text-yellow-200 border border-yellow-300 dark:border-yellow-700';
    case 'kick':
        return 'bg-gray-50 text-gray-800 dark:bg-gray-900 dark:bg-opacity-25 dark:text-gray-200 border border-gray-300 dark:border-gray-700';
    default:
        return 'bg-gray-100 text-gray-600 dark:bg-gray-900 dark:text-gray-300 border border-gray-300 dark:border-gray-700';
    }
});

</script>

<template>
  <Badge
    :color-class="colorClass"
  >
    {{ startCase(status) }}
  </Badge>
</template>
