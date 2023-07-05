<script setup>
import AppHead from '@/Components/AppHead.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ServerIntelServerSelector from '@/Shared/ServerIntelServerSelector.vue';

defineProps({
    serverList: {
        type: Object,
    },
    filters: {
        type: Object,
    },
});
</script>

<template>
  <AdminLayout>
    <AppHead :title="__('Overview - ServerIntel')" />

    <div class="p-4 mx-auto space-y-4 max-w-7xl">
      <ServerIntelServerSelector
        :title="__('Server Overview')"
        :server-list="serverList"
        :filters="filters"
      />

      <div>
        Overview
      </div>
    </div>
  </AdminLayout>
</template>

