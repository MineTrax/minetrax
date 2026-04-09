<template>
  <Card v-if="poll">
    <CardContent class="p-4 sm:px-5 space-y-3">
      <div class="flex items-center justify-between">
        <h3 class="font-extrabold text-card-foreground">
          {{ isListing ? __("Poll") + ' #' + poll.id : __("Latest Poll") }}
        </h3>
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
