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
          <ShowNewsCard :news="news" />
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
import ShowNewsCard from '@/Shared/ShowNewsCard.vue';

export default {

    components: {
        ServerStatusBox,
        NewsBox,
        AppLayout,
        ShowNewsCard
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
