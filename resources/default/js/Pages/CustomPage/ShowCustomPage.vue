<template>
  <app-layout>
    <app-head :title="customPage.title" />

    <div v-if="!customPage.is_visible">
      <div class="bg-surface-500 text-white px-4 py-3 text-center">
        <div class="flex items-center justify-center">
          <svg
            class="fill-current h-5 w-5 mr-2"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20 20"
          ><path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z" /></svg>
          <span class="font-semibold">{{ __("Page is hidden!") }}</span>
          <span class="ml-2 text-sm">{{ __("This page is not visible to public.") }}</span>
        </div>
      </div>
    </div>

    <!-- If HTML Page -->
    <div v-if="customPage.is_html_page" v-html="customPage.body_html" />

    <!-- If not HTML Page -->
    <div v-else class="py-4 px-2 md:py-12 md:px-10 max-w-7xl mx-auto">
      <div class="flex justify-between md:mb-4">
        <h1 class="text-center font-bold text-2xl md:text-4xl text-foreground dark:text-foreground mb-5">
          {{ customPage.title }}
        </h1>
        <div class="">
          <inertia-link
            :href="route('home')"
            class="inline-flex items-center px-4 py-2 bg-surface-400 dark:bg-surface-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-surface-500 active:bg-surface-600 focus:outline-none focus:border-foreground focus:shadow-outline-gray transition ease-in-out duration-150"
          >
            <span>{{ __("Homepage") }}</span>
          </inertia-link>
        </div>
      </div>
      <div class="flex flex-col md:flex-row md:space-x-4">
        <div
          class="overflow-x-auto"
          :class="customPage.is_sidebar_visible ? 'md:w-9/12' : 'md:w-full'"
        >
          <div class="min-w-full">
            <div class="shadow max-w-none bg-white dark:bg-surface-800 px-3 py-2 md:px-10 md:py-5 overflow-hidden rounded">
              <div
                class="prose max-w-none dark:prose-invert"
                v-html="customPage.body_html"
              />
            </div>
          </div>
        </div>

        <div
          v-if="customPage.is_sidebar_visible"
          class="md:w-3/12 flex-1 space-y-4 mt-4 md:mt-0"
        >
          <server-status-box />
          <shout-box />
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import ServerStatusBox from '@/Shared/ServerStatusBox.vue';
import ShoutBox from '@/Shared/ShoutBox.vue';

export default {

    components: {
        ServerStatusBox,
        AppLayout,
        ShoutBox
    },
    props: {
        customPage: Object
    },
};
</script>
