<template>
  <jet-dropdown
    width="full"
    class="ml-4 relative"
  >
    <template #trigger>
      <div class="relative hover:cursor-pointer">
        <BellIcon
          :class="[
            'w-5 h-5 transition-all duration-300',
            notifications && notifications.length > 0
              ? 'text-error-500 dark:text-error-400 animate-pulse'
              : 'text-foreground dark:text-foreground dark:hover:text-foreground'
          ]"
        />
        <span class="sr-only">{{ __("Notifications") }}</span>

        <!-- Enhanced notification badge with glow and bounce -->
        <div
          v-if="notifications && notifications.length > 0"
          class="absolute inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-error-500 border-2 border-white rounded-full -top-2 -end-2 dark:border-foreground cursor-pointer animate-bounce shadow-lg"
          style="animation-duration: 2s; box-shadow: 0 0 10px rgba(239, 68, 68, 0.6);"
        >
          {{ notifications.length }}
        </div>

        <!-- Pulsing ring effect for extra attention -->
        <div
          v-if="notifications && notifications.length > 0"
          class="absolute -inset-1 bg-error-500 rounded-full opacity-20 animate-ping"
          style="animation-duration: 2s;"
        ></div>
      </div>
    </template>

    <template #content>
      <div class="container p-3 pb-2 w-80">
        <div class="flex justify-between">
          <div class="block text-xs text-foreground">
            {{ __("Notifications") }}
          </div>
          <inertia-link
            as="button"
            :href="route('notification.mark-as-read')"
            method="post"
            :preserve-state="false"
            class="block text-xs text-primary hover:underline"
          >
            {{ __("Mark all as read") }}
          </inertia-link>
        </div>

        <!-- Loading Spinner -->
        <div
          v-if="loading"
          class="flex p-4 justify-center"
        >
          <svg
            class="animate-spin -ml-1 mr-3 h-5 w-5 text-primary"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
          >
            <circle
              class="opacity-25"
              cx="12"
              cy="12"
              r="10"
              stroke="currentColor"
              stroke-width="4"
            />
            <path
              class="opacity-75"
              fill="currentColor"
              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            />
          </svg>
        </div>

        <error-message v-if="error">
          {{ error }}
        </error-message>

        <div class="flex flex-col text-sm text-foreground dark:text-foreground mt-2 h-96 overflow-y-auto space-y-1">
          <notification
            v-for="notification in notifications"
            :key="notification.id"
            :notification="notification"
          />
          <div
            v-if="!loading && notifications.length <= 0"
            :key="999999999"
            class="flex items-center justify-center italic text-foreground dark:text-foreground p-4"
          >
            {{ __("No new notifications to show.") }}
          </div>
        </div>

        <div class="flex justify-center text-primary text-xs mt-2 hover:underline cursor-pointer">
          <inertia-link
            as="a"
            :href="route('notification.index')"
          >
            {{ __("View All") }}
          </inertia-link>
        </div>
      </div>
    </template>
  </jet-dropdown>
</template>

<script>
import JetDropdown from '@/Jetstream/Dropdown.vue';
import Notification from '@/Components/Notification.vue';
import ErrorMessage from '@/Components/ErrorMessage.vue';
import { BellIcon } from '@heroicons/vue/24/outline';

export default {
    name: 'NotificationDropdown',
    components: {
        ErrorMessage,
        Notification,
        JetDropdown,
        BellIcon,
    },
    data() {
        return {
            loading: true,
            error: null,
            notifications: []
        };
    },
    mounted() {
        let routeToHit = route('notification.index', {
            'unread_only': true
        });
        axios.get(routeToHit).then(response => {
            this.notifications = response.data.data;
        }).catch(e => {
            console.log('Error fetching notifications: ', e);
            this.notifications = [];
        }).finally(() => {
            this.loading = false;
        });
    }
};
</script>
