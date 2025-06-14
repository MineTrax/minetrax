<script setup>

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
  <div class="bg-white dark:bg-surface-800 rounded w-full h-[405px] space-y-2 p-3 shadow overflow-y-auto">
    <h3 class="font-extrabold text-secondary-800 dark:text-secondary-200 flex items-center">
      {{ title }}
    </h3>
    <div>
      <ul class="space-y-2">
        <li
          v-for="(value, key) in data.filter(x => x != null)"
          :key="key"
          class="bg-surface-100 p-2 rounded dark:bg-surface-900 dark:text-secondary-300"
        >
          <p class="whitespace-pre-line">
            {{ value }}
          </p>
        </li>
      </ul>
    </div>
  </div>
</template>
