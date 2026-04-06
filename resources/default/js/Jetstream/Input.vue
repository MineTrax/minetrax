<script setup>
import { onMounted, ref } from"vue";

defineProps({
    modelValue: String,
});

defineEmits(["update:modelValue"]);

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute("autofocus")) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
  <input
    ref="input"
    class="border-foreground focus:border-primary focus:ring-3 focus:ring-primary/50 rounded-md shadow-sm dark:bg-background dark:border-foreground"
    :value="modelValue"
    @input="$emit('update:modelValue', $event.target.value)"
  >
</template>
