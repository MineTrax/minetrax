<script setup>
import { useHelpers } from '@/Composables/useHelpers';
import Comments from '@/Components/Comments.vue';
import {
  Card,
  CardContent,
} from '@/Components/ui/card'

const {formatTimeAgoToNow, formatToDayDateString} = useHelpers();

defineProps({
    news: {
        type: Object,
        required: true,
    },
});
</script>

<template>
  <div class="space-y-6">
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
              :alt="news.title"
            >
            <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
          </div>
        </div>

        <!-- Type badge -->
        <div class="mb-4">
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

        <!-- Title section -->
        <div class="mb-6">
          <h1 class="text-3xl sm:text-4xl font-bold leading-tight text-card-foreground mb-2">
            {{ news.title }}
          </h1>
        </div>

        <!-- Metadata section -->
        <div class="flex items-center justify-between mb-6 pb-6 border-b border-border/30">
          <div class="flex items-center space-x-3">
            <img
              :src="news.creator.profile_photo_url"
              alt="Profile"
              class="h-12 w-12 rounded-full border-2 border-border/20"
            >
            <div>
              <inertia-link
                :href="route('user.public.get', news.creator.username)"
                class="font-semibold text-card-foreground hover:text-primary transition-colors cursor-pointer block"
                :style="[news.creator.roles[0].color ? {color: news.creator.roles[0].color} : null]"
              >
                {{ news.creator.name }}
              </inertia-link>
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
        </div>

        <!-- Content section -->
        <div
          class="prose max-w-none text-card-foreground/90 prose-headings:text-card-foreground prose-p:text-card-foreground/90 prose-strong:text-card-foreground prose-a:text-primary prose-a:no-underline hover:prose-a:underline prose-blockquote:border-primary/30 prose-blockquote:text-card-foreground/70 prose-code:text-primary prose-code:bg-muted prose-code:rounded prose-code:px-1 prose-pre:bg-muted prose-img:rounded-lg"
          v-html="news.body_html"
        />
      </CardContent>
    </Card>

    <!-- Comments section -->
    <Card
      v-if="news.is_commentable"
      class="transition-shadow duration-300 hover:shadow-md"
    >
      <CardContent class="p-6 sm:p-8">
        <div class="mb-4">
          <h3 class="text-xl font-semibold text-card-foreground">
            {{ __("Comments") }}
          </h3>
        </div>
        <Comments
          :commentable="news"
          commentable-type="news"
        />
      </CardContent>
    </Card>
  </div>
</template>
