<script setup>
import {
    ChevronDoubleLeftIcon,
    ServerStackIcon,
    UsersIcon,
    HomeIcon,
    NewspaperIcon,
    ChartPieIcon,
    DocumentTextIcon,
    Cog8ToothIcon,
    PresentationChartLineIcon,
    CircleStackIcon,
    TrophyIcon,
    CloudArrowDownIcon,
    ClipboardDocumentListIcon,
    BugAntIcon
} from '@heroicons/vue/24/outline';
import SideNavItem from '@/Components/Navigation/SideNavItem.vue';
import { useAuthorizable } from '@/Composables/useAuthorizable';
const { canWild, hasRole, can } = useAuthorizable();

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
        visible: canWild('servers')
    },
    {
        label: 'Server Analytics',
        href: '#',
        active: route().current('admin.intel.server.*'),
        children: [
            {
                label: 'Overview',
                href: route('admin.intel.server.index'),
                active: route().current('admin.intel.server.index'),
                children: [],
                icon: null,
                visible: canWild('server_intel')
            },
            {
                label: 'Performance',
                href: route('admin.intel.server.performance'),
                active: route().current('admin.intel.server.performance'),
                children: [],
                icon: null,
                visible: canWild('server_intel')
            },
            {
                label: 'Playerbase',
                href: route('admin.intel.server.playerbase'),
                active: route().current('admin.intel.server.playerbase'),
                children: [],
                icon: null,
                visible: canWild('server_intel')
            },
            {
                label: 'Chatlog',
                href: route('admin.intel.server.chatlog'),
                active: route().current('admin.intel.server.chatlog'),
                children: [],
                icon: null,
                visible: canWild('server_intel')
            },
            {
                label: 'Consolelog',
                href: route('admin.intel.server.consolelog'),
                active: route().current('admin.intel.server.consolelog'),
                children: [],
                icon: null,
                visible: canWild('server_intel')
            },
        ],
        icon: PresentationChartLineIcon,
        visible: canWild('server_intel')
    },
    {
        label: 'Players',
        href: '#',
        active: route().current('admin.intel.player.*') || route().current('admin.rank.*'),
        children: [
            {
                label: 'List Players',
                href: route('admin.intel.player.list'),
                active: route().current('admin.intel.player.list'),
                children: [],
                icon: null,
                visible: canWild('player_intel_critical')
            },
            {
                label: 'Player Ranks',
                href: route('admin.rank.index'),
                active: route().current('admin.rank.*'),
                children: [],
                icon: null,
                visible: canWild('ranks')
            },
        ],
        icon: TrophyIcon,
        visible: canWild('player_intel_critical') || canWild('ranks')
    },
    {
        label: 'Users', href: '#', active: false, children: [
            {
                label: 'List Users',
                href: route('admin.user.index'),
                active: route().current('admin.user.*'),
                children: [],
                icon: null,
                visible: canWild('users')
            },
            {
                label: 'Roles & Permissions',
                href: route('admin.role.index'),
                active: route().current('admin.role.*'),
                children: [],
                icon: null,
                visible: canWild('roles')
            },
            {
                label: 'User Badges',
                href: route('admin.badge.index'),
                active: route().current('admin.badge.*'),
                children: [],
                icon: null,
                visible: canWild('badges')
            },
            {
                label: 'Online Users',
                href: route('admin.session.index'),
                active: route().current('admin.session.*'),
                children: [],
                icon: null,
                visible: canWild('sessions')
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
        visible: canWild('news')
    },
    {
        label: 'Polls',
        href: route('admin.poll.index'),
        active: route().current('admin.poll.*'),
        children: [],
        icon: ChartPieIcon,
        visible: canWild('polls')
    },
    {
        label: 'Downloads',
        href: route('admin.download.index'),
        active: route().current('admin.download.*'),
        children: [],
        icon: CloudArrowDownIcon,
        visible: canWild('downloads')
    },
    {
        label: 'Custom Pages',
        href: route('admin.custom-page.index'),
        active: route().current('admin.custom-page.*'),
        children: [],
        icon: DocumentTextIcon,
        visible: canWild('custom_pages')
    },
    {
        label: 'Ask DB',
        href: route('admin.ask-db.index'),
        active: route().current('admin.ask-db.*'),
        children: [],
        icon: CircleStackIcon,
        visible: canWild('ask_db')
    },
    {
        label: 'Custom Forms',
        active: false,
        children: [
            {
                label: 'List Forms',
                href: route('admin.custom-form.index'),
                active: route().current('admin.custom-form.index'),
                children: [],
                icon: null,
                visible: can('read custom_forms')
            },
            {
                label: 'User Submissions',
                href: route('admin.custom-form-submission.index'),
                active: route().current('admin.custom-form-submission.index'),
                children: [],
                icon: null,
                visible: can('read custom_form_submissions')
            },
        ],
        icon: ClipboardDocumentListIcon,
        visible: canWild('custom_forms')
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
            {
                label: 'Dangerzone',
                href: route('admin.setting.danger.show'),
                active: route().current('admin.setting.danger.show'),
                children: [],
                icon: null,
                visible: hasRole('superadmin')
            },
        ],
        icon: Cog8ToothIcon,
        visible: canWild('settings')
    },
    {
        label: 'Debug', href: '#', active: false, children: [
            {
                label: 'Pulse',
                href: '/admin/pulse',
                active: false,
                children: [],
                icon: null,
                visible: true,
                newtab: true
            },
            {
                label: 'Telescope',
                href: '/telescope',
                active: false,
                children: [],
                icon: null,
                visible: true,
                newtab: true
            },
        ],
        icon: BugAntIcon,
        visible: hasRole('superadmin')
    },
];
</script>

<template>
  <div
    :class="[
      'min-h-screen fixed bg-white shadow dark:bg-cool-gray-800 z-10 duration-300',
      collapsed ? 'w-16' : 'w-64'
    ]"
  >
    <div class="h-screen overflow-y-auto">
      <div :class="['px-4 mt-2 flex', collapsed ? 'justify-center' : 'justify-end']">
        <button @click.prevent="$emit('toggleCollapse')">
          <ChevronDoubleLeftIcon
            :class="[
              'h-6 w-6 p-0.5 text-gray-400 hover:text-gray-600 dark:text-gray-600 dark:hover:text-gray-400',
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

      <div
        v-if="!collapsed"
        class="mt-10 text-xs text-center text-gray-600 dark:text-gray-500"
      >
        {{ __("Web Version:") }}&nbsp;{{ $page.props.webVersion || 'unknown' }}
      </div>
    </div>
  </div>
</template>
