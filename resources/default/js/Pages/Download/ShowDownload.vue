<script setup>
import ServerStatusBox from '@/Shared/ServerStatusBox.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import {Link} from '@inertiajs/vue3';
import AppHead from '@/Components/AppHead.vue';
import {useTranslations} from '@/Composables/useTranslations';
import {useHelpers} from '@/Composables/useHelpers';
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';
import { truncate } from 'lodash';

const { __ } = useTranslations();
const { formatToDayDateString } = useHelpers();

const props = defineProps({
    download: {
        type: Object,
        required: true,
    },
});

const breadcrumbItems = [
    {
        text: __('Home'),
        url: route('home'),
        current: false
    },
    {
        text: __('Downloads'),
        url: route('download.index'),
        current: false
    },
    {
        text: truncate(props.download.name, { length: 50 }),
        current: true
    }
];
</script>

<template>
  <AppLayout>
    <AppHead :title="__(':title - Downloads', {title: download.name})" />

    <AppBreadcrumb class="max-w-screen-2xl mx-auto" :items="breadcrumbItems" />

    <div class="py-4 px-2 md:px-10 max-w-screen-2xl mx-auto">
      <div class="flex flex-col md:flex-row md:space-x-4">
        <div class="-my-2 md:w-9/12 overflow-x-auto md:-mx-6 lg:-mx-8">
          <div class="py-2 align-middle inline-block min-w-full md:px-6 lg:px-8">
            <div class="shadow max-w-none bg-white px-3 py-2 md:px-10 md:py-5 overflow-hidden border-b border-foreground rounded md:rounded-lg dark:bg-surface-800 dark:border-none">
              <h1 class="font-bold text-4xl text-foreground dark:text-foreground mb-5">
                {{ download.name }}
              </h1>

              <div class="mb-4">
                <p
                  class="text-foreground dark:text-foreground text-sm focus:outline-none"
                >
                  {{ __("Created:") }} {{ formatToDayDateString(download.created_at) }}
                  ▪
                  {{ __("Total Downloads: ") }} {{ download.download_count }}
                </p>
              </div>

              <div
                class="prose dark:prose-invert max-w-none"
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
                  <span class="absolute inset-0 w-full h-full bg-white dark:bg-surface-800 border-2 border-black dark:border-white group-hover:bg-black dark:group-hover:bg-white" />
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
