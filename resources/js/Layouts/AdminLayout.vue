<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import AdminSideMenu from '@/Shared/AdminSideMenu.vue';
import { useStorage } from '@vueuse/core';

let isMenuCollapsed = useStorage('is-admin-sidebar-menu-collapsed', false);
function toggleMenuCollapse() {
    isMenuCollapsed.value = !isMenuCollapsed.value;
}
</script>

<template>
  <AppLayout>
    <AdminSideMenu
      :collapsed="isMenuCollapsed"
      @toggle-collapse="toggleMenuCollapse"
    />

    <main
      :class="[
        isMenuCollapsed ? 'ml-16' : 'ml-64'
      ]"
    >
      <slot />
    </main>
  </AppLayout>
</template>
