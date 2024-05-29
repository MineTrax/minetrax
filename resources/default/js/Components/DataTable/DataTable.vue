<script setup>
import Icon from '@/Components/Icon.vue';
import { Popover, PopoverButton, PopoverPanel } from '@headlessui/vue';
import { XMarkIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline';
import Multiselect from 'vue-multiselect';
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
                servers: undefined   // Handle for special server filter for ServerIntel pages.
            };
        },
    },
    routeParams: {
        required: false,
        type: Object,
        default: () => {
            return {};
        },
    }
});

const filters = reactive({
    filter: props.filters.filter ?? { q: '' },
    sort: props.filters.sort ?? '',
    perPage: props.filters.perPage ?? 10,
    servers: props.filters.servers ?? undefined // Handle for special server filter for ServerIntel pages.
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
        router.get(route(route().current(), props.routeParams), parsedParams, {
            replace: true,
            preserveScroll: true,
            preserveState: true,
        });
    }, 200
));

const showFilterResetButton = computed(() => {
    if (filters.sort) {
        return true;
    }

    if (filters.perPage != 10) {
        return true;
    }

    for (let f in filters.filter) {
        if (filters.filter[f]) {
            return true;
        }
    }

    return false;
});
function resetFilters() {
    for (let f in filters.filter) {
        delete filters.filter[f];
    }
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
              <MagnifyingGlassIcon class="w-4 h-4 text-gray-500 dark:text-gray-400 stroke-2" />
            </div>
            <input
              id="table-search"
              v-model="filters.filter.q"
              type="text"
              class="block p-2 pl-9 text-sm text-gray-900 border border-gray-300 rounded-lg md:w-80 bg-gray-50 dark:bg-gray-900 dark:border-gray-800 dark:placeholder-gray-400 dark:text-gray-300  focus:border-light-blue-300 focus:ring focus:ring-light-blue-200 focus:ring-opacity-50"
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
                      class="px-4 py-3 text-xs font-semibold text-gray-400 dark:text-gray-300 text-left"
                      :class="[
                        th.class ? th.class : '',
                      ]"
                    >
                      <div class="inline-flex items-center">
                        <Popover
                          v-if="th.filterable"
                        >
                          <PopoverButton class="focus:outline-none">
                            <Icon
                              v-if="filters.filter[th.filterable.key ?? th.key]"
                              name="funnel-fill"
                              class="h-4 cursor-pointer mr-1 inline-block text-green-500 dark:text-green-500 hover:text-gray-700 dark:hover:text-white"
                            />
                            <Icon
                              v-else
                              name="funnel-outline"
                              class="h-4 cursor-pointer mr-1 inline-block text-gray-400 dark:text-gray-300 hover:text-gray-700 dark:hover:text-white"
                            />
                          </PopoverButton>

                          <transition
                            enter-active-class="transition duration-200 ease-out"
                            enter-from-class="translate-y-1 opacity-0"
                            enter-to-class="translate-y-0 opacity-100"
                            leave-active-class="transition duration-150 ease-in"
                            leave-from-class="translate-y-0 opacity-100"
                            leave-to-class="translate-y-1 opacity-0"
                          >
                            <PopoverPanel
                              v-slot="{ close }"
                              class="absolute z-10 min-w-64 p-4 bg-gray-700 shadow border border-gray-600 rounded"
                            >
                              <h3 class="font-semibold text-sm mb-1">
                                {{ __("Filter by :column", {
                                  column: th.label,
                                }) }}
                              </h3>

                              <div>
                                <input
                                  v-if="th.filterable.type === 'text'"
                                  v-model="filters.filter[th.filterable.key ?? th.key]"
                                  class="block p-2 dark:bg-cool-gray-900 dark:text-gray-300 w-full dark:border-gray-700 border-gray-200 rounded-md shadow-sm focus:ring-light-blue-500 focus:border-light-blue-500 sm:text-sm"
                                  :placeholder="`Enter ${th.label}...`"
                                  type="text"
                                >
                                <Multiselect
                                  v-if="['multiselect', 'select'].includes(th.filterable.type)"
                                  v-model="filters.filter[th.filterable.key ?? th.key]"
                                  class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-light-blue-500 focus:border-light-blue-500 sm:text-sm"
                                  :options="th.filterable.options"
                                  :multiple="th.filterable.type === 'multiselect'"
                                  :close-on-select="th.filterable.type === 'select'"
                                  :limit="1"
                                  :clear-on-select="false"
                                  :searchable="false"
                                  :placeholder="`Select ${th.label}...`"
                                />

                                <button
                                  class="inline-flex w-full justify-center py-1.5 px-4 mt-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-500 hover:bg-red-600 focus:outline-none disabled:opacity-50"
                                  :disabled="!filters.filter[th.filterable.key ?? th.key]"
                                  type="button"
                                  @click="() => {
                                    if (!filters.filter[th.filterable.key ?? th.key]) {
                                      return;
                                    }
                                    delete filters.filter[th.filterable.key ?? th.key]
                                    close()
                                  }"
                                >
                                  {{ __("Clear") }}
                                </button>
                              </div>
                            </PopoverPanel>
                          </transition>
                        </Popover>
                        <div
                          class="inline-flex items-center uppercase"
                          :class="[
                            th.sortable ? 'cursor-pointer' : '',
                          ]"
                          @click="th.sortable ? toggleSorting(th.key) : null"
                        >
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

                <tr v-if="data.data.length <= 0">
                  <td
                    :colspan="header.length"
                    class="px-4 py-3 text-sm font-medium text-gray-500 dark:text-gray-300 text-center"
                  >
                    {{ __("No data found") }}
                  </td>
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
          <p
            v-if="data.total != undefined"
            class="text-sm text-gray-700 dark:text-gray-400 ml-2"
          >
            {{ __("Showing") }}
            <span class="font-semibold dark:text-gray-300">{{ data.from ?? 0 }}</span>
            {{ __("to") }}
            <span class="font-semibold dark:text-gray-300">{{ data.to ?? 0 }}</span>
            {{ __("of") }}
            <span class="font-semibold dark:text-gray-300">{{ data.total }}</span>
            {{ __("results") }}
          </p>
        </div>
        <div>
          <DtPagination
            v-if="data.next_page_url || data.prev_page_url"
            :data="data"
          />
        </div>
      </div>
    </div>
  </div>
  <!-- DataTable ends-->
</template>
