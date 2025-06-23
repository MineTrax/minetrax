<script setup>
import { useHelpers } from '@/Composables/useHelpers';
import Comments from '@/Components/Comments.vue';
const {formatTimeAgoToNow, formatToDayDateString} = useHelpers();

defineProps({
    news: {
        type: Object,
        required: true,
    },
});
</script>

<template>
  <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
    <div class="px-3 py-2 overflow-hidden bg-white border-b border-foreground rounded shadow max-w-none md:px-10 md:py-5 md:rounded-lg dark:bg-surface-800 dark:border-none">
      <span
        v-if="news.type.value === 0"
        class="inline-flex px-3 mb-3 text-sm font-bold leading-7 text-white rounded bg-primary"
      >{{ news.type.key }}</span>
      <span
        v-else-if="news.type.value === 1"
        class="inline-flex px-3 mb-3 text-sm font-bold leading-7 text-white bg-orange-600 rounded"
      >{{ news.type.key }}</span>
      <span
        v-else-if="news.type.value === 2"
        class="inline-flex px-3 mb-3 text-sm font-bold leading-7 text-white bg-success-600 rounded"
      >{{ news.type.key }}</span>
      <span
        v-else
        class="inline-flex px-3 mb-3 text-sm font-bold leading-7 text-white bg-surface-600 rounded"
      >{{ news.type.key }}</span>

      <img
        v-if="news.photo_url"
        class="w-full mb-5 rounded"
        :src="news.photo_url"
        :alt="news.title"
      >

      <h1 class="mb-5 text-2xl font-bold text-foreground md:text-4xl dark:text-foreground">
        {{ news.title }}
      </h1>

      <div class="flex w-full mb-5 md:w-auto">
        <img
          :src="news.creator.profile_photo_url"
          alt="Profile"
          class="w-12 h-12 mr-3 rounded-full"
        >
        <div>
          <inertia-link
            as="p"
            :href="route('user.public.get', news.creator.username)"
            class="font-bold text-foreground cursor-pointer hover:underline dark:text-foreground"
            :style="[news.creator.roles[0].color ? {color: news.creator.roles[0].color} : null]"
          >
            {{ news.creator.name }}
          </inertia-link>
          <p
            v-tippy
            :title="formatTimeAgoToNow(news.created_at)"
            class="text-sm text-foreground dark:text-foreground focus:outline-none"
          >
            {{ formatToDayDateString(news.published_at) }}
          </p>
          <p class="text-sm text-foreground dark:text-foreground">
            {{ news.time_to_read }} {{ __("read") }}
          </p>
        </div>
      </div>
      <div
        class="prose dark:prose-invert max-w-none"
        v-html="news.body_html"
      />
    </div>

    <div
      v-if="news.is_commentable"
      class="px-3 py-2 mt-2 overflow-hidden bg-white border-b border-foreground rounded shadow max-w-none md:px-10 md:py-5 md:rounded-lg dark:bg-surface-800 dark:border-none"
    >
      <Comments
        :commentable="news"
        commentable-type="news"
      />
    </div>
  </div>
</template>
