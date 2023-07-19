<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import AdminSideMenu from '@/Shared/AdminSideMenu.vue';
import {ref} from 'vue';

const isMenuCollapsed = ref(false);
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
