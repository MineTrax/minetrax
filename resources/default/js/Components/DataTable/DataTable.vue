<script setup>
import Icon from "@/Components/Icon.vue";
import { Popover, PopoverTrigger, PopoverContent } from "@/Components/ui/popover";
import { XMarkIcon, MagnifyingGlassIcon } from "@heroicons/vue/24/outline";
import Multiselect from "vue-multiselect";
import { reactive, watch } from "vue";
import DtPagination from "./DtPagination.vue";
import { router } from "@inertiajs/vue3";
import { identity, pickBy, throttle } from "lodash";
import { computed } from "vue";

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
                sort: "",
                perPage: "",
                filter: {},
                servers: undefined, // Handle for special server filter for ServerIntel pages.
            };
        },
    },
    routeParams: {
        required: false,
        type: Object,
        default: () => {
            return {};
        },
    },
});

const filters = reactive({
    filter: props.filters.filter ?? { q: "" },
    sort: props.filters.sort ?? "",
    perPage: props.filters.perPage ?? 10,
    servers: props.filters.servers ?? undefined, // Handle for special server filter for ServerIntel pages.
});

// Track popover open state
const popoverOpenStates = reactive({});

watch(
    filters,
    throttle((newParams) => {
        let parsedParams = pickBy(newParams, identity);
        // delete search if not exists
        if (!parsedParams.filter.q) {
            delete parsedParams.filter.q;
        }
        if (parsedParams.perPage == 10) {
            delete parsedParams.perPage;
        }
        router.get(route(route().current(), props.routeParams), parsedParams, {
            replace: true,
            preserveScroll: true,
            preserveState: true,
        });
    }, 200)
);

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
    filters.filter.q = "";
    filters.sort = "";
    filters.perPage = 10;
}

// Sorting
const sortedField = computed(() => {
    if (filters.sort) {
        return filters.sort.replace("-", "");
    }
    return "";
});
const sortedDirection = computed(() => {
    if (filters.sort) {
        return filters.sort.startsWith("-") ? "desc" : "asc";
    }
    return "";
});

function toggleSorting(key) {
    // toggle sorting by logic of -key and key
    if (filters.sort === key) {
        filters.sort = "-" + key;
    } else if (filters.sort === "-" + key) {
        filters.sort = "";
    } else {
        filters.sort = key;
    }
}
</script>

