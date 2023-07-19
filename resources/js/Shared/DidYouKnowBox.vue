<template>
  <div
    v-if="enabled"
    ref="box"
  >
    <div class="p-3 bg-white rounded shadow sm:px-5 dark:bg-cool-gray-800">
      <h3 class="font-extrabold text-gray-800 dark:text-gray-200">
        {{ __("Did You Know?") }}
      </h3>

      <!--Loading-->
      <div
        v-if="loading"
        class="mt-4 space-y-4"
      >
        <div class="w-full max-w-sm mx-auto">
          <div class="flex space-x-4 animate-pulse">
            <div class="w-8 h-8 bg-gray-300 rounded dark:bg-cool-gray-700" />
            <div class="flex-1 py-1 space-y-1">
              <div class="w-3/4 h-4 bg-gray-300 rounded dark:bg-cool-gray-700" />
              <div class="w-5/6 h-4 bg-gray-300 rounded dark:bg-cool-gray-700" />
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
          class="text-sm text-gray-600 dark:text-gray-300"
          :class="{ 'font-semibold text-center': imageUrl }"
        >
          {{ text }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

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
let interval = null;

const isInViewport = () => {
    const rect = box.value?.getBoundingClientRect();
    if (!rect) return false;

    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <=
        (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <=
        (window.innerWidth || document.documentElement.clientWidth)
    );
};

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
        if (!isInViewport()) {
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
