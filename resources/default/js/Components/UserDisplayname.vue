<script setup>
import Icon from '@/Components/Icon.vue';

defineProps({
    user: {
        type: Object,
        required: true,
    },
    textClass: {
        type: String,
        default: 'text-base'
    },
    iconClass: {
        type: String,
        default: 'w-5 h-5'
    },
    showUsername: {
        type: Boolean,
        default: false
    }
});
</script>

<template>
  <div
    class="leading-6 text-black dark:text-gray-200"
    :class="textClass"
    :style="[user.roles[0].color ? {color: user.roles[0].color} : null]"
  >
    <span class="font-semibold">
      {{ user.name }}
    </span>
    <span
      v-if="showUsername"
      class="text-gray-500 dark:text-gray-300"
    > @{{ user.username }}</span>

    <Icon
      v-if="user.verified_at"
      v-tippy
      name="verified-check-fill"
      :title="__('Verified Account')"
      class="inline mb-1 fill-current focus:outline-none text-light-blue-400"
      :class="iconClass"
    />
    <Icon
      v-if="user.is_staff"
      v-tippy
      name="shield-check-fill"
      :title="__('Staff Member')"
      class="inline mb-1 text-amber-400 fill-current focus:outline-none"
      :class="iconClass"
    />
    <Icon
      v-if="user.muted_at"
      v-tippy
      name="volume-off-fill"
      :title="__('Muted User')"
      class="inline mb-1 text-red-500 fill-current focus:outline-none"
      :class="iconClass"
    />

    <template v-if="user.sticky_badges && user.sticky_badges.length > 0">
      <img
        v-for="badge in user.sticky_badges"
        :key="badge.id"
        v-tippy
        :title="badge.name"
        class="inline mb-1 ml-0.5 text-red-500 fill-current focus:outline-none"
        :class="iconClass"
        :src="badge.photo_url"
        :alt="badge.name"
      >
    </template>

    <slot />
  </div>
</template>
