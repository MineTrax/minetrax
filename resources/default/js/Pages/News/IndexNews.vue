<template>
  <app-layout>
    <app-head :title="__('News')" />

    <div class="py-4 px-2 md:py-12 md:px-10 max-w-screen-2xl mx-auto">

      <div class="flex flex-col md:flex-row md:space-x-4">
        <div class="flex flex-col space-y-6 md:w-9/12">
          <infinite-scroll :load-more="loadMoreNews" :can-load-more="newsList.next_page_url !== null">
            <div
              v-for="(news, index) in newsList.data"
              :key="index"
              class="space-y-6"
            >
              <Card class="group relative overflow-hidden transition-shadow duration-300 hover:shadow-lg">
                <CardContent class="p-6 sm:p-8">
                  <!-- Image section -->
                  <div
                    v-if="news.photo_url"
                    class="relative mb-6 -mx-6 sm:-mx-8 -mt-6 sm:-mt-8 overflow-hidden"
                  >
                    <div class="aspect-video relative">
                      <img
                        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                        :src="news.photo_url"
                        alt="News Image"
                      >
                      <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    </div>
                  </div>

                  <!-- Title section -->
                  <div class="mb-4">
                    <Link
                      :href="route('news.show', news.slug)"
                      class="group/title"
                      prefetch
                    >
                      <h2 class="text-2xl sm:text-3xl font-bold leading-tight text-card-foreground group-hover/title:text-primary transition-colors duration-200 mb-2">
                        {{ news.title }}
                      </h2>
                    </Link>
                  </div>

                  <!-- Metadata section -->
                  <div class="flex items-center justify-between mb-4 pb-4 border-b border-border/30">
                    <div class="flex items-center space-x-3">
                      <span
                        class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-full"
                        :class="{
                          'bg-primary/10 text-primary border border-primary/20': news.type.value === 0,
                          'bg-orange-100 text-orange-700 border border-orange-200 dark:bg-orange-900/20 dark:text-orange-400 dark:border-orange-800': news.type.value === 1,
                          'bg-green-100 text-green-700 border border-green-200 dark:bg-green-900/20 dark:text-green-400 dark:border-green-800': news.type.value === 2
                        }"
                      >
                        {{ news.type.key }}
                      </span>
                    </div>

                    <!-- Author and time metadata -->
                    <div class="flex items-center space-x-4">
                      <div class="flex items-center space-x-2">
                        <img
                          :src="news.creator.profile_photo_url"
                          alt="Profile"
                          class="h-8 w-8 rounded-full border border-border/20"
                        >
                        <div class="text-sm">
                          <Link
                            :href="route('user.public.get', news.creator.username)"
                            class="font-medium text-card-foreground hover:text-primary transition-colors cursor-pointer"
                            :style="[news.creator.roles[0].color ? {color: news.creator.roles[0].color} : null]"
                            prefetch
                          >
                            {{ news.creator.name }}
                          </Link>
                        </div>
                      </div>
                      <div class="text-sm text-muted-foreground">
                        <span
                          v-tippy
                          :content="formatTimeAgoToNow(news.created_at)"
                          class="cursor-help"
                        >
                          {{ formatToDayDateString(news.published_at) }}
                        </span>
                      </div>
                    </div>
                  </div>

                  <!-- Content section -->
                  <div
                    class="prose prose-sm sm:prose-base max-w-none text-card-foreground/80 prose-headings:text-card-foreground prose-p:text-card-foreground/80 prose-strong:text-card-foreground prose-a:text-primary prose-a:no-underline hover:prose-a:underline prose-blockquote:border-primary/30 prose-blockquote:text-card-foreground/70 prose-code:text-primary prose-code:bg-muted prose-code:rounded prose-code:px-1 prose-pre:bg-muted"
                    v-html="news.body_html_small"
                  />

                  <!-- Read more indicator -->
                  <div class="mt-6 pt-4 border-t border-border/50">
                    <Link
                      :href="route('news.show', news.slug)"
                      class="inline-flex items-center space-x-2 text-sm font-medium text-primary hover:text-primary/90 transition-colors group/link"
                      prefetch
                    >
                      <span>{{ __("Read full article") }}</span>
                      <icon name="arrow-right" class="w-4 h-4 transition-transform group-hover/link:translate-x-1" />
                  </Link>
                  </div>
                </CardContent>
              </Card>
            </div>
          </infinite-scroll>

          <div
            v-if="newsList.data <= 0"
            class="py-2 align-middle inline-block min-w-full"
          >
            <Card class="text-center">
              <CardContent class="p-8">
                <div class="text-muted-foreground italic">
                  {{ __("No News or Announcement Yet.") }}
                </div>
              </CardContent>
            </Card>
          </div>
        </div>

        <div class="hidden md:flex flex-col md:w-3/12 flex-none space-y-4 h-screen sticky" :class="{'top-16': isStickyNav, 'top-5': !isStickyNav}">
          <server-status-box />
          <shout-box />
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script setup>
import { ref } from 'vue';
import InfiniteScroll from '@/Components/InfiniteScroll.vue';
import Icon from '@/Components/Icon.vue';
import { useAuthorizable } from '@/Composables/useAuthorizable';
import { useHelpers } from '@/Composables/useHelpers';
import AppLayout from '@/Layouts/AppLayout.vue';
import ServerStatusBox from '@/Shared/ServerStatusBox.vue';
import ShoutBox from '@/Shared/ShoutBox.vue';
import {
  Card,
  CardContent,
} from '@/Components/ui/card'
import { Link } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    newses: Object,
});

const page = usePage();
const { can } = useAuthorizable();
const { formatTimeAgoToNow, formatToDayDateString } = useHelpers();

const isStickyNav = page.props.generalSettings.enable_sticky_header_menu;

const newsList = ref(props.newses);

const loadMoreNews = () => {
    if (!newsList.value.next_page_url) {
        return Promise.resolve();
    }

    return axios(newsList.value.next_page_url).then(response => {
        newsList.value = {
            ...response.data,
            data: [...newsList.value.data, ...response.data.data]
        };
    });
};
</script>
