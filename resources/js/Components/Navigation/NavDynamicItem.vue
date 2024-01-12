<template>
  <template v-if="shouldRender">
    <!--  Route -->
    <div
      v-if="item.type === 'route'"
      class="hidden md:flex"
    >
      <NavLink
        :href="route(item.route)"
        :active="route().current(item.route)"
        :open-in-new-tab="item.is_open_in_new_tab"
      >
        {{ __(item.title) }}
      </NavLink>
    </div>

    <!--  CustomPage -->
    <div
      v-if="['custom-page', 'download'].includes(item.type)"
      class="hidden md:flex"
    >
      <NavLink
        :href="route(item.route, item.route_params)"
        :active="route().current(item.route, item.route_params)"
        :open-in-new-tab="item.is_open_in_new_tab"
      >
        {{ __(item.title) }}
      </NavLink>
    </div>

    <!--  Dropdown -->
    <div
      v-if="item.type === 'dropdown'"
      class="hidden md:flex"
    >
      <NavDropdown
        :title="item.title"
        :items="item.children"
      />
    </div>

    <!-- Logo Component -->
    <AppLogoMark
      v-if="item.type === 'component' && item.component === 'AppLogoMark'"
      :can-show-admin-sidebar="canShowAdminSidebar"
    />

    <!--    Notification Component -->
    <div
      v-if="$page.props.auth.user &&
        item.type === 'component' && item.component === 'NotificationDropdown'"
      class="hidden md:flex items-center"
    >
      <NotificationDropdown />
    </div>

    <!--      Profile Dropdown-->
    <div
      v-if="$page.props.auth.user &&
        item.type === 'component' && item.component === 'ProfileDropdown'"
      class="hidden md:flex items-center"
    >
      <ProfileDropdown
        :can-show-admin-sidebar="canShowAdminSidebar"
        @logout="emit('logout')"
      />
    </div>

    <!--    Search Component -->
    <div
      v-if="item.type === 'component' && item.component === 'NavbarSearch'"
      class="hidden md:flex items-center"
    >
      <Search />
    </div>

    <!--  Light Dark Theme Selector Component -->
    <div
      v-if="item.type === 'component' && item.component === 'LightDarkSelector'"
      class="hidden md:flex items-center"
    >
      <LightDarkSelector />
    </div>
  </template>
</template>

<script setup>
import {computed} from 'vue';
import AppLogoMark from '@/Components/Navigation/AppLogoMark.vue';
import NavLink from '@/Jetstream/NavLink.vue';
import NavDropdown from '@/Components/Navigation/NavDropdown.vue';
import NotificationDropdown from '@/Shared/NotificationDropdown.vue';
import Search from '@/Shared/Search.vue';
import { usePage } from '@inertiajs/vue3';
import ProfileDropdown from '@/Components/Navigation/ProfileDropdown.vue';
import LightDarkSelector from '@/Components/Navigation/LightDarkSelector.vue';

const user = computed(() => usePage().props?.auth?.user);

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

const emit = defineEmits(['logout']);

const shouldRender = computed(() => {
    if (props.item.authenticated && !user?.value)
        return false;
    if (props.item.guestonly && user?.value)
        return false;
    return true;
});

</script>

