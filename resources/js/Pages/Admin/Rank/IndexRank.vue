<template>
  <AdminLayout>
    <app-head :title="__('Player Ranks Administration')" />

    <div class="py-12 px-10 max-w-7xl mx-auto">
      <div class="flex justify-between mb-8">
        <h1 class="font-bold text-3xl text-gray-500 dark:text-gray-300">
          {{ __("Player Ranks") }}
        </h1>
        <div class="flex">
          <inertia-link
            v-if="can('update ranks')"
            v-confirm="{message: 'Are you sure you want to Reset all Ranks? This will remove current rank of all players.'}"
            method="post"
            as="button"
            :href="route('admin.rank.reset')"
            class="mr-2 inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:shadow-outline-red transition ease-in-out duration-150"
          >
            {{ __("Reset to Default Ranks") }}
          </inertia-link>
          <inertia-link
            v-if="can('create ranks')"
            :href="route('admin.rank.create')"
            class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:shadow-outline-gray transition ease-in-out duration-150"
          >
            <span>{{ __("Create New") }}</span>
            <span class="hidden md:inline">&nbsp;{{ __("Rank") }}</span>
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
                      {{ __("Name") }}
                    </th>
                    <th
                      scope="col"
                      class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      {{ __("Play Time Needed") }}
                    </th>
                    <th
                      scope="col"
                      class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      {{ __("Score Needed") }}
                    </th>
                    <th
                      scope="col"
                      class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      {{ __("Player Count") }}
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
                    v-for="(rank, index) in ranks.data"
                    :key="index"
                  >
                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                      {{ (index)+ranks.from }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                          <img
                            class="h-10 w-10"
                            :src="rank.photo_url"
                            alt=""
                          >
                        </div>
                        <div class="ml-4">
                          <div class="text-sm font-medium text-gray-900 dark:text-gray-300">
                            {{ rank.name }}
                          </div>
                          <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ rank.shortname }}
                          </div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900 dark:text-gray-300">
                        {{ rank.total_play_one_minute_needed }}
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                      {{ rank.total_score_needed }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                      {{ rank.players_count }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                      {{ formatTimeAgoToNow(rank.created_at) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium dark:text-gray-400">
                      <a
                        href="#"
                        class="text-blue-600 hover:text-blue-900"
                      >{{ __("View") }}</a>
                      /
                      <inertia-link
                        v-if="can('update ranks')"
                        as="a"
                        :href="route('admin.rank.edit', rank.id)"
                        class="text-yellow-600 hover:text-yellow-900"
                      >
                        {{ __("Edit") }}
                      </inertia-link>
                      /
                      <button
                        v-if="can('delete ranks')"
                        class="text-red-600 hover:text-red-900 focus:outline-none"
                        @click="confirmRankDeletion(rank.id)"
                      >
                        {{ __("Delete") }}
                      </button>
                    </td>
                  </tr>

                  <tr v-if="ranks.data.length === 0">
                    <td
                      class="border-t dark:border-gray-700 px-6 py-4 text-center"
                      colspan="7"
                    >
                      {{ __("No ranks found.") }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <pagination :links="ranks.links" />
    </div>

    <jet-confirmation-modal
      :show="!!rankBeingDeleted"
      @close="rankBeingDeleted = null"
    >
      <template #title>
        {{ __("Delete Rank") }}
      </template>

      <template #content>
        {{ __("Are you sure you would like to delete this Rank?") }}
      </template>

      <template #footer>
        <jet-secondary-button @click="rankBeingDeleted = null">
          {{ __("Nevermind") }}
        </jet-secondary-button>

        <jet-danger-button
          class="ml-2"
          :class="{ 'opacity-25': deleteRankForm.processing }"
          :disabled="deleteRankForm.processing"
          @click="deleteNews"
        >
          {{ __("Delete Rank") }}
        </jet-danger-button>
      </template>
    </jet-confirmation-modal>
  </AdminLayout>
</template>

<script>
import Pagination from '@/Components/Pagination.vue';
import JetConfirmationModal from '@/Jetstream/ConfirmationModal.vue';
import JetSecondaryButton from '@/Jetstream/SecondaryButton.vue';
import JetDangerButton from '@/Jetstream/DangerButton.vue';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {useAuthorizable} from '@/Composables/useAuthorizable';
import { useHelpers } from '@/Composables/useHelpers';

export default {

    components: {
        AdminLayout,
        Pagination,
        JetConfirmationModal,
        JetSecondaryButton,
        JetDangerButton
    },
    props: {
        ranks: Object
    },
    setup() {
        const {can} = useAuthorizable();
        const {formatTimeAgoToNow, formatToDayDateString} = useHelpers();
        return {can, formatTimeAgoToNow, formatToDayDateString};
    },
    data() {
        return {
            deleteRankForm: useForm({}),
            rankBeingDeleted: null
        };
    },
    methods: {
        confirmRankDeletion(id) {
            this.rankBeingDeleted = id;
        },

        deleteNews() {
            this.deleteRankForm.delete(route('admin.rank.delete', this.rankBeingDeleted), {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => (this.rankBeingDeleted = null),
            });
        },
    },
};
</script>
