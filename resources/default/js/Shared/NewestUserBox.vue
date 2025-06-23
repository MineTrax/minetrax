<template>
  <Card v-if="enabled && user">
    <CardContent class="p-3 space-y-2">
      <h3 class="font-extrabold text-foreground dark:text-foreground">
        {{ __("Newest User") }}
      </h3>
      <inertia-link
        as="a"
        :href="route('user.public.get', user.username)"
        class="cursor-pointer flex space-x-2 border dark:border-foreground rounded-tl-md rounded-tr-xl rounded-b-3xl p-2 items-center hover:border-primary dark:hover:border-primary"
      >
        <img
          class="w-14 h-14 rounded-full"
          :src="user.profile_photo_url"
          alt="Avatar"
        >
        <div class="flex flex-col">
          <div class="break-all">
            <span
              class="text-foreground dark:text-foreground font-semibold"
              :style="[user.roles[0].color ? {color: user.roles[0].color} : null]"
            >{{ user.name }}</span>
            <span class="text-foreground dark:text-foreground">@{{ user.username }}</span>
          </div>
          <span
            v-tippy
            class="text-sm text-foreground dark:text-foreground focus:outline-none"
            :title="formatToDayDateString(user.created_at)"
          >{{ __("Joined") }}&nbsp;{{ formatTimeAgoToNow(user.created_at) }}</span>
        </div>
      </inertia-link>
    </CardContent>
  </Card>
</template>

<script>
import { useHelpers } from '@/Composables/useHelpers';
import {
  Card,
  CardContent,
} from '@/Components/ui/card'

export default {
    components: {
        Card,
        CardContent,
    },
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
