<script>
import JetApplicationMark from '@/Jetstream/ApplicationMark.vue';
import JetSidebarLink from '@/Jetstream/SidebarLink.vue';
import JetResponsiveNavLink from '@/Jetstream/ResponsiveNavLink.vue';
import Search from '@/Shared/Search.vue';
import ColorThemeToggle from '@/Components/ColorThemeToggle.vue';
import Icon from '@/Components/Icon.vue';
import NavDynamicItem from '@/Components/Navigation/NavDynamicItem.vue';
import NavDynamicItemResponsive from '@/Components/Navigation/NavDynamicItemResponsive.vue';

export default {
    components: {
        NavDynamicItemResponsive,
        NavDynamicItem,
        JetApplicationMark,
        Icon,
        JetResponsiveNavLink,
        Search,
        ColorThemeToggle,
        JetSidebarLink,
    },

    data() {
        return {
            leftItems: window._customnav.data?.left ?? [],
            middleItems: window._customnav.data?.middle ?? [],
            rightItems: window._customnav.data?.right ?? [],
            isAdminSidebarOpen: false,
            showingNavigationDropdown: false,
        };
    },

    computed: {
        canShowAdminSidebar() {
            return this.isStaff(this.$page.props.user);
        },
        isStickyHeader() {
            return this.$page.props.generalSettings.enable_sticky_header_menu;
        },
    },

    mounted() {
        document.addEventListener('keydown', e => {
            if (e.keyCode == 27 && this.isAdminSidebarOpen) this.isAdminSidebarOpen = false;
        });
    },

    methods: {
        logout() {
            this.$inertia.post(route('logout'));
        },

        adminDrawer() {
            this.isAdminSidebarOpen = !this.isAdminSidebarOpen;
        },
    }
};
</script>

