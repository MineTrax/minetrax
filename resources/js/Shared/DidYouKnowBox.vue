<template>
  <div
    v-if="enabled"
    ref="box"
  >
    <div class="p-3 sm:px-5 bg-white dark:bg-cool-gray-800 rounded shadow">
      <h3 class="font-extrabold text-gray-800 dark:text-gray-200">
        {{ __("Did You Know?") }}
      </h3>

      <!--Loading-->
      <div
        v-if="loading"
        class="space-y-4 mt-4"
      >
        <div class="max-w-sm w-full mx-auto">
          <div class="animate-pulse flex space-x-4">
            <div class="rounded bg-gray-300 dark:bg-cool-gray-700 h-8 w-8" />
            <div class="flex-1 space-y-1 py-1">
              <div class="h-4 bg-gray-300 dark:bg-cool-gray-700 rounded w-3/4" />
              <div class="h-4 bg-gray-300 dark:bg-cool-gray-700 rounded w-5/6" />
            </div>
          </div>
        </div>
      </div>

      <!--Data-->
      <div
        v-if="!loading"
        class="flex space-x-2 mt-4"
      >
        <img
          v-if="imageUrl"
          class="w-14 h-14"
          :src="imageUrl"
          alt="Image"
        >
        <div class="text-gray-600 dark:text-gray-300 text-sm">
          {{ text }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>

export default {
    props: {
        enabled: Boolean
    },

    data() {
        return {
            text: null,
            imageUrl: null,
            loading: true,
            interval: null
        };
    },

    created() {
        axios.get(route('didyouknow.get')).then(data => {
            this.text = data.data.text;
            this.imageUrl = data.data.image;
        }).finally(() => {
            this.loading = false;
        });
    },

    mounted() {
        this.interval = setInterval(() => {
            // If element is not in viewport then don't hit
            if (!this.isInViewport()) {
                return;
            }
            this.loading = true;
            axios.get(route('didyouknow.get')).then(data => {
                this.text = data.data.text;
                this.imageUrl = data.data.image;
            }).finally(() => {
                this.loading = false;
            });
        }, 30000);
    },

    destroyed() {
        clearInterval(this.interval);
    },

    methods: {
        isInViewport() {
            const rect = this.$refs.box?.getBoundingClientRect();
            if (!rect) return false;

            return (
                rect.top >= 0 &&
                rect.left >= 0 &&
                rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                rect.right <= (window.innerWidth || document.documentElement.clientWidth)
            );
        }
    }
};
</script>
