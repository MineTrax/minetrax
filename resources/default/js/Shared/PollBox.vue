<template>
  <Card v-if="poll">
    <CardContent class="p-4 sm:px-5 space-y-3">
      <div class="flex items-center justify-between">
        <h3 class="font-extrabold text-card-foreground">
          {{ isListing ? __("Poll") + ' #' + poll.id : __("Latest Poll") }}
        </h3>
        <span
          v-if="poll.isComingSoon"
          class="inline-flex items-center gap-1 rounded-full bg-amber-500/10 px-2.5 py-0.5 text-xs font-semibold text-amber-500"
        >
          <!-- clock icon -->
          <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 16 16"
            fill="currentColor"
            class="h-3 w-3"
          >
            <path
              fill-rule="evenodd"
              d="M1 8a7 7 0 1 1 14 0A7 7 0 0 1 1 8Zm7.75-4.25a.75.75 0 0 0-1.5 0V8c0 .414.336.75.75.75h3.25a.75.75 0 0 0 0-1.5h-2.5v-3.5Z"
              clip-rule="evenodd"
            />
          </svg>
          {{ __("Upcoming") }}
        </span>
        <span
          v-else-if="!poll.finalResults"
          class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-2.5 py-0.5 text-xs font-semibold text-emerald-500"
        >
          <span class="relative flex h-2 w-2">
            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75" />
            <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500" />
          </span>
          {{ __("Active") }}
        </span>
        <span
          v-else
          class="inline-flex items-center gap-1 rounded-full bg-muted px-2.5 py-0.5 text-xs font-semibold text-muted-foreground"
        >
          {{ __("Closed") }}
        </span>
      </div>

      <div class="text-card-foreground">
        <poll
          v-bind="options"
          @addvote="addVote"
        />
      </div>
    </CardContent>
  </Card>
</template>

<script>
import Poll from "@/Components/Poll.vue";
import {
    Card,
    CardContent,
} from "@/Components/ui/card";

export default {
    components: {
        Poll,
        Card,
        CardContent,
    },
    props: {
        poll: Object,
        isListing: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            options: this.poll
        };
    },
    methods: {
        addVote(obj){
            if (this.poll.isComingSoon) {
                return;
            }
            this.$inertia.post(route("poll.vote", [this.poll.id, obj.value]), null, {
                preserveState: true,
                preserveScroll: true,
            });
        }
    }
};
</script>
