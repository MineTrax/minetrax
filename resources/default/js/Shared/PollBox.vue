<template>
  <div v-if="poll">
    <div class="p-3 sm:px-5 bg-white dark:bg-cool-gray-800 rounded shadow">
      <h3
        v-if="!isListing"
        class="font-extrabold text-gray-800 dark:text-gray-200"
      >
        {{ __("Latest Poll") }}
      </h3>
      <h3
        v-if="isListing"
        class="font-extrabold text-gray-800 dark:text-gray-200"
      >
        {{ __("Poll") }} {{ poll.id }}
      </h3>
      <div class="mt-3 text-gray-500 dark:text-gray-300">
        <poll
          v-bind="options"
          @addvote="addVote"
        />
      </div>
    </div>
  </div>
</template>

<script>
import Poll from '@/Components/Poll.vue';

export default {
    components: {
        Poll
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
