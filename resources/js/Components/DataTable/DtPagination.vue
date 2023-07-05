<script setup>
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline';
import {  computed, ref, watchEffect } from 'vue';
let pages = ref([]);
let previousPage = ref({});
let nextPage = ref({});
let pageGenArray = ref([]);

const props = defineProps({
    data: {
        required: true,
        type: Object,
    },
});

const isSimplePagination = computed(() => {
    return props.data.total == undefined;
});

generatePaginationLinks();
watchEffect(() => {
    generatePaginationLinks();
});

// Generate Pagination Links
function generatePaginationLinks() {
    if(isSimplePagination.value) {
        return;
    }

    // copy array
    const linksArray = props.data.links.map((link) => {
        return {
            url: link.url,
            label: link.label,
            active: link.active,
        };
    });

    pages.value = linksArray;
    // remove last and first element
    previousPage.value = pages.value.shift();
    nextPage.value = pages.value.pop();

    const currentPageIndex = pages.value.findIndex((page) => page.active);
    const totalPages = pages.value.length;
    pageGenArray.value = pagination(currentPageIndex + 1, totalPages);
}

// Pagination Helper
function getRange(start, end) {
    return Array(end - start + 1)
        .fill()
        .map((v, i) => i + start);
}
function pagination(currentPage, pageCount) {
    let delta = null;
    if (pageCount <= 7) {
        // delta === 7: [1 2 3 4 5 6 7]
        delta = 7;
    } else {
        // delta === 2: [1 ... 4 5 6 ... 10]
        // delta === 4: [1 2 3 4 5 ... 10]
        delta = currentPage > 4 && currentPage < pageCount - 3 ? 2 : 4;
    }

    const range = {
        start: Math.round(currentPage - delta / 2),
        end: Math.round(currentPage + delta / 2),
    };

    if (range.start - 1 === 1 || range.end + 1 === pageCount) {
        range.start += 1;
        range.end += 1;
    }

    let pages =
        currentPage > delta
            ? getRange(
                Math.min(range.start, pageCount - delta),
                Math.min(range.end, pageCount)
            )
            : getRange(1, Math.min(pageCount, delta + 1));

    const withDots = (value, pair) =>
        pages.length + 1 !== pageCount ? pair : [value];

    if (pages[0] !== 1) {
        pages = withDots(1, [1, '...']).concat(pages);
    }

    if (pages[pages.length - 1] < pageCount) {
        pages = pages.concat(withDots(pageCount, ['...', pageCount]));
    }

    return pages;
}
// Pagination Helper ends
</script>

<template>
  <nav
    v-if="isSimplePagination"
    class="isolate inline-flex space-x-2 rounded-md shadow-sm"
  >
    <InertiaLink
      v-if="props.data.prev_page_url"
      :href="data.prev_page_url"
      class="relative inline-flex items-center rounded-md border border-gray-300 dark:border-gray-800 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
    >
      {{ __("Previous") }}
    </InertiaLink>
    <button
      v-else
      disabled
      class="relative inline-flex items-center rounded-md border border-gray-300 dark:border-gray-800 dark:bg-gray-700 dark:text-gray-500  bg-white px-4 py-2 text-sm font-medium text-gray-400 cursor-not-allowed"
    >
      {{ __("Previous") }}
    </button>
    <InertiaLink
      v-if="props.data.next_page_url"
      :href="data.next_page_url"
      class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 dark:border-gray-800 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
    >
      {{ __("Next") }}
    </InertiaLink>
    <button
      v-else
      disabled
      class="relative inline-flex items-center rounded-md border border-gray-300 dark:border-gray-800 dark:bg-gray-700 dark:text-gray-500  bg-white px-4 py-2 text-sm font-medium text-gray-400 cursor-not-allowed"
    >
      {{ __("Next") }}
    </button>
  </nav>

  <nav
    v-else
    class="isolate inline-flex -space-x-px rounded-md shadow-sm"
    aria-label="Pagination"
  >
    <InertiaLink
      :href="previousPage.url ?? '#'"
      class="relative disabled:bg-gray-900 inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 dark:text-gray-300 ring-1 ring-inset ring-gray-300 dark:ring-gray-700 focus:z-20 focus:outline-offset-0"
      :class="[
        previousPage.url == null
          ? 'pointer-events-none'
          : 'hover:bg-gray-50 dark:hover:bg-gray-700',
      ]"
    >
      <span class="sr-only">{{ __("Previous") }}</span>
      <ChevronLeftIcon class="h-5 w-5" />
    </InertiaLink>
    <!-- Current: "z-10 bg-indigo-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600", Default: "text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:outline-offset-0" -->

    <template
      v-for="(page, index) in pageGenArray"
      :key="index"
    >
      <span
        v-if="page === '...'"
        class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 ring-1 ring-inset ring-gray-300 dark:ring-gray-700 focus:outline-offset-0"
      >...</span>
      <InertiaLink
        v-else
        :href="pages[page - 1].url"
        class="relative inline-flex items-center px-4 py-2 text-sm text-gray-900 dark:text-gray-300 ring-1 ring-inset ring-gray-300 dark:ring-gray-700 focus:z-20 focus:outline-offset-0"
        :class="[
          pages[page - 1].active
            ? 'bg-gray-300 dark:bg-gray-900 font-semibold'
            : 'hover:bg-gray-50 dark:hover:bg-gray-700',
        ]"
      >
        {{ pages[page - 1].label }}
      </InertiaLink>
    </template>

    <InertiaLink
      :disabled="!nextPage.url == null"
      :href="nextPage.url ?? '#'"
      class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 dark:text-gray-300 ring-1 ring-inset ring-gray-300 dark:ring-gray-700 focus:z-20 focus:outline-offset-0"
      :class="[
        nextPage.url == null
          ? 'pointer-events-none'
          : 'hover:bg-gray-50 dark:hover:bg-gray-700',
      ]"
    >
      <span class="sr-only">{{ __("Next") }}</span>
      <ChevronRightIcon class="h-5 w-5" />
    </InertiaLink>
  </nav>
</template>
