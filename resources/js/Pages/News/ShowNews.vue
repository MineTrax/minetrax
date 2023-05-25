<template>
  <app-layout>
    <app-head :title="__(':title - News', {title: news.title})" />

    <div class="py-4 px-2 md:py-12 md:px-10 max-w-7xl mx-auto">
      <div class="flex justify-end mb-8">
        <div class="flex">
          <inertia-link
            :href="route('home')"
            class="inline-flex items-center px-4 py-2 bg-gray-400 dark:bg-cool-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 active:bg-gray-600 focus:outline-none focus:border-gray-500 focus:shadow-outline-gray transition ease-in-out duration-150"
          >
            <span>{{ __("Homepage") }}</span>
          </inertia-link>
        </div>
      </div>
      <div class="flex flex-col md:flex-row md:space-x-4">
        <div class="-my-2 md:w-9/12 overflow-x-auto md:-mx-6 lg:-mx-8">
          <div class="py-2 align-middle inline-block min-w-full md:px-6 lg:px-8">
            <div class="shadow max-w-none bg-white px-3 py-2 md:px-10 md:py-5 overflow-hidden border-b border-gray-200 rounded md:rounded-lg dark:bg-cool-gray-800 dark:border-none">
              <span
                v-if="news.type.value === 0"
                class="bg-light-blue-400 font-bold inline-flex leading-7 mb-3 px-3 rounded text-sm text-white"
              >{{ news.type.key }}</span>
              <span
                v-else-if="news.type.value === 1"
                class="bg-orange-600 font-bold inline-flex leading-7 mb-3 px-3 rounded text-sm text-white"
              >{{ news.type.key }}</span>
              <span
                v-else-if="news.type.value === 2"
                class="bg-green-600 font-bold inline-flex leading-7 mb-3 px-3 rounded text-sm text-white"
              >{{ news.type.key }}</span>
              <span
                v-else
                class="bg-gray-600 font-bold inline-flex leading-7 mb-3 px-3 rounded text-sm text-white"
              >{{ news.type.key }}</span>

              <h1 class="font-bold text-4xl text-gray-900 dark:text-gray-200 mb-5">
                {{ news.title }}
              </h1>
              <img
                v-if="news.photo_url"
                class="float-right w-full md:w-1/2 ml-10 mb-5 md:mb-0"
                :src="news.photo_url"
                alt="News Image"
              >
              <div class="flex w-full md:w-auto mb-5">
                <img
                  :src="news.creator.profile_photo_url"
                  alt="Profile"
                  class="h-12 w-12 mr-3 rounded-full"
                >
                <div>
                  <inertia-link
                    as="p"
                    :href="route('user.public.get', news.creator.username)"
                    class="cursor-pointer hover:underline font-bold text-gray-700 dark:text-gray-300"
                    :style="[news.creator.roles[0].color ? {color: news.creator.roles[0].color} : null]"
                  >
                    {{ news.creator.name }}
                  </inertia-link>
                  <p
                    v-tippy
                    :title="formatTimeAgoToNow(news.created_at)"
                    class="text-gray-500 dark:text-gray-400 text-sm focus:outline-none"
                  >
                    {{ formatToDayDateString(news.published_at) }}
                  </p>
                  <p class="text-gray-500 dark:text-gray-400 text-sm">
                    {{ news.time_to_read }} {{ __("read") }}
                  </p>
                </div>
              </div>
              <div
                class="prose dark:prose-dark max-w-none"
                v-html="news.body_html"
              />
            </div>
          </div>
        </div>

        <div class="md:w-3/12 flex-1 space-y-4 mt-4 md:mt-0">
          <server-status-box />
          <news-box :newslist="newslist" />
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script>
import { useHelpers } from '@/Composables/useHelpers';
import AppLayout from '@/Layouts/AppLayout.vue';
import NewsBox from '@/Shared/NewsBox.vue';
import ServerStatusBox from '@/Shared/ServerStatusBox.vue';

export default {

    components: {
        ServerStatusBox,
        NewsBox,
        AppLayout,
    },
    props: {
        news: Object,
        newslist: Array
    },
    setup() {
        const {formatTimeAgoToNow, formatToDayDateString} = useHelpers();
        return {formatTimeAgoToNow, formatToDayDateString};
    },
};
</script>
