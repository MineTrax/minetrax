<template>
  <Card v-if="poll">
    <CardContent class="p-3 space-y-2 sm:px-5">
      <h3
        v-if="!isListing"
        class="font-extrabold text-foreground dark:text-foreground"
      >
        {{ __("Latest Poll") }}
      </h3>
      <h3
        v-if="isListing"
        class="font-extrabold text-foreground dark:text-foreground"
      >
        {{ __("Poll") }} {{ poll.id }}
      </h3>
      <div class="mt-3 text-foreground dark:text-card-foreground">
        <poll
          v-bind="options"
          @addvote="addVote"
        />
      </div>
    </CardContent>
  </Card>
</template>

<script>
import Poll from '@/Components/Poll.vue';
import {
  Card,
  CardContent,
} from '@/Components/ui/card'

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
            this.$inertia.post(route('poll.vote', [this.poll.id, obj.value]), null, {
                preserveState: true,
                preserveScroll: true,
            });
        }
    }
};
</script>
