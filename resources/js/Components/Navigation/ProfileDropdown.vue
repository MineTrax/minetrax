<template>
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
          @click="emit('open-admin-sidebar')"
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

        <div class="border-t border-gray-100 dark:border-cool-gray-700" />

        <!-- Authentication -->
        <form @submit.prevent="emit('logout')">
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
</template>

<script setup>
import JetDropdownLink from '@/Jetstream/DropdownLink.vue';
import JetDropdown from '@/Jetstream/Dropdown.vue';

const emit = defineEmits(['open-admin-sidebar', 'logout']);

defineProps({
    canShowAdminSidebar: {
        type: Boolean,
        default: false,
    },
});
</script>
