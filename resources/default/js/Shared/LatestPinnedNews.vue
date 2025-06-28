<template>
  <div
    v-if="newslist && newslist.length > 0"
    class="space-y-6"
  >
    <Card
      v-for="news in newslist"
      :key="news.id"
      class="group relative overflow-hidden transition-shadow duration-300 hover:shadow-lg"
    >
      <!-- Pinned indicator -->
      <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-primary/60 via-primary to-primary/60"></div>

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
          <inertia-link
            :href="route('news.show', news.slug)"
            class="group/title"
          >
            <h2 class="text-2xl sm:text-3xl font-bold leading-tight text-card-foreground group-hover/title:text-primary transition-colors duration-200 mb-2">
              {{ news.title }}
            </h2>
          </inertia-link>
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

          <!-- Time metadata -->
          <div class="flex items-center space-x-4 text-sm text-card-foreground/70">
            <span
              v-tippy
              :content="formatTimeAgoToNow(news.published_at)"
              class="cursor-help text-muted-foreground"
            >
              {{ formatToDayDateString(news.published_at) }}
            </span>
          </div>
        </div>

        <!-- Content section -->
        <div
          class="prose prose-sm sm:prose-base max-w-none text-card-foreground/80 prose-headings:text-card-foreground prose-p:text-card-foreground/80 prose-strong:text-card-foreground prose-a:text-primary prose-a:no-underline hover:prose-a:underline prose-blockquote:border-primary/30 prose-blockquote:text-card-foreground/70 prose-code:text-primary prose-code:bg-muted prose-code:rounded prose-code:px-1 prose-pre:bg-muted"
          v-html="news.body_md"
        />

        <!-- Read more indicator -->
        <div class="mt-6 pt-4 border-t border-border/50">
          <inertia-link
            :href="route('news.show', news.slug)"
            class="inline-flex items-center space-x-2 text-sm font-medium text-primary hover:text-primary/90 transition-colors group/link"
          >
            <span>{{ __("Read full article") }}</span>
            <icon name="arrow-right" class="w-4 h-4 transition-transform group-hover/link:translate-x-1" />
          </inertia-link>
        </div>
      </CardContent>
    </Card>
  </div>
</template>

<script>
import Icon from '@/Components/Icon.vue';
import { useHelpers } from '@/Composables/useHelpers';
import {
  Card,
  CardContent,
} from '@/Components/ui/card'

export default {
    components: {
        Icon,
        Card,
        CardContent,
    },
    props: {
        newslist: Array
    },
    setup() {
        const {formatTimeAgoToNow,formatToDayDateString} = useHelpers();
        return {formatTimeAgoToNow,formatToDayDateString};
    },
};
</script>
