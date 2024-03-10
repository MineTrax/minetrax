<template>
  <app-layout>
    <app-head
      :title="__('Your Notifications')"
    />

    <div class="py-4 px-2 md:py-12 md:px-10 max-w-7xl mx-auto">
      <div class="flex justify-between mb-8">
        <h1 class="font-bold text-3xl text-gray-500 dark:text-gray-300">
          {{ __("Notifications") }}
        </h1>
        <div class="flex">
          <inertia-link
            as="button"
            :href="route('notification.mark-as-read')"
            method="post"
            :preserve-state="false"
            class="mr-1 inline-flex items-center px-4 py-2 bg-light-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-light-blue-700 active:bg-light-blue-600 focus:outline-none focus:border-light-blue-700 focus:shadow-outline-gray transition ease-in-out duration-150"
          >
            <span>{{ __("Mark all as read") }}</span>
          </inertia-link>
        </div>
      </div>
      <div class="flex flex-col md:flex-row md:space-x-4">
        <div class="flex flex-col space-y-4  md:w-8/12 bg-white p-4 rounded dark:bg-cool-gray-800 text-gray-700 dark:text-gray-300">
          <infinite-scroll :load-more="loadNotifications">
            <transition-group
              name="list"
              tag="div"
              class="space-y-1"
            >
              <notification
                v-for="notification in notifications.data"
                :key="notification.id"
                :notification="notification"
              />
            </transition-group>
          </infinite-scroll>
          <div
            v-if="notifications.data.length <= 0"
            :key="999999999"
            class="flex items-center justify-center italic text-gray-500 dark:text-gray-400"
          >
            {{ __("No notifications to show.") }}
          </div>
        </div>

        <div class="hidden md:block md:w-4/12 flex-1 space-y-4 mt-4 md:mt-0">
          <server-status-box />
          <shout-box />
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import ServerStatusBox from '@/Shared/ServerStatusBox.vue';
import ShoutBox from '@/Shared/ShoutBox.vue';
import InfiniteScroll from '@/Components/InfiniteScroll.vue';
import Notification from '@/Components/Notification.vue';

export default {

    components: {
        Notification,
        ShoutBox,
        ServerStatusBox,
        AppLayout,
        InfiniteScroll
    },
    props: {
        notificationsList: Object,
    },

    data() {
        return {
            notifications: this.notificationsList,
            loading: false,
            error: null
        };
    },

    methods: {
        loadNotifications() {
            if (!this.notifications.next_page_url) {
                return Promise.resolve();
            }

            this.loading = true;
            return axios(this.notifications.next_page_url).then(response => {
                this.notifications = {
                    ...response.data,
                    data: [...this.notifications.data, ...response.data.data]
                };
            }).finally(() => {
                this.loading = false;
            });
        }
    },
};
</script>
