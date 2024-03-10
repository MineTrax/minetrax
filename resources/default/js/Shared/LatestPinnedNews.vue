<template>
  <div
    v-if="newslist && newslist.length > 0"
    class="space-y-4"
  >
    <div
      v-for="news in newslist"
      :key="news.id"
      class="flex justify-between bg-white rounded shadow dark:bg-cool-gray-800"
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
              class="text-sm font-semibold tracking-wider uppercase"
              :class="{ 'text-light-blue-400': news.type.value === 0, 'text-orange-500': news.type.value === 1, 'text-green-500': news.type.value === 2 }"
            >&nbsp;{{ news.type.key }}&nbsp;</span>
            <span
              v-tippy
              :content="formatToDayDateString(news.published_at)"
              class="text-sm font-light leading-snug text-gray-500 focus:outline-none dark:text-gray-400"
            >{{
              formatTimeAgoToNow(news.published_at)
            }}
            </span>
            <span class="text-sm font-light leading-snug text-gray-500 dark:text-gray-400"> - {{ news.time_to_read }} read</span>
          </p>

          <img
            v-if="news.photo_url"
            class="object-cover object-center h-auto mt-2 mb-2 rounded md:mb-4"
            :src="news.photo_url"
            alt="News Image"
          >

          <inertia-link
            :href="route('news.show', news.slug)"
            class="duration-200 hover:text-light-blue-400 dark:text-gray-200 dark:hover:text-light-blue-400"
          >
            {{ news.title }}
          </inertia-link>
        </div>
        <div
          class="mt-3 text-gray-500 dark:text-gray-400"
          v-html="news.body_md"
        />
      </div>
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
