<template>
  <app-layout>
    <app-head title="Polls Administration" />

    <div class="py-12 px-10 max-w-7xl mx-auto">
      <div class="flex justify-between mb-8">
        <h1 class="font-bold text-3xl text-gray-500 dark:text-gray-400">
          {{ __("Polls") }}
        </h1>
        <div class="flex">
          <inertia-link
            v-if="can('create polls')"
            :href="route('admin.poll.create')"
            class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:shadow-outline-gray transition ease-in-out duration-150"
          >
            <span>{{ __("Create New") }}</span>
            <span class="hidden md:inline">&nbsp;{{ __("Poll") }}</span>
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
                      class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      {{ __("Question") }}
                    </th>
                    <th
                      scope="col"
                      class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      {{ __("Options") }}
                    </th>
                    <th
                      scope="col"
                      class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      {{ __("Started At") }}
                    </th>
                    <th
                      scope="col"
                      class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      {{ __("End At") }}
                    </th>
                    <th
                      scope="col"
                      class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      {{ __("Created At") }}
                    </th>
                    <th
                      scope="col"
                      class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      {{ __("Created By") }}
                    </th>
                    <th
                      scope="col"
                      class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      {{ __("Locked") }}
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
                    v-for="poll in polls.data"
                    :key="poll.id"
                  >
                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                      {{ poll.id }}
                    </td>
                    <td class="px-3 py-4">
                      <div class="items-center">
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-300">
                          {{ poll.question }}
                        </div>
                      </div>
                    </td>
                    <td class="px-3 py-4 text-sm">
                      <template v-if="poll.options.length > 0">
                        <span
                          v-for="option in poll.options"
                          class="px-2 mr-1 mb-1 inline-flex text-xs leading-5 font-semibold rounded bg-gray-100 text-gray-800 dark:bg-cool-gray-700 dark:text-gray-300"
                        >{{ option.name }}</span>
                      </template>
                      <span
                        v-else
                        class="italic text-gray-500"
                      >{{ __("No options.") }}</span>
                    </td>
                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                      <span
                        v-tippy
                        class="focus:outline-none"
                        :content="formatToDayDateString(poll.started_at)"
                      >
                        {{ formatTimeAgoToNow(poll.started_at) }}
                      </span>
                    </td>
                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                      <span
                        v-if="poll.closed_at"
                        v-tippy
                        class="focus:outline-none"
                        :content="formatToDayDateString(poll.closed_at)"
                      >
                        {{ formatTimeAgoToNow(poll.closed_at) }}
                      </span>
                      <span
                        v-else
                        class="italic"
                      >{{ __("None") }}</span>
                    </td>
                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                      <span
                        v-tippy
                        class="focus:outline-none"
                        :content="formatToDayDateString(poll.created_at)"
                      >
                        {{ formatTimeAgoToNow(poll.created_at) }}
                      </span>
                    </td>
                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                      <span v-if="poll.creator">{{ poll.creator.username }}</span>
                      <span
                        v-else
                        class="italic text-red-600"
                      >{{ __("None") }}</span>
                    </td>
                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500 align-middle text-center">
                      <icon
                        v-if="poll.is_closed"
                        class="text-green-500"
                        name="check-circle"
                      />
                      <icon
                        v-else
                        class="text-red-500"
                        name="cross-circle"
                      />
                    </td>
                    <td class="px-3 py-4 whitespace-nowrap text-right text-sm font-medium dark:text-gray-400">
                      <inertia-link
                        v-if="can('update polls') && !poll.is_closed"
                        as="a"
                        method="put"
                        :href="route('admin.poll.lock', poll.id)"
                        class="text-yellow-600 hover:text-yellow-900"
                      >
                        {{ __("Lock") }}
                      </inertia-link>
                      <inertia-link
                        v-if="can('update polls') && poll.is_closed"
                        as="a"
                        method="put"
                        :href="route('admin.poll.unlock', poll.id)"
                        class="text-green-600 hover:text-green-900"
                      >
                        {{ __("Unlock") }}
                      </inertia-link>
                      /
                      <button
                        v-if="can('delete polls')"
                        class="text-red-600 hover:text-red-900 focus:outline-none"
                        @click="confirmRoleDeletion(poll.id)"
                      >
                        {{ __("Delete") }}
                      </button>
                    </td>
                  </tr>

                  <tr v-if="polls.data.length === 0">
                    <td
                      class="px-6 py-4 text-center dark:text-gray-400"
                      colspan="9"
                    >
                      {{ __("No polls found.") }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <pagination :links="polls.links" />
    </div>

    <jet-confirmation-modal
      :show="pollIsBeingDeleted"
      @close="pollIsBeingDeleted = null"
    >
      <template #title>
        {{ __("Delete Poll") }}
      </template>

      <template #content>
        {{ __("Are you sure you would like to delete this Poll? It is not reversible.") }}
      </template>

      <template #footer>
        <jet-secondary-button @click.native="pollIsBeingDeleted = null">
          {{ __("Nevermind") }}
        </jet-secondary-button>

        <jet-danger-button
          class="ml-2"
          :class="{ 'opacity-25': deletePollForm.processing }"
          :disabled="deletePollForm.processing"
          @click.native="deletePoll"
        >
          {{ __("Delete Poll") }}
        </jet-danger-button>
      </template>
    </jet-confirmation-modal>
  </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout';
import Pagination from '@/Components/Pagination';
import Icon from '@/Components/Icon';
import JetConfirmationModal from '@/Jetstream/ConfirmationModal';
import JetSecondaryButton from '@/Jetstream/SecondaryButton';
import JetDangerButton from '@/Jetstream/DangerButton';

export default {

    components: {
        AppLayout,
        Pagination,
        Icon,
        JetConfirmationModal,
        JetSecondaryButton,
        JetDangerButton
    },
    props: {
        polls: Object
    },

    data() {
        return {
            deletePollForm: this.$inertia.form(),
            pollIsBeingDeleted: null
        };
    },

    methods: {
        confirmRoleDeletion(id) {
            this.pollIsBeingDeleted = id;
        },

        deletePoll() {
            this.deletePollForm.delete(route('admin.poll.delete', this.pollIsBeingDeleted), {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => (this.pollIsBeingDeleted = null),
            });
        },
    },
};
</script>
