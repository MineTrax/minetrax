<script setup>
import Icon from '@/Components/Icon.vue';
import { XMarkIcon } from '@heroicons/vue/24/outline';
import { reactive, watch } from 'vue';
import DtPagination from './DtPagination.vue';
import { router } from '@inertiajs/vue3';
import { identity, pickBy, throttle } from 'lodash';
import { computed } from 'vue';

const props = defineProps({
    data: {
        required: true,
        type: Object,
    },
    header: {
        required: true,
        type: Array,
    },
    filters: {
        required: false,
        type: Object,
        default: () => {
            return {
                sort: '',
                perPage: '',
                filter: {},
            };
        },
    },
});

const filters = reactive({
    filter: props.filters.filter ?? { q: '' },
    sort: props.filters.sort ?? '',
    perPage: props.filters.perPage ?? 10,
});
watch(filters, throttle(
    (newParams) => {
        let parsedParams = pickBy(newParams, identity);
        // delete search if not exists
        if (!parsedParams.filter.q) {
            delete parsedParams.filter.q;
        }
        if(parsedParams.perPage == 10) {
            delete parsedParams.perPage;
        }
        router.get(route(route().current()), parsedParams, {
            replace: true,
            preserveScroll: true,
            preserveState: true,
        });
    }, 200
));

const showFilterResetButton = computed(() => {
    return filters.filter.q || filters.sort || filters.perPage != 10;
});
function resetFilters() {
    filters.filter.q = '';
    filters.sort = '';
    filters.perPage = 10;
}

// Sorting
const sortedField = computed(() => {
    if (filters.sort) {
        return filters.sort.replace('-', '');
    }
    return '';
});
const sortedDirection = computed(() => {
    if (filters.sort) {
        return filters.sort.startsWith('-') ? 'desc' : 'asc';
    }
    return '';
});

function toggleSorting(key) {
    // toggle sorting by logic of -key and key
    if (filters.sort === key) {
        filters.sort = '-' + key;
    } else if (filters.sort === '-' + key) {
        filters.sort = '';
    } else {
        filters.sort = key;
    }
}

</script>

<template>
  <!-- DataTable starts -->
  <div class="flex flex-col">
    <div
      id="tableHeader"
      class="flex justify-between p-4"
    >
      <div
        id="headerLeft"
        class="flex"
      >
        <div id="searchBox">
          <div class="relative mt-1">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
              <svg
                class="w-5 h-5 text-gray-500 dark:text-gray-400"
                aria-hidden="true"
                fill="currentColor"
                viewBox="0 0 20 20"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  fill-rule="evenodd"
                  d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                  clip-rule="evenodd"
                />
              </svg>
            </div>
            <input
              id="table-search"
              v-model="filters.filter.q"
              type="text"
              class="block p-2 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg md:w-80 bg-gray-50 focus:ring-light-blue-500 focus:border-light-blue-500 dark:bg-gray-900 dark:border-gray-800 dark:placeholder-gray-400 dark:text-gray-300 dark:focus:ring-light-blue-500 dark:focus:border-light-blue-500"
              :placeholder="__('Search..')"
            >
          </div>
        </div>
      </div>
      <div
        id="headerRight"
        class="flex"
      >
        <div
          v-show="showFilterResetButton"
          id="resetButton"
        >
          <button
            class="bg-white hidden md:block dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-400 dark:border-gray-800 hover:bg-gray-100 text-gray-500 font-semibold py-1 px-4 border border-gray-200 rounded"
            @click="resetFilters()"
          >
            <XMarkIcon class="w-4 h-4 inline-block text-gray-500" />
            {{ __("Reset") }}
          </button>
        </div>
      </div>
    </div>

    <div
      id="tableSection"
      class="flex flex-col"
    >
      <div class="overflow-x-auto">
        <div class="min-w-full inline-block align-middle">
          <div class="overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-100 dark:bg-gray-700">
                <tr>
                  <slot name="header">
                    <th
                      v-for="th in header"
                      :key="th.key"
                      scope="col"
                      class="px-6 py-3 text-left text-xs font-semibold text-gray-400 dark:text-gray-300 uppercase"
                      :class="[
                        th.sortable ? 'cursor-pointer' : '',
                        th.class ? th.class : '',
                      ]"
                      @click="th.sortable ? toggleSorting(th.key) : null"
                    >
                      <div class="flex items-center">
                        {{ th.label }}
                        <Icon
                          v-if="th.sortable"
                          :name="sortedField === th.key ? (sortedDirection === 'asc' ? 'sort-up' : 'sort-down') : 'sort-updown'"
                          class="w-3 h-3 ml-1 inline-block text-gray-400 dark:text-gray-300"
                          :class="[
                            sortedField === th.key ? 'text-light-blue-500 dark:text-light-blue-400' : '',
                          ]"
                        />
                      </div>
                    </th>
                  </slot>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <tr
                  v-for="item in data.data"
                  :key="item.id"
                >
                  <slot
                    :item="item"
                    :data="data"
                  />
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div
      id="tableFooter"
      class="flex items-center justify-between border-t border-gray-200 dark:border-gray-700 px-4 py-3"
    >
      <div class="flex flex-1 justify-between sm:hidden">
        <InertiaLink
          :href="data.prev_page_url ?? '#'"
          class="relative inline-flex items-center rounded-md border border-gray-300 dark:border-gray-800 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
        >
          {{ __("Previous") }}
        </InertiaLink>
        <InertiaLink
          :href="data.next_page_url ?? '#'"
          class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 dark:border-gray-800 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
        >
          {{ __("Next") }}
        </InertiaLink>
      </div>
      <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
        <div class="flex items-center">
          <div>
            <select
              id="perPage"
              v-model="filters.perPage"
              class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-light-blue-500 focus:border-light-blue-500 block w-full dark:bg-gray-900 dark:border-gray-800 dark:placeholder-gray-400 dark:text-gray-300 dark:focus:ring-light-blue-500 dark:focus:border-light-blue-500"
            >
              <option
                :value="10"
                :selected="data.per_page == 10"
              >
                10 {{ __("per page") }}
              </option>
              <option
                :value="20"
                :selected="data.per_page == 20"
              >
                20 {{ __("per page") }}
              </option>
              <option
                :value="50"
                :selected="data.per_page == 50"
              >
                50 {{ __("per page") }}
              </option>
              <option
                :value="100"
                :selected="data.per_page == 100"
              >
                100 {{ __("per page") }}
              </option>
            </select>
          </div>
          <p class="text-sm text-gray-700 dark:text-gray-400 ml-2">
            {{ __("Showing") }}
            <span class="font-semibold dark:text-gray-300">{{ data.from }}</span>
            {{ __("to") }}
            <span class="font-semibold dark:text-gray-300">{{ data.to }}</span>
            {{ __("of") }}
            <span class="font-semibold dark:text-gray-300">{{ data.total }}</span>
            {{ __("results") }}
          </p>
        </div>
        <div>
          <DtPagination
            v-if="data.last_page > 1"
            :data="data"
          />
        </div>
      </div>
    </div>
  </div>
  <!-- DataTable ends-->
</template>
