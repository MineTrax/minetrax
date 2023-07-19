<template>
  <div
    v-if="newslist && newslist.length > 0"
    class="space-y-4"
  >
    <div
      v-for="news in newslist"
      :key="news.id"
      class="bg-white dark:bg-cool-gray-800 rounded shadow flex justify-between"
    >
      <div class="p-3 sm:px-5">
        <div class="text-xl">
          <p class="mb-1">
            <icon
              v-tippy
              :title="__('Pinned News')"
              class="inline text-gray-400 focus:outline-none"
              name="paper-clip"
            />
            <span
              class="font-semibold uppercase text-sm tracking-wider"
              :class="{ 'text-light-blue-400': news.type.value === 0, 'text-orange-500': news.type.value === 1, 'text-green-500': news.type.value === 2 }"
            >&nbsp;{{ news.type.key }}&nbsp;</span>
            <span
              v-tippy
              :content="formatToDayDateString(news.published_at)"
              class="focus:outline-none text-sm text-gray-500 dark:text-gray-400 font-light leading-snug"
            >{{
              formatTimeAgoToNow(news.published_at)
            }}
            </span>
            <span class="text-sm text-gray-500 dark:text-gray-400 font-light leading-snug"> - {{ news.time_to_read }} read</span>
          </p>
          <inertia-link
            :href="route('news.show', news.slug)"
            class="hover:text-light-blue-400 dark:text-gray-200 dark:hover:text-light-blue-400 duration-200"
          >
            {{ news.title }}
          </inertia-link>
        </div>
        <div
          class="mt-3 text-gray-500 dark:text-gray-400"
          v-html="news.body_md"
        />
      </div>
      <img
        v-if="news.photo_url"
        class="h-auto w-2/5 object-cover object-center"
        :src="news.photo_url"
        alt="News Image"
      >
    </div>
  </div>
</template>

<script>
import Icon from '@/Components/Icon.vue';
import { useHelpers } from '@/Composables/useHelpers';

export default {
    components: {Icon},
    props: {
        newslist: Array
    },
    setup() {
        const {formatTimeAgoToNow,formatToDayDateString} = useHelpers();
        return {formatTimeAgoToNow,formatToDayDateString};
    },
};
</script>
