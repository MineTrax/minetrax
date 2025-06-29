<template>
  <app-layout>
    <app-head :title="__(':title - News', {title: news.title})" />

    <div class="py-4 px-2 md:py-12 md:px-10 max-w-screen-2xl mx-auto">
      <div class="flex flex-col md:flex-row md:space-x-4">
        <div class="md:w-9/12 flex-1">
          <ShowNewsCard :news="news" />
        </div>

        <div class="hidden md:flex flex-col md:w-3/12 flex-none space-y-4 h-screen sticky" :class="{'top-16': isStickyNav, 'top-5': !isStickyNav}">
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
import { usePage } from '@inertiajs/vue3';

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
        const page = usePage();
        const isStickyNav = page.props.generalSettings.enable_sticky_header_menu;

        return {formatTimeAgoToNow, formatToDayDateString, isStickyNav};
    },
};
</script>
