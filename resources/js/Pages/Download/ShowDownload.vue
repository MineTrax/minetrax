<script setup>
import ServerStatusBox from '@/Shared/ServerStatusBox.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import {Link} from '@inertiajs/vue3';
import AppHead from '@/Components/AppHead.vue';
import {useTranslations} from '@/Composables/useTranslations';
import {useHelpers} from '@/Composables/useHelpers';

const { __ } = useTranslations();
const { formatToDayDateString } = useHelpers();

defineProps({
    download: {
        type: Object,
        required: true,
    },
});
</script>

<template>
  <AppLayout>
    <AppHead :title="__(':title - Downloads', {title: download.name})" />

    <div class="py-4 px-2 md:py-12 md:px-10 max-w-7xl mx-auto">
      <div class="flex justify-end mb-8">
        <div class="flex">
          <Link
            :href="route('download.index')"
            class="inline-flex items-center px-4 py-2 bg-gray-400 dark:bg-cool-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 active:bg-gray-600 focus:outline-none focus:border-gray-500 focus:shadow-outline-gray transition ease-in-out duration-150"
          >
            <span>{{ __("Back") }}</span>
          </Link>
        </div>
      </div>
      <div class="flex flex-col md:flex-row md:space-x-4">
        <div class="-my-2 md:w-9/12 overflow-x-auto md:-mx-6 lg:-mx-8">
          <div class="py-2 align-middle inline-block min-w-full md:px-6 lg:px-8">
            <div class="shadow max-w-none bg-white px-3 py-2 md:px-10 md:py-5 overflow-hidden border-b border-gray-200 rounded md:rounded-lg dark:bg-cool-gray-800 dark:border-none">
              <h1 class="font-bold text-4xl text-gray-900 dark:text-gray-200 mb-5">
                {{ download.name }}
              </h1>

              <div class="mb-4">
                <p
                  class="text-gray-500 dark:text-gray-400 text-sm focus:outline-none"
                >
                  {{ __("Created:") }} {{ formatToDayDateString(download.created_at) }}
                  ▪
                  {{ __("Total Downloads: ") }} {{ download.download_count }}
                </p>
              </div>

              <div
                class="prose dark:prose-dark max-w-none"
                v-html="download.description_html"
              />

              <div
                id="download-button-section"
                class="flex justify-center items-center mb-5"
              >
                <a
                  target="_blank"
                  :href="route('download.download', download.slug)"
                  class="relative inline-block px-4 py-2 md:px-8 md:py-4 font-medium group"
                >
                  <span class="absolute inset-0 w-full h-full transition duration-200 ease-out transform translate-x-1 translate-y-1 bg-black dark:bg-white group-hover:-translate-x-0 group-hover:-translate-y-0" />
                  <span class="absolute inset-0 w-full h-full bg-white dark:bg-gray-800 border-2 border-black dark:border-white group-hover:bg-black dark:group-hover:bg-white" />
                  <span class="relative text-black dark:text-white group-hover:text-white dark:group-hover:text-black">{{ __("Download Now") }}</span>
                </a>
              </div>
            </div>
          </div>
        </div>

        <div class="md:w-3/12 flex-1 space-y-4 mt-4 md:mt-0">
          <server-status-box />
        </div>
      </div>
    </div>
  </AppLayout>
</template>
