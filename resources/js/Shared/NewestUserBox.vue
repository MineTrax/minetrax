<template>
  <div v-if="enabled && user">
    <div class="p-3 bg-white dark:bg-cool-gray-800 rounded shadow space-y-2">
      <h3 class="font-extrabold text-gray-800 dark:text-gray-200">
        Newest User
      </h3>
      <inertia-link
        as="div"
        :href="route('user.public.get', user.username)"
        class="cursor-pointer flex space-x-2 border dark:border-gray-700 rounded-tl-md rounded-tr-xl rounded-b-3xl p-2 items-center hover:border-light-blue-400 dark:hover:border-light-blue-400"
      >
        <img
          class="w-14 h-14 rounded-full"
          :src="user.profile_photo_url"
          alt="Avatar"
        >
        <div class="flex flex-col">
          <div>
            <span
              class="text-gray-800 dark:text-gray-300 font-semibold"
              :style="[user.roles[0].color ? {color: user.roles[0].color} : null]"
            >{{ user.name }}</span>
            <span class="text-gray-500 dark:text-gray-400">@{{ user.username }}</span>
          </div>
          <span
            v-tippy
            class="text-sm text-gray-700 dark:text-gray-500 focus:outline-none"
            :content="format(new Date(user.created_at), 'E, do MMM yyyy, h:mm aaa')"
          >Joined {{ formatDistanceToNowStrict(new Date(user.created_at), { addSuffix: true }) }}</span>
        </div>
      </inertia-link>
    </div>
  </div>
</template>

<script>
import { formatDistanceToNowStrict, format } from 'date-fns';

export default {
    props: {
        user: Object,
        enabled: Boolean
    },
    data() {
        return {
            formatDistanceToNowStrict: formatDistanceToNowStrict,
            format: format
        };
    }
};
</script>
