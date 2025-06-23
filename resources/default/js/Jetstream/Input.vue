<script setup>
import { onMounted, ref } from 'vue';

defineProps({
    modelValue: String,
});

defineEmits(['update:modelValue']);

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
  <input
    ref="input"
    class="border-foreground focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 rounded-md shadow-sm dark:bg-surface-900 dark:border-foreground"
    :value="modelValue"
    @input="$emit('update:modelValue', $event.target.value)"
  >
</template>
