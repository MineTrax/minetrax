<template>
    <template v-if="shouldRender">

        <!--  Route -->
        <NavigationMenuItem v-if="item.type === 'route'" class="hidden md:flex">
            <NavigationMenuLink :class='cn(
                "group inline-flex h-9 w-max items-center justify-center rounded-md bg-sidebar px-4 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground focus:outline-none disabled:pointer-events-none disabled:opacity-50 data-[active]:bg-accent/50 data-[state=open]:bg-accent/50"
            )' :active="route().current(item.route)" as-child>
                <NavLink :href="route(item.route)" :open-in-new-tab="item.is_open_in_new_tab">
                    {{ __(item.title) }}
                </NavLink>
            </NavigationMenuLink>
        </NavigationMenuItem>

        <!-- Custom Page -->
        <NavigationMenuItem v-if="['custom-page', 'download', 'recruitment', 'application'].includes(item.type)"
            class="hidden md:flex">
            <NavigationMenuLink :class='cn(
                "group inline-flex h-9 w-max items-center justify-center rounded-md bg-sidebar px-4 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground focus:outline-none disabled:pointer-events-none disabled:opacity-50 data-[active]:bg-accent/50 data-[state=open]:bg-accent/50"
            )' :active="route().current(item.route, item.route_params)">
                <NavLink :href="route(item.route, item.route_params)" :open-in-new-tab="item.is_open_in_new_tab">
                    {{ __(item.title) }}
                </NavLink>
            </NavigationMenuLink>
        </NavigationMenuItem>

        <!--  Dropdown -->
        <NavigationMenuItem v-if="item.type === 'dropdown'" class="hidden md:flex">
            <NavDropdown :title="item.title" :items="item.children" />
        </NavigationMenuItem>

        <!-- Logo Component -->
        <AppLogoMark v-if="item.type === 'component' && item.component === 'AppLogoMark'"
            :can-show-admin-sidebar="canShowAdminSidebar" />

        <!--    Notification Component -->
        <div v-if="$page.props.auth.user &&
            item.type === 'component' && item.component === 'NotificationDropdown'"
            class="hidden md:flex items-center mx-2">
            <NotificationDropdown />
        </div>

        <!--      Profile Dropdown-->
        <NavigationMenuItem v-if="$page.props.auth.user &&
            item.type === 'component' && item.component === 'ProfileDropdown'"
            class="hidden md:flex items-center mx-2">
            <ProfileDropdown v-if="$page.props.auth.user" :can-show-admin-sidebar="canShowAdminSidebar"
                @logout="emit('logout')" />
        </NavigationMenuItem>

        <!--    Search Component -->
        <div v-if="item.type === 'component' && item.component === 'NavbarSearch'"
            class="hidden md:flex items-center mx-2">
            <Search />
        </div>

        <!--  Light Dark Theme Selector Component -->
        <div v-if="item.type === 'component' && item.component === 'LightDarkSelector'"
            class="hidden md:flex items-center mx-2">
            <LightDarkSelector />
        </div>

        <!--  Locale Selector Component -->
        <div v-if="item.type === 'component' && item.component === 'LocaleSelector' && $page.props.localeSwitcherEnabled"
            class="hidden md:flex items-center mx-2">
            <LocaleSelector />
        </div>
    </template>
</template>

<script setup>
import AppLogoMark from '@/Components/Navigation/AppLogoMark.vue';
import LightDarkSelector from '@/Components/Navigation/LightDarkSelector.vue';
import LocaleSelector from '@/Components/Navigation/LocaleSelector.vue';
import NavDropdown from '@/Components/Navigation/NavDropdown.vue';
import ProfileDropdown from '@/Components/Navigation/ProfileDropdown.vue';
import NavLink from '@/Jetstream/NavLink.vue';
import NotificationDropdown from '@/Shared/NotificationDropdown.vue';
import Search from '@/Shared/Search.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

import {
    NavigationMenuItem,
    NavigationMenuLink
} from '@/Components/ui/navigation-menu';
import { cn } from '@/lib/utils';

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
