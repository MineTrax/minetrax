<template>
  <app-layout>
    <app-head :title="__('News')" />

    <div class="py-4 px-2 md:py-12 md:px-10 max-w-7xl mx-auto">
      <div class="flex justify-between mb-8">
        <h1 class="font-bold text-3xl text-gray-500 dark:text-gray-300">
          {{ __("News") }} <span class="hidden md:inline">{{ __("& Announcements") }}</span>
        </h1>
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
        <div class="flex flex-col space-y-4 -my-2 md:w-9/12 overflow-x-auto md:-mx-6 lg:-mx-8">
          <infinite-scroll :load-more="loadMoreNews">
            <div
              v-for="(news, index) in newsList.data"
              :key="index"
              class=""
            >
              <div class="py-2 align-middle inline-block min-w-full md:px-6 lg:px-8">
                <div
                  class="shadow max-w-none bg-white px-3 py-2 md:px-10 md:py-5 overflow-hidden border-b border-gray-200 rounded md:rounded-lg dark:bg-cool-gray-800 dark:border-none"
                >
                  <span
                    v-if="news.type.value === 0"
                    class="bg-light-blue-400 font-bold inline-flex leading-7 mb-3 px-3 rounded text-sm text-white"
                  >{{
                    news.type.key
                  }}</span>
                  <span
                    v-else-if="news.type.value === 1"
                    class="bg-orange-600 font-bold inline-flex leading-7 mb-3 px-3 rounded text-sm text-white"
                  >{{
                    news.type.key
                  }}</span>
                  <span
                    v-else-if="news.type.value === 2"
                    class="bg-green-600 font-bold inline-flex leading-7 mb-3 px-3 rounded text-sm text-white"
                  >{{
                    news.type.key
                  }}</span>
                  <span
                    v-else
                    class="bg-gray-600 font-bold inline-flex leading-7 mb-3 px-3 rounded text-sm text-white"
                  >{{
                    news.type.key
                  }}</span>

                  <inertia-link
                    as="a"
                    :href="route('news.show', news.slug)"
                    class="block font-bold text-4xl text-gray-900 dark:text-gray-200 mb-5 cursor-pointer hover:underline"
                  >
                    {{
                      news.title
                    }}
                  </inertia-link>
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
                        {{
                          formatToDayDateString(news.published_at)
                        }}
                      </p>
                      <p class="text-gray-500 dark:text-gray-400 text-sm">
                        {{ news.time_to_read }}
                        {{ __("read") }}
                      </p>
                    </div>
                  </div>
                  <div
                    class="prose dark:prose-dark max-w-none"
                    v-html="news.body_html_small"
                  />
                </div>
              </div>
            </div>
          </infinite-scroll>

          <div
            v-if="newsList.data <= 0"
            class="py-2 align-middle inline-block min-w-full md:px-6 lg:px-8"
          >
            <div
              class="shadow text-center dark:text-gray-400 italic max-w-none bg-white px-3 py-2 md:px-10 md:py-5 overflow-hidden border-b border-gray-200 rounded md:rounded-lg dark:bg-cool-gray-800 dark:border-none"
            >
              {{ __("No News or Announcement Yet.") }}
            </div>
          </div>
        </div>

        <div class="hidden md:block md:w-3/12 flex-1 space-y-4 mt-4 md:mt-0">
          <server-status-box />
          <shout-box />
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script>
import InfiniteScroll from '@/Components/InfiniteScroll.vue';
import { useAuthorizable } from '@/Composables/useAuthorizable';
import { useHelpers } from '@/Composables/useHelpers';
import AppLayout from '@/Layouts/AppLayout.vue';
import ServerStatusBox from '@/Shared/ServerStatusBox.vue';
import ShoutBox from '@/Shared/ShoutBox.vue';

export default {

    components: {
        ShoutBox,
        ServerStatusBox,
        AppLayout,
        InfiniteScroll
    },
    props: {
        newses: Object,
    },

    setup() {
        const {can} = useAuthorizable();
        const {formatTimeAgoToNow, formatToDayDateString} = useHelpers();
        return {can, formatTimeAgoToNow, formatToDayDateString};
    },

    data() {
        return {
            newsList: this.newses
        };
    },

    methods: {
        loadMoreNews() {
            if (!this.newsList.next_page_url) {
                return Promise.resolve();
            }

            return axios(this.newsList.next_page_url).then(response => {
                this.newsList = {
                    ...response.data,
                    data: [...this.newsList.data, ...response.data.data]
                };
            });
        }
    },
};
</script>