<template>
    <!-- DataTable starts -->
    <div class="flex flex-col">
        <div id="tableHeader" class="flex justify-between p-4">
            <div id="headerLeft" class="flex">
                <div id="searchBox">
                    <div class="relative mt-1">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <MagnifyingGlassIcon class="w-4 h-4 text-muted-foreground stroke-2" />
                        </div>
                        <input
                            id="table-search"
                            v-model="filters.filter.q"
                            type="text"
                            class="block p-2 text-sm text-foreground border border-input rounded-lg pl-9 md:w-80 bg-background focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                            :placeholder="__('Search..')"
                        />
                    </div>
                </div>
            </div>
            <div id="headerRight" class="flex">
                <div v-show="showFilterResetButton" id="resetButton">
                    <button class="hidden px-4 py-1 font-semibold text-secondary-foreground bg-secondary border border-border rounded md:block hover:bg-secondary/80" @click="resetFilters()">
                        <XMarkIcon class="inline-block w-4 h-4 text-secondary-foreground" />
                        {{ __("Reset") }}
                    </button>
                </div>
            </div>
        </div>

        <div id="tableSection" class="flex flex-col">
            <div class="overflow-x-auto">
                <div class="inline-block min-w-full align-middle">
                    <div class="overflow-hidden">
                        <table class="min-w-full divide-y divide-border/50">
                            <thead class="bg-popover">
                                <tr>
                                    <slot name="header">
                                        <th v-for="th in header" :key="th.key" scope="col" class="px-4 py-3 text-xs font-semibold text-left text-muted-foreground" :class="[th.class ? th.class : '']">
                                            <div class="inline-flex items-center">
                                                <Popover v-if="th.filterable" @update:open="popoverOpenStates[th.key] = $event" :open="popoverOpenStates[th.key]">
                                                    <PopoverTrigger class="focus:outline-none">
                                                        <Icon
                                                            v-if="Array.isArray(th.filterable) ? th.filterable.some((filter) => filters.filter[filter.key ?? th.key]) : filters.filter[th.filterable.key ?? th.key]"
                                                            name="funnel-fill"
                                                            class="inline-block h-4 mr-1 text-primary cursor-pointer hover:text-primary/80"
                                                        />
                                                        <Icon v-else name="funnel-outline" class="inline-block h-4 mr-1 text-muted-foreground cursor-pointer hover:text-foreground" />
                                                    </PopoverTrigger>

                                                    <PopoverContent class="min-w-64" align="start" @open-auto-focus.prevent>
                                                        <h3 class="mb-1 text-sm font-semibold">
                                                            {{ Array.isArray(th.filterable) ? th.filterable.title ?? null : th.filterable.title ?? __("Filters for :column", { column: th.label }) }}
                                                        </h3>

                                                        <div>
                                                            <!-- If: Array Filterable -->
                                                            <template v-if="Array.isArray(th.filterable)">
                                                                <div v-for="filter in th.filterable" :key="filter.key ?? th.key" class="mb-4">
                                                                    <h4 class="mb-1 text-sm font-medium">
                                                                        {{ filter.title ?? __("Filters for :column", { column: filter.label }) }}
                                                                    </h4>
                                                                    <input
                                                                        v-if="filter.type === 'text'"
                                                                        v-model="filters.filter[filter.key ?? th.key]"
                                                                        class="block w-full p-2 border-input rounded-md shadow-sm bg-background text-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 sm:text-sm"
                                                                        :placeholder="`Enter ${filter.title ?? filter.label}...`"
                                                                        type="text"
                                                                    />
                                                                    <Multiselect
                                                                        v-if="['multiselect', 'select'].includes(filter.type)"
                                                                        v-model="filters.filter[filter.key ?? th.key]"
                                                                        class="block w-full border-input rounded-md shadow-sm focus:ring-ring focus:border-primary sm:text-sm"
                                                                        :options="filter.options"
                                                                        :multiple="filter.type === 'multiselect'"
                                                                        :close-on-select="filter.type === 'select'"
                                                                        :limit="1"
                                                                        :clear-on-select="false"
                                                                        :searchable="filter.searchable ?? false"
                                                                        :placeholder="`Select ${filter.title ?? filter.label}...`"
                                                                    />
                                                                    <button
                                                                        class="inline-flex w-full justify-center py-1.5 px-4 mt-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-destructive-foreground bg-destructive hover:bg-destructive/90 focus:outline-none disabled:opacity-50"
                                                                        :disabled="!filters.filter[filter.key ?? th.key]"
                                                                        type="button"
                                                                        @click="
                                                                            () => {
                                                                                if (!filters.filter[filter.key ?? th.key]) {
                                                                                    return;
                                                                                }
                                                                                delete filters.filter[filter.key ?? th.key];
                                                                                popoverOpenStates[th.key] = false;
                                                                            }
                                                                        "
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
                                                                    class="block w-full p-2 border-input rounded-md shadow-sm bg-background text-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 sm:text-sm"
                                                                    :placeholder="`Enter ${th.label}...`"
                                                                    type="text"
                                                                />
                                                                <Multiselect
                                                                    v-if="['multiselect', 'select'].includes(th.filterable.type)"
                                                                    v-model="filters.filter[th.filterable.key ?? th.key]"
                                                                    class="block w-full border-input rounded-md shadow-sm focus:ring-ring focus:border-primary sm:text-sm"
                                                                    :options="th.filterable.options"
                                                                    :multiple="th.filterable.type === 'multiselect'"
                                                                    :close-on-select="th.filterable.type === 'select'"
                                                                    :limit="1"
                                                                    :clear-on-select="false"
                                                                    :searchable="th.filterable.searchable ?? false"
                                                                    :placeholder="`Select ${th.label}...`"
                                                                />
                                                                <button
                                                                    class="inline-flex w-full justify-center py-1.5 px-4 mt-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-destructive-foreground bg-destructive hover:bg-destructive/90 focus:outline-none disabled:opacity-50"
                                                                    :disabled="!filters.filter[th.filterable.key ?? th.key]"
                                                                    type="button"
                                                                    @click="
                                                                        () => {
                                                                            if (!filters.filter[th.filterable.key ?? th.key]) {
                                                                                return;
                                                                            }
                                                                            delete filters.filter[th.filterable.key ?? th.key];
                                                                            popoverOpenStates[th.key] = false;
                                                                        }
                                                                    "
                                                                >
                                                                    {{ __("Clear") }}
                                                                </button>
                                                            </template>
                                                        </div>
                                                    </PopoverContent>
                                                </Popover>
                                                <div class="inline-flex items-center uppercase" :class="[th.sortable ? 'cursor-pointer' : '']" @click="th.sortable ? toggleSorting(th.key) : null">
                                                    {{ th.label }}
                                                    <Icon
                                                        v-if="th.sortable"
                                                        :name="sortedField === th.key ? (sortedDirection === 'asc' ? 'sort-up' : 'sort-down') : 'sort-updown'"
                                                        class="inline-block w-3 h-3 ml-1 text-muted-foreground"
                                                        :class="[sortedField === th.key ? 'text-primary' : '']"
                                                    />
                                                </div>
                                            </div>
                                        </th>
                                    </slot>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-border/50">
                                <tr v-for="item in data.data" :key="item.id">
                                    <slot :item="item" :data="data" />
                                </tr>

                                <tr v-if="data.data.length <= 0">
                                    <td :colspan="header.length" class="px-4 py-3 text-sm font-medium text-center text-muted-foreground">
                                        {{ __("No data found") }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div id="tableFooter" class="flex items-center justify-between px-4 py-3 border-t border-border/50">
            <div class="flex justify-between flex-1 sm:hidden">
                <InertiaLink
                    :href="data.prev_page_url ?? '#'"
                    class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-secondary-foreground bg-secondary border border-border rounded-md hover:bg-secondary/80"
                >
                    {{ __("Previous") }}
                </InertiaLink>
                <InertiaLink
                    :href="data.next_page_url ?? '#'"
                    class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-secondary-foreground bg-secondary border border-border rounded-md hover:bg-secondary/80"
                >
                    {{ __("Next") }}
                </InertiaLink>
            </div>
            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                <div class="flex items-center">
                    <div>
                        <select id="perPage" v-model="filters.perPage" class="block w-full text-sm text-foreground border border-input rounded-lg bg-background focus:ring-ring focus:border-primary">
                            <option :value="10" :selected="data.per_page == 10">10 {{ __("per page") }}</option>
                            <option :value="20" :selected="data.per_page == 20">20 {{ __("per page") }}</option>
                            <option :value="50" :selected="data.per_page == 50">50 {{ __("per page") }}</option>
                            <option :value="100" :selected="data.per_page == 100">100 {{ __("per page") }}</option>
                        </select>
                    </div>
                    <p v-if="data.total != undefined" class="ml-2 text-sm text-foreground">
                        {{ __("Showing") }}
                        <span class="font-semibold">{{ data.from ?? 0 }}</span>
                        {{ __("to") }}
                        <span class="font-semibold">{{ data.to ?? 0 }}</span>
                        {{ __("of") }}
                        <span class="font-semibold">{{ data.total }}</span>
                        {{ __("results") }}
                    </p>
                </div>
                <div>
                    <DtPagination v-if="data.next_page_url || data.prev_page_url" :data="data" />
                </div>
            </div>
        </div>
    </div>
    <!-- DataTable ends-->
</template>
