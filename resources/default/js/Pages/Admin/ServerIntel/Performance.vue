<script setup>
import AppHead from '@/Components/AppHead.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';
import ServerPerformanceOverTimeMetricBox from '@/Shared/ServerPerformanceOverTimeMetricBox.vue';
import ServerIntelPerformanceNumbersBox from '@/Shared/ServerIntelPerformanceNumbersBox.vue';
import ServerIntelServerSelector from '@/Shared/ServerIntelServerSelector.vue';
import AlertCard from '@/Components/AlertCard.vue';
import { useTranslations } from '@/Composables/useTranslations';

const { __ } = useTranslations();

const props = defineProps({
    serverList: {
        type: Object,
    },
    filters: {
        type: Object,
    },
    noIntelForOverWeek: {
        type: Boolean
    }
});


let selectedServers = props.filters?.servers?.length ? props.filters?.servers[0] : null;
const breadcrumbItems = [
    {
        text: __('Admin'),
        current: false,
    },
    {
        text: __('Server Intel'),
        current: false,
    },
    {
        text: __('Performance'),
        current: true,
    },
    {
        text: props.serverList[selectedServers] ?? __('All Servers'),
        current: true,
    }
];
</script>

<template>
  <AdminLayout>
    <AppHead :title="__('Performance - ServerIntel')" />

    <div class="px-10 py-8 mx-auto space-y-4">
      <div class="flex justify-between items-center">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
        <ServerIntelServerSelector
          :server-list="props.serverList"
          :filters="props.filters"
        />
      </div>

      <AlertCard
        v-if="noIntelForOverWeek"
        variant="warning"
      >
        {{ __("Server haven't sent Intel data for over 7 days.") }}
      </AlertCard>

      <ServerPerformanceOverTimeMetricBox :servers="filters?.servers" />

      <ServerIntelPerformanceNumbersBox :servers="filters?.servers" />
    </div>
  </AdminLayout>
</template>
