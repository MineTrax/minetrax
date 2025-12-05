<script setup>
import { Card, CardContent } from '@/Components/ui/card';

defineProps({
    title: {
        type: String,
    },
    data: {
        type: Object,
    }
});

</script>

<template>
  <Card class="w-full h-[405px] overflow-y-auto">
    <CardContent class="p-4 space-y-2">
      <h3 class="font-extrabold text-foreground flex items-center">
        {{ title }}
      </h3>
      <div>
        <ul class="space-y-2">
          <li
            v-for="(value, key) in data.filter(x => x != null)"
            :key="key"
            class="bg-muted p-2 rounded text-foreground"
          >
            <p class="whitespace-pre-line">
              {{ value }}
            </p>
          </li>
        </ul>
      </div>
    </CardContent>
  </Card>
</template>
