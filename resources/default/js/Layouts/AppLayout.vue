<script setup>
import MainNavbarCustom from '@/Shared/MainNavbarCustom.vue';
import MainFooter from '@/Shared/MainFooter.vue';
import HeaderBroadcastBar from '@/Shared/HeaderBroadcastBar.vue';
import BaseLayout from './BaseLayout.vue';

const props = defineProps({
  hideFooter: {
    type: Boolean,
    default: false,
  }
})
</script>

<template>
  <BaseLayout :hide-footer="hideFooter">
    <!-- App NavBar -->
    <MainNavbarCustom />

    <!-- App Content with Broadcast Bar -->
    <HeaderBroadcastBar />
    <slot />

    <!-- App Footer -->
    <MainFooter v-if="!hideFooter" />
  </BaseLayout>
</template>
