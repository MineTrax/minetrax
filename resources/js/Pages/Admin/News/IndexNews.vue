<template>
  <app-layout>
    <app-head title="News Administration" />

    <div class="py-12 px-10 max-w-7xl mx-auto">
      <div class="flex justify-between mb-8">
        <h1 class="font-bold text-3xl text-gray-500 dark:text-gray-400">
          {{ __("News") }}
        </h1>
        <div class="flex">
          <inertia-link
            v-if="can('create news')"
            :href="route('admin.news.create')"
            class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:shadow-outline-gray transition ease-in-out duration-150"
          >
            <span>{{ __("Create") }}</span>
            <span class="hidden md:inline">&nbsp;{{ __("News") }}</span>
          </inertia-link>
        </div>
      </div>
      <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
          <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
            <div class="shadow overflow-hidden border-b border-gray-200 dark:border-none sm:rounded-lg">
              <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-cool-gray-800 text-gray-500 dark:text-gray-300">
                  <tr>
                    <th
                      scope="col"
                      class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      {{ __("#") }}
                    </th>
                    <th
                      scope="col"
                      class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      {{ __("Type") }}
                    </th>
                    <th
                      scope="col"
                      class="w-6 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      {{ __("Title") }}
                    </th>
                    <th
                      scope="col"
                      class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      {{ __("Published") }}
                    </th>
                    <th
                      scope="col"
                      class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      {{ __("Pinned") }}
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
                      <span class="sr-only">{{ __("Edit") }}</span>
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white dark:bg-cool-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                  <tr
                    v-for="news in newslist.data"
                    :key="news.id"
                  >
                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                      {{ news.id }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900">
                        <span
                          v-if="news.type.value === 0"
                          class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-light-blue-100 text-light-blue-800 dark:bg-light-blue-700 dark:bg-opacity-25 dark:text-light-blue-400"
                        >{{ news.type.key }}</span>
                        <span
                          v-else-if="news.type.value === 1"
                          class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800 dark:bg-orange-700 dark:bg-opacity-25 dark:text-orange-400"
                        >{{ news.type.key }}</span>
                        <span
                          v-else-if="news.type.value === 2"
                          class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-700 dark:bg-opacity-25 dark:text-green-400"
                        >{{ news.type.key }}</span>
                        <span
                          v-else
                          class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800"
                        >{{ news.type.key }}</span>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-normal w-1/2">
                      <div class="flex items-center">
                        <div
                          v-if="news.photo_url"
                          class="flex-shrink-0 h-10 w-14"
                        >
                          <img
                            class="h-10 w-14"
                            :src="news.photo_url"
                            alt=""
                          >
                        </div>
                        <div class="ml-4">
                          <div class="text-sm font-medium text-gray-900 dark:text-gray-300">
                            {{ news.title }}
                          </div>
                        </div>
                      </div>
                    </td>
                    <td class="px-12 py-4 text-sm text-gray-500">
                      <icon
                        v-if="news.published_at"
                        v-tippy
                        :content="formatTimeAgoToNow(news.published_at)"
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
                        v-if="news.is_pinned"
                        class="text-green-500"
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
                        :content="formatToDayDateString(news.created_at)"
                      >
                        {{ formatTimeAgoToNow(news.created_at) }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right dark:text-gray-300 text-sm font-medium">
                      <inertia-link
                        as="a"
                        :href="route('news.show', news.slug)"
                        class="text-blue-600 hover:text-blue-900"
                      >
                        {{ __("View") }}
                      </inertia-link>
                      /
                      <inertia-link
                        v-if="can('update news')"
                        as="a"
                        :href="route('admin.news.edit', news.id)"
                        class="text-yellow-600 hover:text-yellow-900"
                      >
                        {{ __("Edit") }}
                      </inertia-link>
                      /
                      <button
                        v-if="can('delete news')"
                        class="text-red-600 hover:text-red-900 focus:outline-none"
                        @click="confirmNewsDeletion(news.id)"
                      >
                        {{ __("Delete") }}
                      </button>
                    </td>
                  </tr>

                  <tr v-if="newslist.data.length === 0">
                    <td
                      class="px-6 py-4 text-center dark:text-gray-400"
                      colspan="7"
                    >
                      {{ __("No news found.") }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <pagination :links="newslist.links" />
    </div>

    <jet-confirmation-modal
      :show="!!newsBeingDeleted"
      @close="newsBeingDeleted = null"
    >
      <template #title>
        {{ __("Delete News") }}
      </template>

      <template #content>
        {{ __("Are you sure you would like to delete this News?") }}
      </template>

      <template #footer>
        <jet-secondary-button @click="newsBeingDeleted = null">
          {{ __("Nevermind") }}
        </jet-secondary-button>

        <jet-danger-button
          class="ml-2"
          :class="{ 'opacity-25': deleteNewsForm.processing }"
          :disabled="deleteNewsForm.processing"
          @click="deleteNews"
        >
          {{ __("Delete News") }}
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
import { useForm } from '@inertiajs/vue3';

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
        newslist: Object
    },

    data() {
        return {
            deleteNewsForm: useForm({}),
            newsBeingDeleted: null
        };
    },

    methods: {
        confirmNewsDeletion(id) {
            this.newsBeingDeleted = id;
        },

        deleteNews() {
            this.deleteNewsForm.delete(route('admin.news.delete', this.newsBeingDeleted), {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => (this.newsBeingDeleted = null),
            });
        },
    },
};
</script>
