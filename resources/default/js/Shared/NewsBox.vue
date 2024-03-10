<template>
  <div v-if="newslist.length > 0">
    <div class="p-3 bg-white dark:bg-cool-gray-800 rounded shadow space-y-4">
      <h3 class="font-extrabold text-gray-800 dark:text-gray-200">
        {{ __("Latest News") }}
      </h3>

      <div
        v-for="news in newslist"
        :key="news.id"
        class="flex justify-between space-y-4"
      >
        <div class="flex-co space-y-1">
          <span
            v-if="news.type.value === 0"
            class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-lg bg-light-blue-100 text-light-blue-800 dark:bg-light-blue-700 dark:bg-opacity-25 dark:text-light-blue-400"
          >{{ news.type.key }}</span>
          <span
            v-else-if="news.type.value === 1"
            class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-lg bg-orange-100 text-orange-800 dark:bg-orange-700 dark:bg-opacity-25 dark:text-orange-400"
          >{{ news.type.key }}</span>
          <span
            v-else-if="news.type.value === 2"
            class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-lg bg-green-100 text-green-800 dark:bg-green-700 dark:bg-opacity-25 dark:text-green-400"
          >{{ news.type.key }}</span>
          <span
            v-else
            class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-lg bg-gray-100 text-gray-800 dark:bg-gray-700 dark:bg-opacity-25 dark:text-gray-400"
          >{{ news.type.key }}</span>

          <p class="font-semibold leading-5 text-gray-800 dark:text-gray-300">
            <inertia-link
              :href="route('news.show', news.slug)"
              class="hover:text-light-blue-400 duration-200"
            >
              <span>{{ news.title }}</span>
            </inertia-link>
          </p>
          <span
            v-tippy
            class="text-gray-500 dark:text-gray-400 text-xs focus:outline-none"
            :title="formatToDayDateString(news.published_at)"
          >{{ formatTimeAgoToNow(news.published_at) }}</span>
          <span class="text-gray-500 dark:text-gray-400 text-xs"> - {{ news.time_to_read }}&nbsp;{{ __("read") }}</span>
        </div>
        <img
          v-if="news.photo_url"
          class="h-16 w-20 rounded"
          :src="news.photo_url"
          alt="News Image"
        >
      </div>
    </div>
  </div>
</template>

<script>
import { useHelpers } from '@/Composables/useHelpers';


export default {
    props: {
        newslist: Array
    },
    setup() {
        const {formatTimeAgoToNow,formatToDayDateString} = useHelpers();
        return {formatTimeAgoToNow,formatToDayDateString};
    },
};
</script>
