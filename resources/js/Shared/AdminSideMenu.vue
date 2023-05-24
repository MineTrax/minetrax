<script setup>
import {
    ChevronDoubleLeftIcon,
    ServerStackIcon,
    UsersIcon,
    AcademicCapIcon,
    HomeIcon,
    NewspaperIcon,
    ChartPieIcon,
    DocumentTextIcon,
    Cog8ToothIcon,
} from '@heroicons/vue/24/outline';
import SideNavItem from '@/Components/Navigation/SideNavItem.vue';

defineProps({
    collapsed: Boolean,
});
defineEmits(['toggleCollapse']);

const navItems = [
    {
        label: 'Dashboard',
        href: route('admin.dashboard'),
        active: route().current('admin.dashboard'),
        children: [],
        icon: HomeIcon,
        visible: true
    },
    {
        label: 'Servers',
        href: route('admin.server.index'),
        active: route().current('admin.server.*'),
        children: [],
        icon: ServerStackIcon,
        visible: true
    },
    {
        label: 'Users', href: '#', active: false, children: [
            {
                label: 'All Users',
                href: route('admin.user.index'),
                active: route().current('admin.user.*'),
                children: [],
                icon: null,
                visible: true
            },
            {
                label: 'User Roles',
                href: route('admin.role.index'),
                active: route().current('admin.role.*'),
                children: [],
                icon: null,
                visible: true
            },
            {
                label: 'User Badges',
                href: route('admin.badge.index'),
                active: route().current('admin.badge.*'),
                children: [],
                icon: null,
                visible: true
            },
            {
                label: 'Online Users',
                href: route('admin.session.index'),
                active: route().current('admin.session.*'),
                children: [],
                icon: null,
                visible: true
            },
        ],
        icon: UsersIcon, visible: true
    },
    {
        label: 'News',
        href: route('admin.news.index'),
        active: route().current('admin.news.*'),
        children: [],
        icon: NewspaperIcon,
        visible: true
    },
    {
        label: 'Polls',
        href: route('admin.poll.index'),
        active: route().current('admin.poll.*'),
        children: [],
        icon: ChartPieIcon,
        visible: true
    },
    {
        label: 'Player Ranks',
        href: route('admin.rank.index'),
        active: route().current('admin.rank.*'),
        children: [],
        icon: AcademicCapIcon,
        visible: true
    },
    {
        label: 'Custom Pages',
        href: route('admin.custom-page.index'),
        active: route().current('admin.custom-page.*'),
        children: [],
        icon: DocumentTextIcon,
        visible: true
    },
    {
        label: 'Settings', href: '#', active: false, children: [
            {
                label: 'General',
                href: route('admin.setting.general.show'),
                active: route().current('admin.setting.general.show'),
                children: [],
                icon: null,
                visible: true
            },
            {
                label: 'Theme',
                href: route('admin.setting.theme.show'),
                active: route().current('admin.setting.theme.show'),
                children: [],
                icon: null,
                visible: true
            },
            {
                label: 'Plugin',
                href: route('admin.setting.plugin.show'),
                active: route().current('admin.setting.plugin.show'),
                children: [],
                icon: null,
                visible: true
            },
            {
                label: 'Player',
                href: route('admin.setting.player.show'),
                active: route().current('admin.setting.player.show'),
                children: [],
                icon: null,
                visible: true
            },
            {
                label: 'Navigation',
                href: route('admin.setting.navigation.show'),
                active: route().current('admin.setting.navigation.show'),
                children: [],
                icon: null,
                visible: true
            },
        ],
        icon: Cog8ToothIcon,
        visible: true
    },
    // {label: 'Media', href: '#', active: false, children: [
    //     {label: 'All media', href: '#', active: false, children: [], icon: null},
    //     {label: 'Add new', href: '#', active: false, children: [
    //         {label: 'Third level', href: '#', active: true, children: [], icon: null},
    //     ], icon: null},
    // ], icon: PhotoIcon},
];
</script>

<template>
  <div
    :class="[
      'min-h-screen fixed bg-white shadow dark:bg-cool-gray-800 z-10 duration-300',
      collapsed ? 'w-16' : 'w-64'
    ]"
  >
    <div :class="['px-4 mt-2 flex', collapsed ? 'justify-center' : 'justify-end']">
      <button @click.prevent="$emit('toggleCollapse')">
        <ChevronDoubleLeftIcon
          :class="[
            'h-6 w-6 p-0.5 text-gray-400 hover:text-gray-600',
            collapsed ? '-rotate-180' : ''
          ]"
        />
      </button>
    </div>

    <nav class="mt-2 px-2">
      <SideNavItem
        v-for="item in navItems"
        :key="item.label"
        :item="item"
        :collapsed="collapsed"
      />
    </nav>
  </div>
</template>


