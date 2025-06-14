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
              <MagnifyingGlassIcon class="w-4 h-4 text-secondary-500 stroke-2 dark:text-secondary-400" />
            </div>
            <input
              id="table-search"
              v-model="filters.filter.q"
              type="text"
              class="block p-2 text-sm text-secondary-900 border border-secondary-300 rounded-lg pl-9 md:w-80 bg-surface-50 dark:bg-surface-900 dark:border-secondary-800 dark:placeholder-secondary-400 dark:text-secondary-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50"
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
            class="hidden px-4 py-1 font-semibold text-secondary-500 bg-white border border-secondary-200 rounded md:block dark:bg-surface-700 dark:hover:bg-surface-600 dark:text-secondary-400 dark:border-secondary-800 hover:bg-surface-100"
            @click="resetFilters()"
          >
            <XMarkIcon class="inline-block w-4 h-4 text-secondary-500" />
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
        <div class="inline-block min-w-full align-middle">
          <div class="overflow-hidden">
            <table class="min-w-full divide-y divide-secondary-200 dark:divide-secondary-700">
              <thead class="bg-surface-100 dark:bg-surface-700">
                <tr>
                  <slot name="header">
                    <th
                      v-for="th in header"
                      :key="th.key"
                      scope="col"
                      class="px-4 py-3 text-xs font-semibold text-left text-secondary-400 dark:text-secondary-300"
                      :class="[th.class ? th.class : '']"
                    >
                      <div class="inline-flex items-center">
                        <Popover v-if="th.filterable">
                          <PopoverButton class="focus:outline-none">
                            <Icon
                              v-if="Array.isArray(th.filterable)
                                ? th.filterable.some(filter => filters.filter[filter.key ?? th.key])
                                : filters.filter[th.filterable.key ?? th.key]"
                              name="funnel-fill"
                              class="inline-block h-4 mr-1 text-success-500 cursor-pointer dark:text-success-500 hover:text-secondary-700 dark:hover:text-white"
                            />
                            <Icon
                              v-else
                              name="funnel-outline"
                              class="inline-block h-4 mr-1 text-secondary-400 cursor-pointer dark:text-secondary-300 hover:text-secondary-700 dark:hover:text-white"
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
                              class="absolute z-10 p-4 text-secondary-800 bg-white border border-secondary-200 rounded shadow dark:text-secondary-300 min-w-64 dark:bg-surface-700 dark:border-secondary-600"
                            >
                              <h3 class="mb-1 text-sm font-semibold">
                                {{ Array.isArray(th.filterable)
                                  ?
                                    th.filterable.title ?? null
                                  : th.filterable.title ?? __("Filters for :column", { column: th.label }) }}
                              </h3>

                              <div>
                                <!-- If: Array Filterable -->
                                <template v-if="Array.isArray(th.filterable)">
                                  <div
                                    v-for="filter in th.filterable"
                                    :key="filter.key ?? th.key"
                                    class="mb-4"
                                  >
                                    <h4 class="mb-1 text-sm font-medium">
                                      {{ filter.title ?? __("Filters for :column", { column: filter.label }) }}
                                    </h4>
                                    <input
                                      v-if="filter.type === 'text'"
                                      v-model="filters.filter[filter.key ?? th.key]"
                                      class="block w-full p-2 border-secondary-200 rounded-md shadow-sm dark:bg-surface-900 dark:text-secondary-300 dark:border-secondary-700 focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                                      :placeholder="`Enter ${filter.title ?? filter.label}...`"
                                      type="text"
                                    >
                                    <Multiselect
                                      v-if="['multiselect', 'select'].includes(filter.type)"
                                      v-model="filters.filter[filter.key ?? th.key]"
                                      class="block w-full border-secondary-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                                      :options="filter.options"
                                      :multiple="filter.type === 'multiselect'"
                                      :close-on-select="filter.type === 'select'"
                                      :limit="1"
                                      :clear-on-select="false"
                                      :searchable="filter.searchable ?? false"
                                      :placeholder="`Select ${filter.title ?? filter.label}...`"
                                    />
                                    <button
                                      class="inline-flex w-full justify-center py-1.5 px-4 mt-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-error-500 hover:bg-error-600 focus:outline-none disabled:opacity-50"
                                      :disabled="!filters.filter[filter.key ?? th.key]"
                                      type="button"
                                      @click="() => {
                                        if (!filters.filter[filter.key ?? th.key]) {
                                          return;
                                        }
                                        delete filters.filter[filter.key ?? th.key];
                                      }"
                                    >
                                      {{ __("Clear") }}
                                    </button>
                                  </div>
                                </template>
                                <!-- Else: Single Object -->
                                <template v-else>
                                  <input
                                    v-if="th.filterable.type === 'text'"
                                    v-model="filters.filter[th.filterable.key ?? th.key]"
                                    class="block w-full p-2 border-secondary-200 rounded-md shadow-sm dark:bg-surface-900 dark:text-secondary-300 dark:border-secondary-700 focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                                    :placeholder="`Enter ${th.label}...`"
                                    type="text"
                                  >
                                  <Multiselect
                                    v-if="['multiselect', 'select'].includes(th.filterable.type)"
                                    v-model="filters.filter[th.filterable.key ?? th.key]"
                                    class="block w-full border-secondary-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                                    :options="th.filterable.options"
                                    :multiple="th.filterable.type === 'multiselect'"
                                    :close-on-select="th.filterable.type === 'select'"
                                    :limit="1"
                                    :clear-on-select="false"
                                    :searchable="th.filterable.searchable ?? false"
                                    :placeholder="`Select ${th.label}...`"
                                  />
                                  <button
                                    class="inline-flex w-full justify-center py-1.5 px-4 mt-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-error-500 hover:bg-error-600 focus:outline-none disabled:opacity-50"
                                    :disabled="!filters.filter[th.filterable.key ?? th.key]"
                                    type="button"
                                    @click="() => {
                                      if (!filters.filter[th.filterable.key ?? th.key]) {
                                        return;
                                      }
                                      delete filters.filter[th.filterable.key ?? th.key];
                                      close();
                                    }"
                                  >
                                    {{ __("Clear") }}
                                  </button>
                                </template>
                              </div>
                            </PopoverPanel>
                          </transition>
                        </Popover>
                        <div
                          class="inline-flex items-center uppercase"
                          :class="[th.sortable ? 'cursor-pointer' : '']"
                          @click="th.sortable ? toggleSorting(th.key) : null"
                        >
                          {{ th.label }}
                          <Icon
                            v-if="th.sortable"
                            :name="sortedField === th.key ? (sortedDirection === 'asc' ? 'sort-up' : 'sort-down') : 'sort-updown'"
                            class="inline-block w-3 h-3 ml-1 text-secondary-400 dark:text-secondary-300"
                            :class="[
                              sortedField === th.key ? 'text-primary-500 dark:text-primary-400' : '',
                            ]"
                          />
                        </div>
                      </div>
                    </th>
                  </slot>
                </tr>
              </thead>

              <tbody class="divide-y divide-secondary-200 dark:divide-secondary-700">
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
                    class="px-4 py-3 text-sm font-medium text-center text-secondary-500 dark:text-secondary-300"
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
      class="flex items-center justify-between px-4 py-3 border-t border-secondary-200 dark:border-secondary-700"
    >
      <div class="flex justify-between flex-1 sm:hidden">
        <InertiaLink
          :href="data.prev_page_url ?? '#'"
          class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-secondary-700 bg-white border border-secondary-300 rounded-md dark:border-secondary-800 dark:bg-surface-700 dark:text-secondary-300 dark:hover:bg-surface-600 hover:bg-surface-50"
        >
          {{ __("Previous") }}
        </InertiaLink>
        <InertiaLink
          :href="data.next_page_url ?? '#'"
          class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-secondary-700 bg-white border border-secondary-300 rounded-md dark:border-secondary-800 dark:bg-surface-700 dark:text-secondary-300 dark:hover:bg-surface-600 hover:bg-surface-50"
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
              class="block w-full text-sm text-secondary-900 border border-secondary-300 rounded-lg bg-surface-50 focus:ring-primary-500 focus:border-primary-500 dark:bg-surface-900 dark:border-secondary-800 dark:placeholder-secondary-400 dark:text-secondary-300 dark:focus:ring-primary-500 dark:focus:border-primary-500"
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
            class="ml-2 text-sm text-secondary-700 dark:text-secondary-400"
          >
            {{ __("Showing") }}
            <span class="font-semibold dark:text-secondary-300">{{ data.from ?? 0 }}</span>
            {{ __("to") }}
            <span class="font-semibold dark:text-secondary-300">{{ data.to ?? 0 }}</span>
            {{ __("of") }}
            <span class="font-semibold dark:text-secondary-300">{{ data.total }}</span>
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
