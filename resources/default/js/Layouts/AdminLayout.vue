<script setup>
import AdminSideMenu from '@/Shared/AdminSideMenu.vue';
import { useStorage } from '@vueuse/core';
import BaseLayout from './BaseLayout.vue';

let isMenuCollapsed = useStorage('is-admin-sidebar-menu-collapsed', false);
function toggleMenuCollapse() {
    isMenuCollapsed.value = !isMenuCollapsed.value;
}
</script>

<template>
  <BaseLayout :hide-footer="true">
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
  </BaseLayout>
</template>
