<template>
  <div v-if="enabled && user">
    <div class="p-3 bg-white dark:bg-surface-800 rounded shadow space-y-2">
      <h3 class="font-extrabold text-secondary-800 dark:text-secondary-200">
        {{ __("Newest User") }}
      </h3>
      <inertia-link
        as="a"
        :href="route('user.public.get', user.username)"
        class="cursor-pointer flex space-x-2 border dark:border-secondary-700 rounded-tl-md rounded-tr-xl rounded-b-3xl p-2 items-center hover:border-primary-400 dark:hover:border-primary-400"
      >
        <img
          class="w-14 h-14 rounded-full"
          :src="user.profile_photo_url"
          alt="Avatar"
        >
        <div class="flex flex-col">
          <div class="break-all">
            <span
              class="text-secondary-800 dark:text-secondary-300 font-semibold"
              :style="[user.roles[0].color ? {color: user.roles[0].color} : null]"
            >{{ user.name }}</span>
            <span class="text-secondary-500 dark:text-secondary-400">@{{ user.username }}</span>
          </div>
          <span
            v-tippy
            class="text-sm text-secondary-700 dark:text-secondary-500 focus:outline-none"
            :title="formatToDayDateString(user.created_at)"
          >{{ __("Joined") }}&nbsp;{{ formatTimeAgoToNow(user.created_at) }}</span>
        </div>
      </inertia-link>
    </div>
  </div>
</template>

<script>
import { useHelpers } from '@/Composables/useHelpers';


export default {
    props: {
        user: Object,
        enabled: Boolean
    },
    setup() {
        const {formatTimeAgoToNow,formatToDayDateString} = useHelpers();
        return {formatTimeAgoToNow,formatToDayDateString};
    },
};
</script>
