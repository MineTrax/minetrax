<script setup>
import MainNavbarCustom from '@/Shared/MainNavbarCustom.vue';
import MainFooter from '@/Shared/MainFooter.vue';
import HeaderBroadcastBar from '@/Shared/HeaderBroadcastBar.vue';
import BaseLayout from './BaseLayout.vue';
</script>

<template>
    <BaseLayout>
        <!-- App NavBar -->
        <MainNavbarCustom />

        <!-- App Content with Broadcast Bar -->
        <HeaderBroadcastBar />
        <slot />

        <!-- App Footer -->
        <MainFooter />
    </BaseLayout>
</template>
