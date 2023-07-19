<script setup>
import AppHead from '@/Components/AppHead.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
defineProps({
});
</script>

<template>
  <AdminLayout>
    <AppHead :title="__('Playerbase - ServerIntel')" />

    <div class="p-4 mx-auto space-y-4 max-w-7xl">
      <div
        class="p-3 space-y-8 bg-white rounded shadow dark:bg-cool-gray-800"
      >
        <h3 class="animate-pulse text-3xl text-center p-5 m-10 text-light-blue-500 font-extrabold">
          {{ __("Coming Soon!") }}
        </h3>
      </div>
    </div>
  </AdminLayout>
</template>

