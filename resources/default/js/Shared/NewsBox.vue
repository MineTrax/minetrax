<template>
  <Card
    v-if="newslist.length > 0"
    class="overflow-hidden"
  >
    <CardContent class="p-0">
      <div class="p-4 sm:px-5 pb-0">
        <h3 class="font-extrabold text-card-foreground">
          {{ __("Latest News") }}
        </h3>
      </div>

      <div class="divide-y divide-border mt-3">
        <Link
          v-for="news in newslist"
          :key="news.id"
          :href="route('news.show', news.slug)"
          class="group flex items-start gap-3 px-4 sm:px-5 py-3 transition-colors hover:bg-muted/50"
          prefetch
        >
          <!-- Thumbnail -->
          <div
            v-if="news.photo_url"
            class="shrink-0 overflow-hidden rounded-md border border-border"
          >
            <img
              class="h-14 w-18 object-cover transition-transform duration-300 group-hover:scale-105"
              :src="news.photo_url"
              :alt="news.title"
            >
          </div>

          <div class="flex-1 min-w-0">
            <!-- Title -->
            <h4 class="text-sm font-semibold text-card-foreground leading-snug line-clamp-2 group-hover:text-primary transition-colors">
              {{ news.title }}
            </h4>

            <!-- Meta -->
            <div class="mt-1.5 flex items-center gap-2 text-xs text-muted-foreground">
              <span
                v-tippy
                class="cursor-help"
                :title="formatToDayDateString(news.published_at)"
              >
                {{ formatTimeAgoToNow(news.published_at) }}
              </span>
              <span class="w-0.5 h-0.5 rounded-full bg-muted-foreground/50" />
              <span
                class="inline-flex items-center px-1.5 py-px text-[0.65rem] font-medium rounded-full"
                :class="{
                  'bg-primary/10 text-primary border border-primary/20': news.type.value === 0,
                  'bg-orange-100 text-orange-700 border border-orange-200 dark:bg-orange-900/20 dark:text-orange-400 dark:border-orange-800': news.type.value === 1,
                  'bg-success/10 text-success border border-success/20 dark:bg-success/20': news.type.value === 2,
                }"
              >
                {{ news.type.key }}
              </span>
            </div>
          </div>

          <!-- Arrow on hover -->
          <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 16 16"
            fill="currentColor"
            class="mt-1 h-3.5 w-3.5 shrink-0 text-muted-foreground opacity-0 transition-opacity group-hover:opacity-100"
          >
            <path
              fill-rule="evenodd"
              d="M6.22 4.22a.75.75 0 0 1 1.06 0l3.25 3.25a.75.75 0 0 1 0 1.06l-3.25 3.25a.75.75 0 0 1-1.06-1.06L8.94 8 6.22 5.28a.75.75 0 0 1 0-1.06Z"
              clip-rule="evenodd"
            />
          </svg>
        </Link>
      </div>
    </CardContent>
  </Card>
</template>

<script setup>
import { useHelpers } from "@/Composables/useHelpers";
import {
    Card,
    CardContent,
} from "@/Components/ui/card";
import { Link } from "@inertiajs/vue3";

defineProps({
    newslist: Array,
});

const { formatTimeAgoToNow, formatToDayDateString } = useHelpers();
</script>
