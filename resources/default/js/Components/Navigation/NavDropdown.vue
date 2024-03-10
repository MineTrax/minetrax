<template>
  <div
    class="inline-flex items-center px-1 pt-1 text-sm leading-5 text-gray-500 transition duration-150 ease-in-out border-b-2 border-transparent hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300"
  >
    <jet-dropdown
      align="right"
      width="48"
    >
      <template #trigger>
        <span class="inline-flex rounded-md">
          <button
            type="button"
            class="inline-flex items-center py-2 text-sm font-semibold leading-4 text-gray-500 transition duration-150 ease-in-out border border-transparent rounded-md dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 focus:outline-none"
          >
            {{ __(title) }}
            <svg
              class="ml-2 -mr-0.5 h-4 w-4"
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 20 20"
              fill="currentColor"
            >
              <path
                fill-rule="evenodd"
                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                clip-rule="evenodd"
              />
            </svg>
          </button>
        </span>
      </template>

      <template #content>
        <jet-dropdown-link
          v-for="item in items"
          :key="item.key"
          class="text-sm"
          :href="route(item.route, item.route_params ?? null)"
          :open-in-new-tab="item.is_open_in_new_tab"
        >
          {{ __(item.title) }}
        </jet-dropdown-link>
      </template>
    </jet-dropdown>
  </div>
</template>

<script>
import JetDropdown from '@/Jetstream/Dropdown.vue';
import JetDropdownLink from '@/Jetstream/DropdownLink.vue';

export default {
    components: {
        JetDropdown,
        JetDropdownLink,
    },
    props: {
        title: {
            type: String,
            required: true
        },
        items: {
            type: Array,
            required: true
        }
    },
};
</script>

