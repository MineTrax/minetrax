<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useAuthorizable } from '@/Composables/useAuthorizable';
import { useHelpers } from '@/Composables/useHelpers';
import { useTranslations } from '@/Composables/useTranslations';
import DataTable from '@/Components/DataTable/DataTable.vue';
import DtRowItem from '@/Components/DataTable/DtRowItem.vue';
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { PencilSquareIcon, TrashIcon } from '@heroicons/vue/24/outline';
import Icon from '@/Components/Icon.vue';
import { Link } from '@inertiajs/vue3';

const { can } = useAuthorizable();
const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString } = useHelpers();

defineProps({
    roles: Object,
    filters: Object,
});

const breadcrumbItems = [
    {
        text: __('Admin'),
        current: false,
    },
    {
        text: __('User Roles & Permissions'),
        current: true,
    }
];

const headerRow = [
    {
        key: 'id',
        label: __('ID'),
        sortable: true,
        class: 'text-center',
    },
    {
        key: 'image',
        sortable: false,
        label: __('Image'),
    },
    {
        key: 'name',
        sortable: true,
        label: __('Name'),
    },
    {
        key: 'users_count',
        label: __('Users'),
    },
    {
        key: 'is_staff',
        label: __('Staff'),
        sortable: true,
    },
    {
        key: 'created_at',
        label: __('Created'),
        sortable: true,
        class: 'w-1/12',
    },
    {
        key: 'permissions',
        sortable: false,
        label: __('Permissions'),
        class: 'w-5/12',
    },
    {
        key: 'weight',
        sortable: true,
        label: __('Weight'),
    },
    {
        key: 'actions',
        label: __('Actions'),
        sortable: false,
        class: 'w-1/12 text-right',
    },
];
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Users Roles Administration')" />

    <div class="px-10 py-8 mx-auto text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb class="mt-0" breadcrumb-class="max-w-none px-0 md:px-0" :items="breadcrumbItems" />
        <div class="flex">
          <Button
            v-if="can('create roles')"
            as-child
          >
            <Link
              :href="route('admin.role.create')"
            >
              {{ __("Create Role") }}
            </Link>
          </Button>
        </div>
      </div>

      <DataTable
        class="bg-card rounded-lg shadow"
        :header="headerRow"
        :data="roles"
        :filters="filters"
      >
        <template #default="{ item }">
          <td
            class="px-4 py-4 text-sm font-medium text-center text-foreground whitespace-nowrap dark:text-foreground"
          >
            {{ item.id }}
          </td>
          <td>
            <div class="flex-shrink-0">
              <img
                v-if="item.photo_url"
                class="max-h-8"
                :src="item.photo_url"
                :alt="__('Role Image')"
              >
              <div
                v-else
                class="inline-flex font-bold uppercase leading-5 p-1.5 bg-primary text-white rounded-sm"
                :style="`background-color: ${item.color};`"
              >
                {{ item.display_name }}
              </div>
            </div>
          </td>

          <td class="px-4 whitespace-nowrap">
            <div class="items-center">
              <div
                class="text-sm font-semibold text-foreground dark:text-foreground"
                :style="[
                  item.color ? { color: item.color } : null,
                ]"
              >
                {{ item.display_name }}
              </div>
              <div
                class="text-sm text-foreground dark:text-foreground"
              >
                {{ item.name }}
              </div>
            </div>
          </td>

          <DtRowItem>
            <Link
              class="hover:text-primary"
              :href="
                route('admin.user.index', {
                  filter: { 'roles.display_name' : item.display_name}
                })
              "
            >
              {{ item.users_count }}
            </Link>
          </DtRowItem>

          <DtRowItem>
            <Icon
              v-if="item.is_staff"
              class="text-success-500"
              name="check-circle"
            />
            <Icon
              v-else
              class="text-error-500"
              name="cross-circle"
            />
          </DtRowItem>
          <DtRowItem>
            <span
              v-tippy
              :title="formatToDayDateString(item.created_at)"
            >
              {{ formatTimeAgoToNow(item.created_at) }}
            </span>
          </DtRowItem>

          <td
            class="px-4 py-3 space-x-1 space-y-1 text-sm font-medium"
          >
            <Badge
              v-if="item.name === 'superadmin'"
              variant="outline"
              class="bg-primary/10 text-primary"
            >{{ __("All Permissions") }}
            </Badge>

            <template v-else-if="item.permissions.length > 0">
              <Badge
                v-for="permission in item.permissions"
                :key="permission.id"
                variant="outline"
              >
                {{ permission.name }}
              </Badge>
            </template>
            <span
              v-else
              class="italic text-foreground"
            >{{
              __("No administration permissions for this role.")
            }}</span>
          </td>

          <DtRowItem>
            {{ item.weight }}
          </DtRowItem>

          <td
            class="px-6 py-4 space-x-2 text-sm font-medium text-right whitespace-nowrap"
          >
            <Link
              v-if="can('update roles')"
              v-tippy
              as="a"
              :href="route('admin.role.edit', item.id)"
              class="inline-flex items-center justify-center text-warning-600 dark:text-warning-500 hover:text-warning-800 dark:hover:text-warning-800"
              :title="__('Edit Role')"
            >
              <PencilSquareIcon class="inline-block w-5 h-5" />
            </Link>
            <Link
              v-if="can('delete roles')"
              v-confirm="{
                message:
                  'Are you sure you want to delete this Role permanently?',
              }"
              v-tippy
              as="button"
              method="DELETE"
              :href="route('admin.role.delete', item.id)"
              class="inline-flex items-center justify-center text-error-600 hover:text-error-900 focus:outline-none"
              :title="__('Delete Role')"
            >
              <TrashIcon class="inline-block w-5 h-5" />
            </Link>
          </td>
        </template>
      </DataTable>
    </div>
  </AdminLayout>
</template>
