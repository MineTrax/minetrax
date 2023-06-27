<script setup>
import AppHead from '@/Components/AppHead.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
defineProps({
});
</script>

<template>
  <AdminLayout>
    <AppHead :title="__('Chatlog - ServerIntel')" />

    <div>
      Chatlog
    </div>
  </AdminLayout>
</template>

