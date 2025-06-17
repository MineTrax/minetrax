<script setup>
import BaseLayout from './BaseLayout.vue';

const props = defineProps({
    hideFooter: {
        type: Boolean,
        default: false,
    }
})
</script>

<template>
  <BaseLayout :hide-footer="hideFooter">
    <slot />
  </BaseLayout>
</template>
