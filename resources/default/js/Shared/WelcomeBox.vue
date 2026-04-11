<script setup>
import {
    Card,
    CardContent,
} from "@/Components/ui/card";

defineProps({
    htmlData: {
        type: [String, null],
        default: null,
    },
});
</script>

<template>
  <Card v-if="htmlData">
    <CardContent class="px-4 py-3">
      <div
        class="prose dark:prose-invert max-w-none text-center"
        v-html="htmlData"
      />
    </CardContent>
  </Card>
</template>
