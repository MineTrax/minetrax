<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useAuthorizable } from '@/Composables/useAuthorizable';
import { useHelpers } from '@/Composables/useHelpers';
import { useTranslations } from '@/Composables/useTranslations';
import DataTable from '@/Components/DataTable/DataTable.vue';
import DtRowItem from '@/Components/DataTable/DtRowItem.vue';
import UserDisplayname from '@/Components/UserDisplayname.vue';
import {
    EyeIcon,
    ArrowUpOnSquareStackIcon,
    PencilSquareIcon,
    TrashIcon,
    BellSlashIcon,
    NoSymbolIcon,
} from '@heroicons/vue/24/outline';

const { can } = useAuthorizable();
const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString } = useHelpers();

defineProps({
    users: Object,
    filters: Object,
});

const headerRow = [
    {
        key: 'id',
        label: __('ID'),
        sortable: true,
        class: 'text-center',
    },
    {
        key: 'flag',
        label: __('Flag'),
    },
    {
        key: 'avatar',
        label: __('Avatar'),
    },
    {
        key: 'name',
        label: __('Name'),
        sortable: true,
        class: 'w-3/12',
    },
    {
        key: 'username',
        label: __('Username'),
        sortable: true,
        class: 'w-2/12',
    },
    {
        key: 'email',
        label: __('Email'),
        sortable: true,
        class: 'w-2/12',
    },
    {
        key: 'created_at',
        label: __('Joined'),
        sortable: true,
        class: 'w-2/12',
    },
    {
        key: 'role_id',
        label: __('Role'),
        sortable: false,
        class: 'w-2/12',
    },
    {
        key: 'flags',
        label: __('Flags'),
        sortable: false,
        class: 'w-1/12',
    },
    {
        key: 'actions',
        label: __('Actions'),
        sortable: false,
        class: 'w-2/12 text-right',
    },
];
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Users Administration')" />

    <div class="px-10 py-8 mx-auto text-gray-400">
      <div class="flex justify-between mb-4">
        <h1 class="text-3xl font-bold text-gray-500 dark:text-gray-300">
          {{ __("Users") }}
        </h1>
      </div>

      <DataTable
        class="bg-white rounded shadow dark:bg-gray-800"
        :header="headerRow"
        :data="users"
        :filters="filters"
      >
        <template #default="{ item }">
          <td
            class="px-4 py-4 text-sm font-medium text-center text-gray-800 whitespace-nowrap dark:text-gray-200"
          >
            {{ item.id }}
          </td>
          <td>
            <img
              v-tippy
              class="w-10 h-10 mx-auto"
              :src="item.country.photo_path"
              alt="Avatar"
              :title="item.country.name"
            >
          </td>

          <td>
            <div class="flex items-center justify-center">
              <img
                class="w-10 h-10 rounded-full"
                :src="item.profile_photo_url"
                alt="Avatar"
              >
            </div>
          </td>

          <td class="px-4 whitespace-nowrap">
            <div
              class="text-sm font-semibold text-gray-900 dark:text-gray-300"
              :style="[
                item.roles[0].color
                  ? { color: item.roles[0].color }
                  : null,
              ]"
            >
              <InertiaLink :href="route('user.public.get', item.username)">
                <UserDisplayname
                  :user="item"
                  icon-class="w-4 h-4"
                  text-class="text-sm"
                />
              </InertiaLink>
            </div>
          </td>

          <DtRowItem>
            {{ item.username }}
          </DtRowItem>

          <DtRowItem>
            {{ item.email }}
          </DtRowItem>
          <DtRowItem class="whitespace-nowrap">
            <span
              v-tippy
              :title="formatToDayDateString(item.created_at)"
            >
              {{ formatTimeAgoToNow(item.created_at) }}
            </span>
          </DtRowItem>
          <DtRowItem>
            {{ item.roles[0].display_name }}
          </DtRowItem>

          <td class="px-4 py-3 space-x-1 text-sm font-medium whitespace-nowrap">
            <span
              v-if="item.muted_at"
              v-tippy
              class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-lg bg-orange-100 text-orange-800 dark:bg-orange-700 dark:bg-opacity-25 dark:text-orange-400"
              :title="__('Muted')"
            >
              <BellSlashIcon class="inline-block w-4 h-4" />
            </span>
            <span
              v-if="item.banned_at"
              v-tippy
              class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-lg bg-red-100 text-red-800 dark:bg-red-700 dark:bg-opacity-25 dark:text-red-400"
              :title="__('Banned')"
            >
              <NoSymbolIcon class="inline-block w-4 h-4" />
            </span>

            <span
              v-if="!item.muted_at && !item.banned_at"
              class="text-xs italic"
            >{{ __("None") }}</span>
          </td>

          <td class="px-6 py-4 space-x-2 text-sm font-medium text-right whitespace-nowrap">
            <InertiaLink
              v-tippy
              as="a"
              :href="route('user.public.get', item.username)"
              class="inline-flex items-center justify-center text-blue-500 hover:text-blue-800"
              :title="__('View Profile')"
            >
              <EyeIcon class="inline-block w-5 h-5" />
            </InertiaLink>
            <InertiaLink
              v-tippy
              as="a"
              :href="route('admin.impersonate.take', item.id)"
              class="inline-flex items-center justify-center text-orange-500 hover:text-orange-800"
              :title="__('Impersonate User')"
            >
              <ArrowUpOnSquareStackIcon class="inline-block w-5 h-5" />
            </InertiaLink>
            <InertiaLink
              v-if="can('update users')"
              v-tippy
              as="a"
              :href="route('admin.user.edit', item.id)"
              class="inline-flex items-center justify-center text-yellow-600 dark:text-yellow-500 hover:text-yellow-800 dark:hover:text-yellow-800"
              :title="__('Edit User')"
            >
              <PencilSquareIcon class="inline-block w-5 h-5" />
            </InertiaLink>
            <InertiaLink
              v-if="can('delete users')"
              v-confirm="{
                message:
                  'Are you sure you want to delete this User permanently?',
              }"
              v-tippy
              as="button"
              method="DELETE"
              :href="route('admin.user.delete', item.id)"
              class="inline-flex items-center justify-center text-red-600 hover:text-red-900 focus:outline-none"
              :title="__('Delete User')"
            >
              <TrashIcon class="inline-block w-5 h-5" />
            </InertiaLink>
          </td>
        </template>
      </DataTable>
    </div>
  </AdminLayout>
</template>
