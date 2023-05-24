<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import AppHead from '@/Components/AppHead.vue';
import AdminSideMenu from '@/Shared/AdminSideMenu.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
</script>

<template>
  <AdminLayout>
    <AppHead title="Admin Dashboard" />
    <h1>This is main section and should not be hidden bla bla bla</h1>
  </AdminLayout>
</template>
