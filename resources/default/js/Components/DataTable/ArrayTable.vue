<script setup>
import Icon from '@/Components/Icon.vue';
import axios from 'axios';
import { ref, computed, onMounted } from 'vue';
import LoadingSpinner from '../LoadingSpinner.vue';

const props = defineProps({
    data: {
        required: false,
        default: () => {
            return [];
        },
        type: Array,
    },
    url: {
        required: false,
        type: String,
    },
    header: {
        required: true,
        type: Array,
    }
});

const loading = ref(true);
const finalData = ref([]);

// If URL is provided, fetch data from the URL
onMounted(() => {
    if (props.url) {
        loading.value = true;
        axios.get(props.url)
            .then(response => {
                finalData.value = response.data.data;
            })
            .catch(error => {
                console.error('Error fetching data', error);
            }).finally(() => {
                loading.value = false;
            });
    } else {
        finalData.value = props.data;
    }
});

// Local state to handle sorting
const sortKey = ref('');
const sortDirection = ref('');

const sortedData = computed(() => {
    let sorted = [...finalData.value];

    // Sort data
    if (sortKey.value) {
        sorted.sort((a, b) => {
            let aValue = getNestedValue(a, sortKey.value);
            let bValue = getNestedValue(b, sortKey.value);

            // Handle undefined values
            if (aValue === undefined && bValue === undefined) return 0;
            if (aValue === undefined) return 1;
            if (bValue === undefined) return -1;

            let result = 0;
            if (typeof aValue === 'string') {
                result = aValue.localeCompare(bValue);
            } else {
                if (aValue < bValue) result = -1;
                if (aValue > bValue) result = 1;
            }

            return sortDirection.value === 'desc' ? -result : result;
        });
    }

    return sorted;
});

// Helper function to get nested object values
function getNestedValue(obj, key) {
    return key.split('.').reduce((o, k) => (o || {})[k], obj);
}

// Sorting logic
function toggleSorting(key) {
    if (sortKey.value === key) {
        if (sortDirection.value === 'asc') {
            sortDirection.value = 'desc';
        } else {
            sortKey.value = '';
            sortDirection.value = '';
        }
    } else {
        sortKey.value = key;
        sortDirection.value = 'asc';
    }
}

// Computed properties for sorting icons and states
const sortedField = computed(() => {
    return sortKey.value ? sortKey.value.replace('-', '') : '';
});

const sortedDir = computed(() => {
    return sortDirection.value === 'desc' ? 'desc' : 'asc';
});
</script>

<template>
  <!-- DataTable starts -->
  <div class="flex flex-col">
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
                      :class="[ th.class ? th.class : '' ]"
                    >
                      <div class="inline-flex items-center">
                        <div
                          class="inline-flex items-center uppercase cursor-pointer"
                          @click="th.sortable ? toggleSorting(th.key) : null"
                        >
                          {{ th.label }}
                          <Icon
                            v-if="th.sortable"
                            :name="sortedField === th.key ? (sortedDir === 'asc' ? 'sort-up' : 'sort-down') : 'sort-updown'"
                            class="inline-block w-3 h-3 ml-1 text-secondary-400 dark:text-secondary-300"
                            :class="[ sortedField === th.key ? 'text-primary-500 dark:text-primary-400' : '' ]"
                          />
                        </div>
                      </div>
                    </th>
                  </slot>
                </tr>
              </thead>
              <tbody
                v-if="!loading"
                class="divide-y divide-secondary-200 dark:divide-secondary-700"
              >
                <tr
                  v-for="item in sortedData"
                  :key="item.id"
                >
                  <slot
                    :item="item"
                    :data="sortedData"
                  >
                    <td
                      v-for="column in header"
                      :key="column.key"
                      class="px-4 py-3 text-sm font-medium text-secondary-900 dark:text-secondary-100"
                    >
                      {{ item[column.key] }}
                    </td>
                  </slot>
                </tr>

                <tr v-if="sortedData.length <= 0">
                  <td
                    :colspan="header.length"
                    class="px-4 py-3 text-sm font-medium text-center text-secondary-500 dark:text-secondary-300"
                  >
                    {{ __("No data found") }}
                  </td>
                </tr>
              </tbody>

              <tfoot
                v-if="loading"
              >
                <tr>
                  <td
                    :colspan="header.length"
                    class="px-4 py-3 text-sm font-medium text-center text-secondary-500 dark:text-secondary-300"
                  >
                    <div
                      v-if="loading"
                      class="flex items-center justify-center"
                    >
                      <LoadingSpinner
                        class="w-6 h-6"
                        :loading="loading"
                      />
                    </div>
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- DataTable ends-->
</template>
