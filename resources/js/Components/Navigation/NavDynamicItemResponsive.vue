<template>
  <template v-if="shouldRender">
    <!--  Routes-->
    <ResponsiveNavLink
      v-if="item.type === 'route' || item.type === 'custom-page'"
      :href="route(item.route, item.route_params ?? null)"
      :active="route().current(item.route, item.route_params ?? null)"
    >
      {{ __(item.title) }}
    </ResponsiveNavLink>

    <!--  Dropdown -->
    <template
      v-if="item.type === 'dropdown'"
    >
      <NavDynamicItemResponsive
        v-for="dropItem in item.children"
        :key="dropItem.key"
        :can-show-admin-sidebar="canShowAdminSidebar"
        :item="dropItem"
      />
    </template>
  </template>
</template>


<script setup>
import {computed, defineProps} from 'vue';
import { usePage } from '@inertiajs/inertia-vue3';
import ResponsiveNavLink from '@/Jetstream/ResponsiveNavLink.vue';

const user = computed(() => usePage().props?.value?.user);

const props = defineProps({
    item: {
        type: Object,
        required: true
    },
    canShowAdminSidebar: {
        type: Boolean,
        required: true
    },
});

const emit = defineEmits(['open-admin-sidebar', 'logout']);

const shouldRender = computed(() => {
    if (props.item.authenticated && !user?.value)
        return false;
    if (props.item.guestonly && user?.value)
        return false;
    return true;
});

</script>
