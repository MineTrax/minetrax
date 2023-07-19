<template>
  <div
    v-if="$page.props.auth.user"
    class="relative ml-3 font-semibold dark:text-gray-400"
  >
    <jet-dropdown
      align="right"
      width="48"
    >
      <template #trigger>
        <button
          v-if="$page.props.jetstream.managesProfilePhotos"
          class="flex text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 items-center text-sm font-semibold transition duration-150 ease-in-out border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 dark:focus:border-cool-gray-700"
        >
          {{ $page.props.auth.user.name }}
          <img
            class="h-8 w-8 ml-0.5 rounded-full object-cover"
            :src="$page.props.auth.user.profile_photo_url"
            :alt="$page.props.auth.user.name"
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
            {{ $page.props.auth.user.name }}

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
        <jet-dropdown-link
          v-if="canShowAdminSidebar"
          class="text-sm"
          :href="route('admin.dashboard')"
        >
          {{ __("Admin Section") }}
        </jet-dropdown-link>

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
          :href="route('user.public.get', $page.props.auth.user.username)"
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

const emit = defineEmits(['logout']);

defineProps({
    canShowAdminSidebar: {
        type: Boolean,
        default: false,
    },
});
</script>
