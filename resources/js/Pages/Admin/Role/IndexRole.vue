<template>
  <app-layout>
    <app-head title="User Roles Administration" />

    <div class="py-12 px-10 max-w-7xl mx-auto">
      <div class="flex justify-between mb-8">
        <h1 class="font-bold text-3xl text-gray-500 dark:text-gray-300">
          {{ __("User Roles & Permissions") }}
        </h1>
        <div class="flex">
          <inertia-link
            v-if="can('create roles')"
            :href="route('admin.role.create')"
            class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:shadow-outline-gray transition ease-in-out duration-150"
          >
            <span>{{ __("Create New") }}</span>
            <span class="hidden md:inline">&nbsp;{{ __("Role") }}</span>
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
                      {{ __("Image") }}
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
                      {{ __("Permissions") }}
                    </th>
                    <th
                      scope="col"
                      class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      {{ __("Users") }}
                    </th>
                    <th
                      scope="col"
                      class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      {{ __("Staff") }}
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
                    v-for="role in roles.data"
                    :key="role.id"
                  >
                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                      {{ role.id }}
                    </td>
                    <td class="whitespace-nowrap">
                      <div class="flex-shrink-0">
                        <img
                          class=""
                          :src="role.photo_url"
                          :alt="__('Role Image')"
                        >
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="items-center">
                        <div
                          class="text-sm font-semibold text-gray-900 dark:text-gray-300"
                          :style="[role.color ? {color: role.color} : null]"
                        >
                          {{ role.display_name }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                          {{ role.name }}
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 text-sm">
                      <template v-if="role.permissions.length > 0">
                        <span
                          v-for="permission in role.permissions"
                          :key="permission.id"
                          class="px-2 mr-1 mb-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-opacity-10 dark:text-light-blue-400"
                        >{{ permission.name }}</span>
                      </template>
                      <span
                        v-else
                        class="italic text-gray-500"
                      >{{ __("No administration permissions for this role.") }}</span>

                      <span
                        v-if="role.name === 'superadmin'"
                        class="px-2 mr-1 mb-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-opacity-10 dark:text-green-400"
                      >{{ __("All Permissions") }}</span>
                    </td>
                    <td class="px-8 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300 align-middle text-center">
                      {{ role.users_count }}
                    </td>
                    <td class="px-8 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300 align-middle text-center">
                      <icon
                        v-if="role.is_staff"
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
                        :content="formatToDayDateString(role.created_at)"
                      >
                        {{ formatTimeAgoToNow(role.created_at) }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right dark:text-gray-300 text-sm font-medium">
                      <inertia-link
                        v-if="can('update roles')"
                        as="a"
                        :href="route('admin.role.edit', role.id)"
                        class="text-yellow-600 hover:text-yellow-900"
                      >
                        {{ __("Edit") }}
                      </inertia-link>
                      /
                      <button
                        v-if="can('delete roles')"
                        class="text-red-600 hover:text-red-900 focus:outline-none"
                        @click="confirmRoleDeletion(role.id)"
                      >
                        {{ __("Delete") }}
                      </button>
                    </td>
                  </tr>

                  <tr v-if="roles.data.length === 0">
                    <td
                      class="border-t px-6 py-4 text-center dark:text-gray-400"
                      colspan="7"
                    >
                      {{ __("No roles found.") }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <pagination :links="roles.links" />
    </div>

    <jet-confirmation-modal
      :show="!!roleBeingDeleted"
      @close="roleBeingDeleted = null"
    >
      <template #title>
        {{ __("Delete Role") }}
      </template>

      <template #content>
        {{ __("Are you sure you would like to delete this Role? It is not reversible.") }}
      </template>

      <template #footer>
        <jet-secondary-button @click.native="roleBeingDeleted = null">
          {{ __("Nevermind") }}
        </jet-secondary-button>

        <jet-danger-button
          class="ml-2"
          :class="{ 'opacity-25': deleteRoleForm.processing }"
          :disabled="deleteRoleForm.processing"
          @click.native="deleteRole"
        >
          {{ __("Delete Role") }}
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
        AppLayout,
        Pagination,
        Icon,
        JetConfirmationModal,
        JetSecondaryButton,
        JetDangerButton
    },
    props: {
        roles: Object
    },

    data() {
        return {
            deleteRoleForm: this.$inertia.form(),
            roleBeingDeleted: null
        };
    },

    methods: {
        confirmRoleDeletion(id) {
            this.roleBeingDeleted = id;
        },

        deleteRole() {
            this.deleteRoleForm.delete(route('admin.role.delete', this.roleBeingDeleted), {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => (this.roleBeingDeleted = null),
            });
        },
    },
};
</script>
