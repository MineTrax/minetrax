<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useTranslations } from '@/Composables/useTranslations';
import DataTable from '@/Components/DataTable/DataTable.vue';
import DtRowItem from '@/Components/DataTable/DtRowItem.vue';

const { __ } = useTranslations();

defineProps({
    sessions : Object,
    filters: Object,
});

const headerRow = [
    {
        key: 'country',
        label: __('Country'),
        sortable: false,
        class: 'text-center',
    },
    {
        key: 'user_id',
        sortable: true,
        label: __('User'),
        class: 'w-3/12',
    },
    {
        key: 'ip_address',
        sortable: true,
        label: __('Ip Address'),
    },
    {
        key: 'platform',
        label: __('Platform'),
        sortable: false,
    },
    {
        key: 'browser',
        sortable: false,
        label: __('Browser'),
    },
    {
        key: 'last_activity',
        label: __('Last Activity'),
        sortable: true,
    },
];
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Online Users & Guests')" />

    <div class="px-10 py-8 mx-auto text-foreground">
      <div class="flex justify-between mb-4">
        <h1 class="text-3xl font-bold text-foreground dark:text-foreground">
          {{ __("Online Users & Guests") }}
        </h1>
      </div>

      <DataTable
        class="bg-white rounded shadow dark:bg-surface-800"
        :header="headerRow"
        :data="sessions"
        :filters="filters"
      >
        <template #default="{ item }">
          <td
            class="px-4 py-4 text-sm font-medium text-center text-foreground whitespace-nowrap dark:text-foreground"
          >
            <div class="flex items-center justify-center">
              <div
                v-tippy
                class="flex-shrink-0 h-10 w-10 focus:outline-none"
                :content="item.country.name"
              >
                <img
                  class="h-10 w-10"
                  :src="item.country.photo_path"
                  alt=""
                >
              </div>
            </div>
          </td>
          <td class="px-4">
            <InertiaLink
              v-if="item.user"
              :href="route('user.public.get', item.user.username)"
              class="flex items-center"
            >
              <div class="flex-shrink-0 h-10 w-10 mr-2">
                <img
                  class="h-10 w-10 rounded-full"
                  :src="item.user.profile_photo_url"
                  alt="Avatar"
                >
              </div>
              <div class="flex-col">
                <div
                  class="text-sm font-semibold text-foreground dark:text-foreground whitespace-nowrap truncate"
                  :style="[item.user.roles[0].color ? {color: item.user.roles[0].color} : null]"
                >
                  {{ item.user.name }}
                </div>
                <div class="text-sm text-foreground">
                  @{{ item.user.username }}
                </div>
              </div>
            </InertiaLink>
            <div
              v-else
              class="flex items-center italic text-sm text-foreground dark:text-foreground"
            >
              {{ __("Anonymous") }}
            </div>
          </td>

          <DtRowItem>
            <div class="text-sm font-medium text-foreground dark:text-foreground">
              <a
                class="hover:underline"
                target="_blank"
                :href="`https://check-host.net/ip-info?host=${item.ip_address}`"
              >{{ item.ip_address }}</a>
            </div>
          </DtRowItem>

          <DtRowItem>
            {{ item.agent.platform }} {{ item.agent.platform_version }}
          </DtRowItem>

          <DtRowItem>
            {{ item.agent.browser }} {{ item.agent.browser_version }}
          </DtRowItem>

          <DtRowItem>
            {{ item.last_active }}
          </DtRowItem>
        </template>
      </DataTable>
    </div>
  </AdminLayout>
</template>
