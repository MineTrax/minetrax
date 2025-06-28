<template>
  <Card
    v-if="enabled"
    ref="box"
  >
    <CardContent class="p-3 sm:px-5">
      <h3 class="font-extrabold text-foreground dark:text-foreground">
        {{ __("Did You Know?") }}
      </h3>

      <!--Loading-->
      <div
        v-if="loading"
        class="mt-4 space-y-4"
      >
        <div class="w-full max-w-sm mx-auto">
          <div class="flex space-x-4 animate-pulse">
            <div class="w-8 h-8 bg-surface-300 rounded dark:bg-surface-700" />
            <div class="flex-1 py-1 space-y-1">
              <div class="w-3/4 h-4 bg-surface-300 rounded dark:bg-surface-700" />
              <div class="w-5/6 h-4 bg-surface-300 rounded dark:bg-surface-700" />
            </div>
          </div>
        </div>
      </div>

      <!--Data-->
      <div
        v-if="!loading"
        class="flex flex-col mt-4 space-y-2"
      >
        <img
          v-if="imageUrl"
          class="w-full rounded"
          :src="imageUrl"
          alt="Image"
        >
        <div
          class="text-sm text-foreground dark:text-foreground"
          :class="{ 'font-semibold text-center': imageUrl }"
        >
          {{ text }}
        </div>
      </div>
    </CardContent>
  </Card>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useElementVisibility } from '@vueuse/core';
import axios from 'axios';
import {
  Card,
  CardContent,
} from '@/Components/ui/card'

defineProps({
    enabled: {
        type: Boolean,
        required: true,
    },
});

const text = ref(null);
const imageUrl = ref(null);
const loading = ref(true);
const box = ref(null);
const isVisible = useElementVisibility(box);
let interval = null;

onMounted(() => {
    axios
        .get(route('didyouknow.get'))
        .then((data) => {
            text.value = data.data.text;
            imageUrl.value = data.data.image;
        })
        .finally(() => {
            loading.value = false;
        });

    interval = setInterval(() => {
        if (!isVisible.value) {
            return;
        }
        loading.value = true;
        axios
            .get(route('didyouknow.get'))
            .then((data) => {
                text.value = data.data.text;
                imageUrl.value = data.data.image;
            })
            .finally(() => {
                loading.value = false;
            });
    }, 30000);
});

onUnmounted(() => {
    clearInterval(interval);
});
</script>
