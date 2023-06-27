<script setup>
import AppHead from '@/Components/AppHead.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
defineProps({
});
</script>

<template>
  <AdminLayout>
    <AppHead :title="__('Playerbase - ServerIntel')" />

    <div>
      Playerbase
    </div>
  </AdminLayout>
</template>

