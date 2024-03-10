<template>
  <slot />

  <slot
    v-if="loading"
    name="loading"
  >
    <div class="p-5 text-center text-gray-600 dark:text-gray-300 text-sm">
      {{ loadingText }}
    </div>
  </slot>
</template>

<script>
import {debounce} from 'lodash/function';

export default {
    props: {
        loadMore: Function,
        loadingText: {
            type: String,
            default: 'Loading more...'
        },
        threshold: {
            type: Number,
            default: 200
        }
    },

    data() {
        return {
            loading: false,
            eventListener: null
        };
    },

    mounted() {
        window.addEventListener('scroll', this.eventListener = debounce(e => {
            let pixelFromBotton = document.documentElement.offsetHeight - document.documentElement.scrollTop - window.innerHeight;
            if (pixelFromBotton < this.threshold && !this.loading) {
                this.loading = true;
                this.loadMore().finally(() => this.loading = false);
            }
        }, 100));
    },

    unmounted() {
        window.removeEventListener('scroll', this.eventListener);
    }
};
</script>
