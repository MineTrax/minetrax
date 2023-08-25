<script setup>
import AppHead from '@/Components/AppHead.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ServerPerformanceOverTimeMetricBox from '@/Shared/ServerPerformanceOverTimeMetricBox.vue';
import ServerIntelPerformanceNumbersBox from '@/Shared/ServerIntelPerformanceNumbersBox.vue';
import ServerIntelServerSelector from '@/Shared/ServerIntelServerSelector.vue';
import AlertCard from '@/Components/AlertCard.vue';

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
</script>

<template>
  <AdminLayout>
    <AppHead :title="__('Performance - ServerIntel')" />

    <div class="p-4 mx-auto space-y-4 px-10">
      <ServerIntelServerSelector
        :title="__('Server Performance')"
        :server-list="props.serverList"
        :filters="props.filters"
      />

      <AlertCard
        v-if="noIntelForOverWeek"
        title-class="flex items-center"
        text-color="text-orange-800 dark:text-orange-500"
        border-color="border-orange-500"
      >
        {{ __("Server haven't sent Intel data for over 7 days.") }}
      </AlertCard>

      <ServerPerformanceOverTimeMetricBox :servers="filters?.servers" />

      <ServerIntelPerformanceNumbersBox :servers="filters?.servers" />
    </div>
  </AdminLayout>
</template>
