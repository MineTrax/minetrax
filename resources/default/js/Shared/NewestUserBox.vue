<template>
  <Card v-if="enabled && user">
    <CardContent class="p-3 space-y-2">
      <h3 class="font-extrabold text-card-foreground">
        {{ __("Newest User") }}
      </h3>
      <inertia-link
        as="a"
        :href="route('user.public.get', user.username)"
        class="cursor-pointer flex space-x-3 border border-border rounded-lg p-3 items-center hover:border-primary transition-colors duration-200 hover:bg-muted/50"
      >
        <img
          class="w-12 h-12 rounded-full flex-shrink-0 ring-2 ring-border"
          :src="user.profile_photo_url"
          alt="Avatar"
        >
        <div class="flex flex-col min-w-0 flex-1">
          <div class="break-all">
            <span
              class="text-card-foreground font-semibold text-sm"
              :style="[user.roles[0].color ? {color: user.roles[0].color} : null]"
            >{{ user.name }}</span>
            <span class="text-muted-foreground text-sm ml-1">@{{ user.username }}</span>
          </div>
          <span
            v-tippy
            class="text-xs text-muted-foreground focus:outline-none"
            :title="formatToDayDateString(user.created_at)"
          >{{ __("Joined") }} {{ formatTimeAgoToNow(user.created_at) }}</span>
        </div>
      </inertia-link>
    </CardContent>
  </Card>
</template>

<script setup>
import { useHelpers } from '@/Composables/useHelpers';
import { useTranslations } from '@/Composables/useTranslations';
import {
  Card,
  CardContent,
} from '@/Components/ui/card'

defineProps({
  user: Object,
  enabled: Boolean
});

const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString } = useHelpers();
</script>
