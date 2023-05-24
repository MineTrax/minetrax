<template>
  <AdminLayout>
    <app-head :title="__('Server #:id', {id: server.id})" />

    <div class="py-12 px-10 max-w-6xl mx-auto space-y-4">
      <h2 class="text-2xl text-gray-600 dark:text-gray-200">
        <span class="font-bold">#{{ server.id }}</span>
        -
        <span class="font-bold">{{ server.name }}</span>
      </h2>

      <server-sub-menu :id="server.id" />

      <div
        id="server-general-data"
        class="shadow bg-white text-gray-700 dark:text-gray-300 text-sm dark:bg-cool-gray-800 dark:text-gray-300 rounded w-full p-2 space-y-4 md:p-5"
      >
        <h2 class="text-3xl text-center animate-bounce">
          {{ __('Coming Soon!') }}
        </h2>
      </div>
    </div>
  </AdminLayout>
</template>

<script>
import ServerSubMenu from '@/Pages/Admin/Server/ServerSubMenu.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

export default {

    components: {
        AdminLayout,
        ServerSubMenu,
    },
    props: {
        server: Object,
    },
};
</script>
