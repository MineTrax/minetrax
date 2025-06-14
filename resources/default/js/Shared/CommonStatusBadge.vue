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
        return 'bg-warning-100 text-warning-800 dark:bg-warning-900 dark:text-warning-200 border border-warning-300 dark:border-warning-700';
    case 'inprogress':
    case 'running':
        return 'bg-info-100 text-info-800 dark:bg-info-900 dark:text-info-200 border border-info-300 dark:border-info-700';
    case 'approved':
    case 'completed':
        return 'bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-200 border border-success-300 dark:border-success-700';
    case 'rejected':
    case 'failed':
    case 'ban':
    case 'active':
    case 'permanent':
        return 'bg-error-100 text-error-800 dark:bg-error-900 dark:text-error-200 border border-error-300 dark:border-error-700';
    case 'onhold':
    case 'deferred':
        return 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200 border border-amber-300 dark:border-amber-700';
    case 'warn':
        return 'bg-warning-50 text-warning-800 dark:bg-warning-900 dark:bg-opacity-25 dark:text-warning-200 border border-warning-300 dark:border-warning-700';
    case 'kick':
        return 'bg-surface-50 text-secondary-800 dark:bg-surface-900 dark:bg-opacity-25 dark:text-secondary-200 border border-secondary-300 dark:border-secondary-700';
    default:
        return 'bg-surface-100 text-secondary-600 dark:bg-surface-900 dark:text-secondary-300 border border-secondary-300 dark:border-secondary-700';
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
