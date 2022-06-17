<template>
  <transition name="slide-fade">
    <div
      v-if="toast && show && !popstate"
      class="flex fixed w-full max-w-sm mt-16 mr-4 bg-white right-0 top-0 p-4 rounded-lg shadow z-50"
    >
      <div class="mr-3">
        <svg
          v-if="toast.type === 'success'"
          class="w-6 h-6 text-green-500"
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
          />
        </svg>
        <svg
          v-else-if="toast.type === 'danger'"
          class="w-6 h-6 text-red-500"
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
          />
        </svg>
        <svg
          v-else-if="toast.type === 'warning'"
          class="w-6 h-6 text-yellow-500"
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
          />
        </svg>
        <svg
          v-else
          class="w-6 h-6 text-blue-500"
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
          />
        </svg>
      </div>
      <div class="flex-1">
        <b class="text-sm">{{ toast.title }}</b>
        <p
          v-if="toast.body"
          class="text-sm text-gray-600"
        >
          {{ toast.body }}
        </p>
      </div>
      <div class="ml-2">
        <button
          class="focus:outline-none"
          @click="show = !show"
        >
          <svg
            class="w-5 h-5 text-gray-500 hover:text-gray-700 "
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M6 18L18 6M6 6l12 12"
            />
          </svg>
        </button>
      </div>
    </div>
  </transition>
</template>

<script>
export default {
    props: {
        toast: Object,
        popstate: Boolean
    },
    data() {
        return {
            milliseconds: this.toast && this.toast.milliseconds ? this.toast.milliseconds : 3000,
            show: false,
            timeout: null,
        };
    },
    watch: {
        toast: {
            deep: true,
            handler(o, n) {
                this.milliseconds = this.toast.milliseconds ?? 3000;
                this.show = true;

                if(this.timeout) {
                    clearTimeout(this.timeout);
                }
                this.timeout = setTimeout(() => {
                    this.show = false;
                }, this.milliseconds);
            }
        }
    },
    mounted() {
        this.show = true;

        if(this.timeout) {
            clearTimeout(this.timeout);
        }
        this.timeout = setTimeout(() => {
            this.show = false;
        }, this.milliseconds);
    }
};
</script>

<style scoped>
/* Enter and leave animations can use different */
/* durations and timing functions.              */
.slide-fade-enter-active {
    transition: all .3s ease;
}
.slide-fade-leave-active {
    transition: all .3s cubic-bezier(1.0, 0.5, 0.8, 1.0);
}
.slide-fade-enter, .slide-fade-leave-to
    /* .slide-fade-leave-active below version 2.1.8 */ {
    transform: translateX(10px);
    opacity: 0;
}
</style>
