<template>
  <Card v-if="newslist.length > 0" class="overflow-hidden">
    <CardContent class="p-0">
      <div class="p-3">
        <h3 class="font-extrabold text-card-foreground">
          {{ __("Latest News") }}
        </h3>
      </div>

      <div>
        <div
          v-for="news in newslist"
          :key="news.id"
          class="px-3 py-2 hover:bg-muted/50 transition-colors duration-200"
        >
          <div class="flex items-start justify-between gap-4">
            <div class="flex-1 min-w-0">
                                          <!-- News Title -->
              <h4 class="font-semibold text-foreground mb-2 leading-tight">
                <inertia-link
                  :href="route('news.show', news.slug)"
                  class="hover:text-primary transition-colors duration-200 line-clamp-2"
                >
                  {{ news.title }}
                </inertia-link>
              </h4>

              <!-- News Meta Information -->
              <div class="flex items-center gap-3 text-xs text-muted-foreground">
                <span
                  v-tippy
                  class="hover:text-foreground transition-colors duration-200 cursor-help"
                  :title="formatToDayDateString(news.published_at)"
                >
                  {{ formatTimeAgoToNow(news.published_at) }}
                </span>
                <span class="w-1 h-1 bg-muted-foreground rounded-full"></span>
                <span>{{ news.time_to_read }}&nbsp;{{ __("read") }}</span>
              </div>
            </div>

            <!-- News Image -->
            <div
              v-if="news.photo_url"
              class="flex-shrink-0"
            >
              <img
                class="h-16 w-20 object-cover rounded-lg border border-border shadow-sm"
                :src="news.photo_url"
                :alt="news.title"
              >
            </div>
          </div>
        </div>
      </div>
    </CardContent>
  </Card>
</template>

<script>
import { useHelpers } from '@/Composables/useHelpers';
import {
  Card,
  CardContent,
} from '@/Components/ui/card'

export default {
    components: {
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
