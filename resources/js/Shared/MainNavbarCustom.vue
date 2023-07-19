<script>
import JetResponsiveNavLink from '@/Jetstream/ResponsiveNavLink.vue';
import Search from '@/Shared/Search.vue';
import ColorThemeToggle from '@/Components/ColorThemeToggle.vue';
import NavDynamicItem from '@/Components/Navigation/NavDynamicItem.vue';
import NavDynamicItemResponsive from '@/Components/Navigation/NavDynamicItemResponsive.vue';
import {useAuthorizable} from '@/Composables/useAuthorizable';

export default {
    components: {
        NavDynamicItemResponsive,
        NavDynamicItem,
        JetResponsiveNavLink,
        Search,
        ColorThemeToggle,
    },
    setup() {
        const {canWild, isStaff} = useAuthorizable();
        return {canWild, isStaff};
    },

    data() {
        return {
            leftItems: window._customnav.data?.left ?? [],
            middleItems: window._customnav.data?.middle ?? [],
            rightItems: window._customnav.data?.right ?? [],
            showingNavigationDropdown: false,
        };
    },

    computed: {
        canShowAdminSidebar() {
            return this.isStaff(this.$page.props.auth.user);
        },
        isStickyHeader() {
            return this.$page.props.generalSettings.enable_sticky_header_menu;
        },
    },

    methods: {
        logout() {
            this.$inertia.post(route('logout'));
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
            @logout="logout"
          />
        </div>

        <div class="right-menu-items flex space-x-4">
          <NavDynamicItem
            v-for="item in rightItems"
            :key="item.key"
            :can-show-admin-sidebar="canShowAdminSidebar"
            :item="item"
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
          @logout="logout"
        />

        <NavDynamicItemResponsive
          v-for="item in middleItems"
          :key="item.key"
          :can-show-admin-sidebar="canShowAdminSidebar"
          :item="item"
          @logout="logout"
        />

        <NavDynamicItemResponsive
          v-for="item in rightItems"
          :key="item.key"
          :can-show-admin-sidebar="canShowAdminSidebar"
          :item="item"
          @logout="logout"
        />
      </div>

      <!-- Responsive Settings Options / Not configurable by CustomNav -->
      <div
        v-if="$page.props.auth.user"
        class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-700"
      >
        <inertia-link
          as="div"
          :href="route('user.public.get', $page.props.auth.user.username)"
          class="flex items-center px-4"
        >
          <div
            v-if="$page.props.jetstream.managesProfilePhotos"
            class="flex-shrink-0 mr-3"
          >
            <img
              class="object-cover w-10 h-10 rounded-full"
              :src="$page.props.auth.user.profile_photo_url"
              :alt="$page.props.auth.user.name"
            >
          </div>

          <div>
            <div class="text-base font-medium text-gray-800 dark:text-gray-300">
              {{ $page.props.auth.user.name }}
            </div>
            <div class="text-sm font-medium text-gray-500">
              {{ $page.props.auth.user.email }}
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
</template>
