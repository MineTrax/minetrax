<template>
  <app-layout>
    <app-head title="Custom Pages Administration" />

    <div class="py-12 px-10 max-w-7xl mx-auto">
      <div class="flex justify-between mb-8">
        <h1 class="font-bold text-3xl text-gray-500 dark:text-gray-400">
          {{ __("Custom Pages") }}
        </h1>
        <div class="flex">
          <inertia-link
            v-if="can('create custom_pages')"
            :href="route('admin.custom-page.create')"
            class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:shadow-outline-gray transition ease-in-out duration-150"
          >
            <span>{{ __("Create") }}</span>
            <span class="hidden md:inline">&nbsp;{{ __("Custom Page") }}</span>
          </inertia-link>
        </div>
      </div>
      <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
          <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
            <div class="shadow overflow-hidden border-b border-gray-200 dark:border-none sm:rounded-lg">
              <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-cool-gray-800 text-gray-500 dark:text-gray-200">
                  <tr>
                    <th
                      scope="col"
                      class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      {{ __("#") }}
                    </th>
                    <th
                      scope="col"
                      class="w-6 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      {{ __("Title") }}
                    </th>
                    <th
                      scope="col"
                      class="w-6 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      {{ __("Path") }}
                    </th>
                    <th
                      scope="col"
                      class="px-6 py-3 whitespace-nowrap text-left text-xs font-medium uppercase tracking-wider"
                    >
                      {{ __("Page Type") }}
                    </th>
                    <th
                      scope="col"
                      class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      {{ __("Visible") }}
                    </th>
                    <th
                      scope="col"
                      class="px-6 py-3 whitespace-nowrap text-left text-xs font-medium uppercase tracking-wider"
                    >
                      {{ __("In Navbar") }}
                    </th>
                    <th
                      scope="col"
                      class="px-6 py-3 whitespace-nowrap text-left text-xs font-medium uppercase tracking-wider"
                    >
                      {{ __("Sidebar") }}
                    </th>
                    <th
                      scope="col"
                      class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      {{ __("Created") }}
                    </th>
                    <th
                      scope="col"
                      class="relative px-6 py-3"
                    >
                      <span class="sr-only">{{ __("Actions") }}</span>
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white dark:bg-cool-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                  <tr
                    v-for="customPage in customPages.data"
                    :key="customPage.id"
                  >
                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                      {{ customPage.id }}
                    </td>
                    <td class="px-6 py-4 whitespace-normal w-1/2">
                      <div class="flex items-center">
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-300">
                          {{ customPage.title }}
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-normal w-1/2">
                      <div class="flex items-center">
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-300">
                          {{ customPage.path }}
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-normal w-1/2">
                      <div class="flex items-center">
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-300">
                          {{ customPage.is_redirect ? 'redirect' : customPage.is_html_page ? 'html' : 'markdown' }}
                        </div>
                      </div>
                    </td>
                    <td class="px-9 py-4 text-sm text-gray-500">
                      <icon
                        v-if="customPage.is_visible"
                        class="text-green-500 focus:outline-none"
                        name="check-circle"
                      />
                      <icon
                        v-else
                        class="text-red-500"
                        name="cross-circle"
                      />
                    </td>
                    <td class="px-9 py-4 whitespace-nowrap text-sm text-gray-500 align-middle text-center">
                      <icon
                        v-if="customPage.is_in_navbar"
                        class="text-green-500"
                        name="check-circle"
                      />
                      <icon
                        v-else
                        class="text-red-500"
                        name="cross-circle"
                      />
                    </td>
                    <td class="px-9 py-4 whitespace-nowrap text-sm text-gray-500 align-middle text-center">
                      <icon
                        v-if="customPage.is_sidebar_visible"
                        class="text-green-500 focus:outline-none"
                        name="check-circle"
                      />
                      <icon
                        v-else
                        class="text-red-500"
                        name="cross-circle"
                      />
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                      <span
                        v-tippy
                        class="focus:outline-none"
                        :content="formatToDayDateString(customPage.created_at)"
                      >
                        {{ formatTimeAgoToNow(customPage.created_at) }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap dark:text-gray-400 text-right text-sm font-medium">
                      <inertia-link
                        as="a"
                        :href="route('custom-page.show', customPage.path)"
                        class="text-blue-600 hover:text-blue-900"
                      >
                        {{ __("View") }}
                      </inertia-link>
                      /
                      <inertia-link
                        v-if="can('update news')"
                        as="a"
                        :href="route('admin.custom-page.edit', customPage.id)"
                        class="text-yellow-600 hover:text-yellow-900"
                      >
                        {{ __("Edit") }}
                      </inertia-link>
                      /
                      <button
                        v-if="can('delete custom_pages')"
                        class="text-red-600 hover:text-red-900 focus:outline-none"
                        @click="confirmCustomPageDeletion(customPage.id)"
                      >
                        {{ __("Delete") }}
                      </button>
                    </td>
                  </tr>

                  <tr v-if="customPages.data.length === 0">
                    <td
                      class="border-t px-6 py-4 text-center"
                      colspan="7"
                    >
                      {{ __("No custom pages found.") }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <pagination :links="customPages.links" />
    </div>

    <jet-confirmation-modal
      :show="!!customPageBeingDeleted"
      @close="customPageBeingDeleted = null"
    >
      <template #title>
        {{ __("Delete Page") }}
      </template>

      <template #content>
        {{ __("Are you sure you would like to delete this Page?") }}
      </template>

      <template #footer>
        <jet-secondary-button @click="customPageBeingDeleted = null">
          {{ __("Nevermind") }}
        </jet-secondary-button>

        <jet-danger-button
          class="ml-2"
          :class="{ 'opacity-25': deleteCustomPageForm.processing }"
          :disabled="deleteCustomPageForm.processing"
          @click="deleteNews"
        >
          {{ __("Delete Page") }}
        </jet-danger-button>
      </template>
    </jet-confirmation-modal>
  </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import Icon from '@/Components/Icon.vue';
import JetConfirmationModal from '@/Jetstream/ConfirmationModal.vue';
import JetSecondaryButton from '@/Jetstream/SecondaryButton.vue';
import JetDangerButton from '@/Jetstream/DangerButton.vue';

export default {

    components: {
        Icon,
        AppLayout,
        Pagination,
        JetConfirmationModal,
        JetSecondaryButton,
        JetDangerButton
    },
    props: {
        customPages: Object
    },

    data() {
        return {
            deleteCustomPageForm: this.$inertia.form(),
            customPageBeingDeleted: null
        };
    },

    methods: {
        confirmCustomPageDeletion(id) {
            this.customPageBeingDeleted = id;
        },

        deleteNews() {
            this.deleteCustomPageForm.delete(route('admin.custom-page.delete', this.customPageBeingDeleted), {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => (this.customPageBeingDeleted = null),
            });
        },
    },
};
</script>
