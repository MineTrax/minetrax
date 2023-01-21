<script>
import JetApplicationMark from '@/Jetstream/ApplicationMark.vue';
import JetDropdown from '@/Jetstream/Dropdown.vue';
import JetDropdownLink from '@/Jetstream/DropdownLink.vue';
import JetNavLink from '@/Jetstream/NavLink.vue';
import JetSidebarLink from '@/Jetstream/SidebarLink.vue';
import JetResponsiveNavLink from '@/Jetstream/ResponsiveNavLink.vue';
import Search from '@/Shared/Search.vue';
import ColorThemeToggle from '@/Components/ColorThemeToggle.vue';
import NotificationDropdown from '@/Shared/NotificationDropdown.vue';
import Icon from '@/Components/Icon.vue';

export default {
    components: {
        JetApplicationMark,
        Icon,
        JetDropdown,
        JetNavLink,
        JetDropdownLink,
        JetResponsiveNavLink,
        Search,
        ColorThemeToggle,
        JetSidebarLink,
        NotificationDropdown,
    },

    data() {
        return {
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
        switchToTeam(team) {
            this.$inertia.put(route('current-team.update'), {
                'team_id': team.id
            }, {
                preserveState: false
            });
        },

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
    class="bg-white shadow dark:bg-cool-gray-800 z-50 w-full"
    :class="{'sticky top-0': isStickyHeader}"
  >
    <!-- Primary Navigation Menu -->
    <div class="px-4 mx-auto max-w-11xl md:px-6 lg:px-8">
      <div class="flex justify-between font-semibold h-14">
        <div class="flex">
          <!-- Logo -->
          <div class="flex items-center flex-shrink-0">
            <inertia-link :href="route('home')">
              <jet-application-mark class="block w-auto h-9" />
            </inertia-link>
            <button
              v-if="canShowAdminSidebar"
              v-tippy
              :title="__('Administration Section')"
              aria-label="Open Menu"
              class="ml-2 focus:outline-none"
              @click="adminDrawer"
            >
              <icon
                name="cog"
                class="w-6 h-6 text-gray-400 dark:text-gray-500 hover:animate-spin"
              />
            </button>
          </div>

          <!-- Navigation Links -->
          <div class="hidden space-x-8 md:-my-px md:ml-10 md:flex">
            <jet-nav-link
              v-if="$page.props.showHomeButton"
              :href="route('home')"
              :active="route().current('home')"
            >
              {{ __("Home") }}
            </jet-nav-link>
            <jet-nav-link
              :href="route('player.index')"
              :active="route().current('player.index')"
            >
              {{ __("Statistics") }}
            </jet-nav-link>
            <jet-nav-link
              :href="route('poll.index')"
              :active="route().current('poll.index')"
            >
              {{ __("Polls") }}
            </jet-nav-link>

            <div
              class="inline-flex items-center px-1 pt-1 text-sm leading-5 text-gray-500 transition duration-150 ease-in-out border-b-2 border-transparent hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300"
            >
              <jet-dropdown
                align="right"
                width="48"
              >
                <template #trigger>
                  <span class="inline-flex rounded-md">
                    <button
                      type="button"
                      class="inline-flex items-center py-2 text-sm font-semibold leading-4 text-gray-500 transition duration-150 ease-in-out border border-transparent rounded-md dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 focus:outline-none"
                    >
                      {{ __("Others") }}
                      <svg
                        class="ml-2 -mr-0.5 h-4 w-4"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                      >
                        <path
                          fill-rule="evenodd"
                          d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                          clip-rule="evenodd"
                        />
                      </svg>
                    </button>
                  </span>
                </template>

                <template #content>
                  <jet-dropdown-link
                    class="text-sm"
                    :href="route('news.index')"
                  >
                    {{ __("News") }}
                  </jet-dropdown-link>

                  <jet-dropdown-link
                    class="text-sm"
                    :href="route('staff.index')"
                  >
                    {{ __("Staff Members") }}
                  </jet-dropdown-link>

                  <jet-dropdown-link
                    v-for="customPage in $page.props.customPageList"
                    v-if="$page.props.customPageList"
                    :key="customPage.id"
                    :href="route('custom-page.show', customPage.path)"
                  >
                    {{ customPage.title }}
                  </jet-dropdown-link>

                  <jet-dropdown-link
                    class="text-sm"
                    :href="route('features.list')"
                  >
                    {{ __("Features") }}
                  </jet-dropdown-link>
                </template>
              </jet-dropdown>
            </div>
          </div>
        </div>

        <div class="hidden md:flex md:items-center md:ml-6">
          <search v-if="$page.props.user" />

          <notification-dropdown v-if="$page.props.user" />

          <div class="relative ml-3">
            <!-- Teams Dropdown -->
            <jet-dropdown
              v-if="$page.props.jetstream.hasTeamFeatures"
              align="right"
              width="60"
            >
              <template #trigger>
                <span class="inline-flex rounded-md">
                  <button
                    type="button"
                    class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out bg-white border border-transparent rounded-md hover:bg-gray-50 hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50"
                  >
                    {{ $page.props.user.current_team.name }}

                    <svg
                      class="ml-2 -mr-0.5 h-4 w-4"
                      xmlns="http://www.w3.org/2000/svg"
                      viewBox="0 0 20 20"
                      fill="currentColor"
                    >
                      <path
                        fill-rule="evenodd"
                        d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                        clip-rule="evenodd"
                      />
                    </svg>
                  </button>
                </span>
              </template>

              <template #content>
                <div class="w-60">
                  <!-- Team Management -->
                  <template v-if="$page.props.jetstream.hasTeamFeatures">
                    <div class="block px-4 py-2 text-xs text-gray-400">
                      Manage Team
                    </div>

                    <!-- Team Settings -->
                    <jet-dropdown-link
                      :href="route('teams.show', $page.props.user.current_team)"
                    >
                      Team Settings
                    </jet-dropdown-link>

                    <jet-dropdown-link
                      v-if="$page.props.jetstream.canCreateTeams"
                      :href="route('teams.create')"
                    >
                      Create New Team
                    </jet-dropdown-link>

                    <div class="border-t border-gray-100" />

                    <!-- Team Switcher -->
                    <div class="block px-4 py-2 text-xs text-gray-400">
                      Switch Teams
                    </div>

                    <template
                      v-for="team in $page.props.user.all_teams"
                      :key="team.id"
                    >
                      <form
                        @submit.prevent="switchToTeam(team)"
                      >
                        <jet-dropdown-link as="button">
                          <div class="flex items-center">
                            <svg
                              v-if="team.id == $page.props.user.current_team_id"
                              class="w-5 h-5 mr-2 text-green-400"
                              fill="none"
                              stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              stroke="currentColor"
                              viewBox="0 0 24 24"
                            >
                              <path
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                              />
                            </svg>
                            <div>{{ team.name }}</div>
                          </div>
                        </jet-dropdown-link>
                      </form>
                    </template>
                  </template>
                </div>
              </template>
            </jet-dropdown>
          </div>

          <!-- Settings Dropdown -->
          <div
            v-if="$page.props.user"
            class="relative ml-3 font-semibold dark:text-gray-400"
          >
            <jet-dropdown
              align="right"
              width="48"
            >
              <template #trigger>
                <button
                  v-if="$page.props.jetstream.managesProfilePhotos"
                  class="flex items-center text-sm font-semibold transition duration-150 ease-in-out border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 dark:focus:border-cool-gray-700"
                >
                  {{ $page.props.user.name }}
                  <img
                    class="h-8 w-8 ml-0.5 rounded-full object-cover"
                    :src="$page.props.user.profile_photo_url"
                    :alt="$page.props.user.name"
                  >
                </button>

                <span
                  v-else
                  class="inline-flex rounded-md"
                >
                  <button
                    type="button"
                    class="inline-flex items-center px-3 py-2 text-sm font-semibold leading-4 text-gray-500 transition duration-150 ease-in-out bg-white border border-transparent rounded-md hover:text-gray-700 focus:outline-none"
                  >
                    {{ $page.props.user.name }}

                    <svg
                      class="ml-2 -mr-0.5 h-4 w-4"
                      xmlns="http://www.w3.org/2000/svg"
                      viewBox="0 0 20 20"
                      fill="currentColor"
                    >
                      <path
                        fill-rule="evenodd"
                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                        clip-rule="evenodd"
                      />
                    </svg>
                  </button>
                </span>
              </template>

              <template #content>
                <div
                  v-if="canShowAdminSidebar"
                  class="block px-4 py-2 text-xs text-gray-400"
                >
                  {{ __("Staff") }}
                </div>
                <button
                  v-if="canShowAdminSidebar"
                  type="button"
                  class="block w-full px-4 py-2 text-sm font-semibold leading-5 text-left text-gray-700 transition duration-150 ease-in-out dark:text-gray-400 hover:bg-cool-gray-100 dark:hover:bg-cool-gray-900 focus:outline-none focus:bg-cool-gray-100 dark:focus:bg-cool-gray-900"
                  @click="adminDrawer"
                >
                  {{ __("Admin Menu") }}
                </button>
                <div
                  v-if="canShowAdminSidebar"
                  class="border-t border-gray-100 dark:border-cool-gray-700"
                />

                <!-- Account Management -->
                <div class="block px-4 py-2 text-xs text-gray-400">
                  {{ __("Manage Account") }}
                </div>

                <jet-dropdown-link
                  class="text-sm"
                  :href="route('user.public.get', $page.props.user.username)"
                >
                  {{ __("Public Profile") }}
                </jet-dropdown-link>

                <jet-dropdown-link :href="route('profile.show')">
                  {{ __("Edit Profile") }}
                </jet-dropdown-link>

                <jet-dropdown-link :href="route('linked-player.list')">
                  {{ __("Linked Players") }}
                </jet-dropdown-link>

                <jet-dropdown-link
                  v-if="$page.props.jetstream.hasApiFeatures"
                  :href="route('api-tokens.index')"
                >
                  API Tokens
                </jet-dropdown-link>

                <div class="border-t border-gray-100 dark:border-cool-gray-700" />

                <!-- Authentication -->
                <form @submit.prevent="logout">
                  <jet-dropdown-link
                    as="button"
                    btn-class="font-semibold"
                  >
                    {{ __("Logout") }}
                  </jet-dropdown-link>
                </form>
              </template>
            </jet-dropdown>
          </div>

          <color-theme-toggle
            v-if="$page.props.user"
            class="items-center justify-center hidden space-x-8 md:ml-8 md:flex"
          />
        </div>

        <div
          v-if="!$page.props.user"
          class="flex"
        >
          <search
            v-if="!$page.props.user"
            class="hidden mt-2 md:block"
          />

          <div class="hidden space-x-8 md:-my-px md:ml-8 md:flex">
            <jet-nav-link
              :href="route('login')"
              :active="route().current('login')"
            >
              {{ __("Login") }}
            </jet-nav-link>
          </div>
          <div class="hidden space-x-8 md:-my-px md:ml-8 md:flex">
            <jet-nav-link
              v-if="$page.props.hasRegistrationFeature"
              :href="route('register')"
              :active="route().current('register')"
            >
              {{ __("Register") }}
            </jet-nav-link>
          </div>
          <color-theme-toggle class="items-center justify-center hidden space-x-8 md:flex md:ml-8" />
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
        <jet-responsive-nav-link
          v-if="$page.props.showHomeButton"
          :href="route('home')"
          :active="route().current('home')"
        >
          {{ __("Home") }}
        </jet-responsive-nav-link>

        <jet-responsive-nav-link
          :href="route('player.index')"
          :active="route().current('player.index')"
        >
          {{ __("Statistics") }}
        </jet-responsive-nav-link>

        <jet-responsive-nav-link
          :href="route('poll.index')"
          :active="route().current('poll.index')"
        >
          {{ __("Polls") }}
        </jet-responsive-nav-link>

        <jet-responsive-nav-link
          :href="route('news.index')"
          :active="route().current('news.index')"
        >
          {{ __("News") }}
        </jet-responsive-nav-link>

        <jet-responsive-nav-link
          :href="route('staff.index')"
          :active="route().current('staff.index')"
        >
          {{ __("Staff Members") }}
        </jet-responsive-nav-link>

        <jet-responsive-nav-link
          v-for="customPage in $page.props.customPageList"
          v-if="$page.props.customPageList"
          :key="customPage.id"
          :href="route('custom-page.show', customPage.path)"
        >
          {{ customPage.title }}
        </jet-responsive-nav-link>

        <jet-responsive-nav-link
          :href="route('features.list')"
          :active="route().current('features.list')"
        >
          {{ __("Features") }}
        </jet-responsive-nav-link>


        <template v-if="!$page.props.user">
          <jet-responsive-nav-link
            :href="route('login')"
            :active="route().current('login')"
          >
            {{ __("Login") }}
          </jet-responsive-nav-link>
          <jet-responsive-nav-link
            v-if="$page.props.hasRegistrationFeature"
            :href="route('register')"
            :active="route().current('register')"
          >
            {{ __("Register") }}
          </jet-responsive-nav-link>
        </template>
      </div>

      <!-- Responsive Settings Options -->
      <div
        v-if="$page.props.user"
        class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-700"
      >
        <div class="flex items-center px-4">
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
        </div>

        <div class="mt-3 space-y-1">
          <jet-responsive-nav-link
            :href="route('user.public.get', $page.props.user.username)"
            :active="route().current('user.public.get')"
          >
            {{ __("Public Profile") }}
          </jet-responsive-nav-link>

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
    class="fixed top-0 left-0 z-30 h-full overflow-auto transition-all duration-300 ease-in-out transform bg-white shadow w-72 dark:bg-cool-gray-800"
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
        <icon name="close" />
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