<template>
  <nav
    class="bg-white shadow dark:bg-cool-gray-800 z-40 w-full"
    :class="{'sticky top-0': isStickyHeader, 'overflow-y-auto': isStickyHeader && showingNavigationDropdown}"
  >
    <!-- Primary Navigation Menu -->
    <div class="px-4 mx-auto max-w-11xl md:px-6 lg:px-8">
      <div class="flex justify-between font-semibold h-14">
        <div class="left-menu-items flex space-x-4">
          <NavDynamicItem
            v-for="item in leftItems"
            :key="item.key"
            :can-show-admin-sidebar="canShowAdminSidebar"
            :item="item"
            @open-admin-sidebar="adminDrawer"
            @logout="logout"
          />
        </div>

        <div class="middle-menu-items flex space-x-4">
          <!--              Add middle items here-->
          <NavDynamicItem
            v-for="item in middleItems"
            :key="item.key"
            :can-show-admin-sidebar="canShowAdminSidebar"
            :item="item"
            @open-admin-sidebar="adminDrawer"
            @logout="logout"
          />
        </div>

        <div class="right-menu-items flex space-x-4">
          <NavDynamicItem
            v-for="item in rightItems"
            :key="item.key"
            :can-show-admin-sidebar="canShowAdminSidebar"
            :item="item"
            @open-admin-sidebar="adminDrawer"
            @logout="logout"
          />
        </div>

        <!-- Hamburger -->
        <div class="flex items-center -mr-2 md:hidden">
          <color-theme-toggle class="flex items-center justify-center space-x-8 md:ml-8 md:hidden" />

          <button
            class="inline-flex items-center justify-center p-2 text-gray-400 transition duration-150 ease-in-out rounded-md dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-cool-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-cool-gray-900 focus:text-gray-500 dark:focus:text-gray-200"
            @click="showingNavigationDropdown = ! showingNavigationDropdown"
          >
            <svg
              class="w-6 h-6"
              stroke="currentColor"
              fill="none"
              viewBox="0 0 24 24"
            >
              <path
                :class="{'hidden': showingNavigationDropdown, 'inline-flex': ! showingNavigationDropdown }"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 6h16M4 12h16M4 18h16"
              />
              <path
                :class="{'hidden': ! showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div
      :class="{'block': showingNavigationDropdown, 'hidden': ! showingNavigationDropdown}"
      class="md:hidden"
    >
      <div class="pt-2 pb-3 space-y-1">
        <NavDynamicItemResponsive
          v-for="item in leftItems"
          :key="item.key"
          :can-show-admin-sidebar="canShowAdminSidebar"
          :item="item"
          @open-admin-sidebar="adminDrawer"
          @logout="logout"
        />

        <NavDynamicItemResponsive
          v-for="item in middleItems"
          :key="item.key"
          :can-show-admin-sidebar="canShowAdminSidebar"
          :item="item"
          @open-admin-sidebar="adminDrawer"
          @logout="logout"
        />

        <NavDynamicItemResponsive
          v-for="item in rightItems"
          :key="item.key"
          :can-show-admin-sidebar="canShowAdminSidebar"
          :item="item"
          @open-admin-sidebar="adminDrawer"
          @logout="logout"
        />
      </div>

      <!-- Responsive Settings Options / Not configurable by CustomNav -->
      <div
        v-if="$page.props.user"
        class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-700"
      >
        <inertia-link
          as="div"
          :href="route('user.public.get', $page.props.user.username)"
          class="flex items-center px-4"
        >
          <div
            v-if="$page.props.jetstream.managesProfilePhotos"
            class="flex-shrink-0 mr-3"
          >
            <img
              class="object-cover w-10 h-10 rounded-full"
              :src="$page.props.user.profile_photo_url"
              :alt="$page.props.user.name"
            >
          </div>

          <div>
            <div class="text-base font-medium text-gray-800 dark:text-gray-300">
              {{ $page.props.user.name }}
            </div>
            <div class="text-sm font-medium text-gray-500">
              {{ $page.props.user.email }}
            </div>
          </div>
        </inertia-link>

        <div class="mt-3 space-y-1">
          <jet-responsive-nav-link
            :href="route('profile.show')"
            :active="route().current('profile.show')"
          >
            {{ __("Edit Profile") }}
          </jet-responsive-nav-link>

          <jet-responsive-nav-link
            :href="route('linked-player.list')"
            :active="route().current('linked-player.list')"
          >
            {{ __("Linked Players") }}
          </jet-responsive-nav-link>

          <jet-responsive-nav-link
            :href="route('notification.index')"
            :active="route().current('notification.index')"
          >
            {{ __("Notifications") }}
          </jet-responsive-nav-link>

          <jet-responsive-nav-link
            v-if="$page.props.jetstream.hasApiFeatures"
            :href="route('api-tokens.index')"
            :active="route().current('api-tokens.index')"
          >
            API Tokens
          </jet-responsive-nav-link>

          <!-- Authentication -->
          <form
            method="POST"
            @submit.prevent="logout"
          >
            <jet-responsive-nav-link as="button">
              {{ __("Logout") }}
            </jet-responsive-nav-link>
          </form>
        </div>
      </div>

      <div class="flex pb-2 just-center">
        <search class="inline-block md:hidden" />
      </div>
    </div>
  </nav>

  <!-- Sidebar of Admin if User is Admin -->
  <aside
    v-if="canShowAdminSidebar"
    class="fixed top-0 left-0 z-50 h-full overflow-auto transition-all duration-300 ease-in-out transform bg-white shadow w-72 dark:bg-cool-gray-800"
    :class="isAdminSidebarOpen ? 'translate-x-0' : '-translate-x-full'"
  >
    <span class="flex items-center w-full p-4 border-b border-gray-200 px-7 dark:border-gray-700 h-14">
      <inertia-link
        :href="route('home')"
      >
        <jet-application-mark class="block w-auto h-9" />
      </inertia-link>
      <button
        aria-label="Open Menu"
        class="ml-2 focus:outline-none"
        @click="adminDrawer"
      >
        <icon
          name="close"
          class="text-gray-500 dark:text-gray-400"
        />
      </button>
    </span>

    <jet-sidebar-link
      v-if="canWild('servers')"
      :href="route('admin.server.index')"
      :active="route().current('admin.server.index')"
    >
      <template #icon>
        <icon name="server" />
      </template>
      {{ __("Servers") }}
    </jet-sidebar-link>

    <jet-sidebar-link
      v-if="canWild('users')"
      :href="route('admin.user.index')"
      :active="route().current('admin.user.index')"
    >
      <template #icon>
        <icon
          name="users"
          class="w-5 h-5"
        />
      </template>
      {{ __("Users") }}
    </jet-sidebar-link>

    <jet-sidebar-link
      v-if="canWild('roles')"
      :href="route('admin.role.index')"
      :active="route().current('admin.role.index')"
    >
      <template #icon>
        <icon name="shield-check" />
      </template>
      {{ __("User Roles") }}
    </jet-sidebar-link>

    <jet-sidebar-link
      v-if="canWild('badges')"
      :href="route('admin.badge.index')"
      :active="route().current('admin.badge.index')"
    >
      <template #icon>
        <icon
          name="question-badge"
          class="w-5 h-5"
        />
      </template>
      {{ __("User Badges") }}
    </jet-sidebar-link>

    <jet-sidebar-link
      v-if="canWild('ranks')"
      :href="route('admin.rank.index')"
      :active="route().current('admin.rank.index')"
    >
      <template #icon>
        <icon name="degree-hat" />
      </template>
      {{ __("Player Ranks") }}
    </jet-sidebar-link>

    <jet-sidebar-link
      v-if="canWild('news')"
      :href="route('admin.news.index')"
      :active="route().current('admin.news.index')"
    >
      <template #icon>
        <icon name="newspaper" />
      </template>
      {{ __("News") }}
    </jet-sidebar-link>

    <jet-sidebar-link
      v-if="canWild('polls')"
      :href="route('admin.poll.index')"
      :active="route().current('admin.poll.index')"
    >
      <template #icon>
        <icon
          class="w-5 h-5"
          name="chart-pie"
        />
      </template>
      {{ __("Polls") }}
    </jet-sidebar-link>

    <jet-sidebar-link
      v-if="canWild('custom_pages')"
      :href="route('admin.custom-page.index')"
      :active="route().current('admin.custom-page.index')"
    >
      <template #icon>
        <icon
          class="w-5 h-5"
          name="collection"
        />
      </template>
      {{ __("Custom Pages") }}
    </jet-sidebar-link>

    <jet-sidebar-link
      v-if="canWild('sessions')"
      :href="route('admin.session.index')"
      :active="route().current('admin.session.index')"
    >
      <template #icon>
        <icon
          class="w-5 h-5"
          name="finger-print"
        />
      </template>
      {{ __("User Sessions") }}
    </jet-sidebar-link>

    <jet-sidebar-link
      v-if="canWild('settings')"
      :href="route('admin.setting.general.show')"
      :active="route().current('admin.setting.general.show')"
    >
      <template #icon>
        <icon name="cog" />
      </template>
      {{ __("Settings") }}
    </jet-sidebar-link>


    <div class="mt-4 text-xs text-center text-gray-600 dark:text-gray-400">
      {{ __("Web Version:") }}&nbsp;{{ $page.props.webVersion || 'unknown' }}
    </div>
  </aside>
</template>
