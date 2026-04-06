<script setup>
import DataTable from "@/Components/DataTable/DataTable.vue";
import DtRowItem from "@/Components/DataTable/DtRowItem.vue";
import UserDisplayname from "@/Components/UserDisplayname.vue";
import { useAuthorizable } from "@/Composables/useAuthorizable";
import { useHelpers } from "@/Composables/useHelpers";
import { useTranslations } from "@/Composables/useTranslations";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Button } from "@/Components/ui/button";
import { ButtonGroup } from "@/Components/ui/button-group";
import AppBreadcrumb from "@/Shared/AppBreadcrumb.vue";
import {
    ArrowUpOnSquareStackIcon,
    BellSlashIcon,
    EyeIcon,
    NoSymbolIcon,
    PencilSquareIcon,
    TrashIcon,
} from "@heroicons/vue/24/outline";

const { can } = useAuthorizable();
const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString } = useHelpers();

const props = defineProps({
    countries: Array,
    roles: Array,
    users: Object,
    filters: Object,
});

const breadcrumbItems = [
    {
        text: __("Admin"),
        current: false,
    },
    {
        text: __("Users"),
        current: true,
    }
];

const headerRow = [
    {
        key:"id",
        label: __("ID"),
        sortable: true,
        class:"text-center",
    },
    {
        key:"flag",
        label: __("Flag"),
        filterable: {
            key:"country.name",
            type:"multiselect",
            options: props.countries,
            searchable: true,
        }
    },
    {
        key:"avatar",
        label: __("Avatar"),
    },
    {
        key:"name",
        label: __("Name"),
        class:"w-3/12",
        sortable: true,
        filterable: [
            {
                type:"text",
                key:"name",
                label: __("Name"),
            },
            {
                key:"is_verified",
                type:"select",
                options: [
                    "true",
                    "false"
                ],
                searchable: false,
                label: __("Verified"),
            }
        ]
    },
    {
        key:"username",
        label: __("Username"),
        class:"w-2/12",
        sortable: true,
        filterable: {
            type:"text",
        }
    },
    {
        key:"email",
        label: __("Email"),
        class:"w-2/12",
        sortable: true,
        filterable: {
            type:"text",
        }
    },
    {
        key:"created_at",
        label: __("Joined"),
        sortable: true,
        class:"w-2/12",
    },
    {
        key:"role_id",
        label: __("Role"),
        class:"w-2/12",
        sortable: false,
        filterable: {
            key:"roles.display_name",
            type:"multiselect",
            options: props.roles,
            searchable: true,
        }
    },
    {
        key:"flags",
        label: __("Flags"),
        sortable: false,
        class:"w-1/12",
    },
    {
        key:"actions",
        label: __("Actions"),
        sortable: false,
        class:"w-2/12 text-right",
    },
];
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Users Administration')" />

    <div class="px-10 py-8 mx-auto text-foreground">
      <AppBreadcrumb
        class="mt-0 mb-4"
        breadcrumb-class="max-w-none px-0 md:px-0"
        :items="breadcrumbItems"
      />

      <DataTable
        class="bg-card rounded-lg shadow"
        :header="headerRow"
        :data="users"
        :filters="filters"
        :row-href="(item) => route('user.public.get', item.username)"
      >
        <template #default="{ item }">
          <td
            class="px-4 py-4 text-sm font-medium text-center text-foreground whitespace-nowrap dark:text-foreground"
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
              class="text-sm font-semibold text-foreground dark:text-foreground"
              :style="[
                item.roles[0].color
                  ? { color: item.roles[0].color }
                  : null,
              ]"
            >
              <UserDisplayname
                :user="item"
                icon-class="w-4 h-4"
                text-class="text-sm"
              />
            </div>
          </td>

          <DtRowItem>
            {{ item.username }}
          </DtRowItem>

          <DtRowItem>
            <div>
              {{ item.email }}
            </div>
            <div
              v-if="item.discord_user_id"
              class="text-xs whitespace-nowrap"
            >
              {{ __('Discord ID') }}:
              {{ item.discord_user_id }}
            </div>
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
              class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-lg bg-orange-100 text-orange-800 dark:bg-orange-700/25 dark:text-orange-400"
              :title="__('Muted')"
            >
              <BellSlashIcon class="inline-block w-4 h-4" />
            </span>
            <span
              v-if="item.banned_at"
              v-tippy
              class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-lg bg-destructive/10 text-destructive"
              :title="__('Banned')"
            >
              <NoSymbolIcon class="inline-block w-4 h-4" />
            </span>

            <span
              v-if="!item.muted_at && !item.banned_at"
              class="text-xs italic"
            >{{ __("None") }}</span>
          </td>

          <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
            <ButtonGroup>
              <Button
                variant="outline"
                size="icon"
                as-child
                class="text-primary hover:text-primary"
              >
                <InertiaLink
                  v-tippy
                  as="a"
                  :href="route('user.public.get', item.username)"
                  :title="__('View Profile')"
                >
                  <EyeIcon />
                </InertiaLink>
              </Button>
              <Button
                v-if="can('impersonate users')"
                variant="outline"
                size="icon"
                as-child
                class="text-orange-500 hover:text-orange-700"
              >
                <InertiaLink
                  v-tippy
                  as="a"
                  :href="route('admin.impersonate.take', item.id)"
                  :title="__('Impersonate User')"
                >
                  <ArrowUpOnSquareStackIcon />
                </InertiaLink>
              </Button>
              <Button
                v-if="can('update users')"
                variant="outline"
                size="icon"
                as-child
                class="text-yellow-600 dark:text-yellow-500 hover:text-yellow-700 dark:hover:text-yellow-400"
              >
                <InertiaLink
                  v-tippy
                  as="a"
                  :href="route('admin.user.edit', item.id)"
                  :title="__('Edit User')"
                >
                  <PencilSquareIcon />
                </InertiaLink>
              </Button>
              <Button
                v-if="can('delete users')"
                variant="outline"
                size="icon"
                as-child
                class="text-destructive hover:text-destructive"
              >
                <InertiaLink
                  v-confirm="{
                    message:
                      'Are you sure you want to delete this User permanently?',
                  }"
                  v-tippy
                  as="button"
                  method="DELETE"
                  :href="route('admin.user.delete', item.id)"
                  :title="__('Delete User')"
                >
                  <TrashIcon />
                </InertiaLink>
              </Button>
            </ButtonGroup>
          </td>
        </template>
      </DataTable>
    </div>
  </AdminLayout>
</template>
