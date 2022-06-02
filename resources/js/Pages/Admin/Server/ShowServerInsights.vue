<template>
  <app-layout>
    <app-head :title="`Server #${server.id}`" />

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
          Coming Soon!
        </h2>
      </div>
    </div>
  </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout';
import ServerSubMenu from '@/Pages/Admin/Server/ServerSubMenu';

export default {

    components: {
        ServerSubMenu,
        AppLayout,
    },
    props: {
        server: Object,
    },
};
</script>
