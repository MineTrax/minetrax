<template>
  <div
    :class="{'bg-light-blue-50 rounded dark:bg-cool-gray-600 dark:bg-opacity-25' : notification.read_at == null}"
    @click="markAsRead"
  >
    <inertia-link
      v-if="notification.type === 'App\\Notifications\\PostCommentedByUser'"
      as="div"
      :href="route('post.show',notification.data.post_id)"
      class="flex cursor-pointer"
    >
      <img
        :src="notification.data.causer.profile_photo_url"
        alt="Profile Picture"
        class="w-10 h-10 rounded-full m-1"
      >
      <div class="m-1">
        <p><b>{{ notification.data.causer.name }}</b>(@{{ notification.data.causer.username }}) commented on your post.</p>
        <p class="text-xs">
          {{ formatTimeAgoToNow(notification.created_at) }}
        </p>
      </div>
    </inertia-link>

    <inertia-link
      v-if="notification.type === 'App\\Notifications\\PostLikedByUser'"
      as="div"
      :href="route('post.show',notification.data.post_id)"
      class="flex cursor-pointer"
    >
      <img
        :src="notification.data.causer.profile_photo_url"
        alt="Profile Picture"
        class="w-10 h-10 rounded-full m-1"
      >
      <div class="m-1">
        <p><b>{{ notification.data.causer.name }}</b>(@{{ notification.data.causer.username }}) liked your post.</p>
        <p class="text-xs">
          {{ formatTimeAgoToNow(notification.created_at) }}
        </p>
      </div>
    </inertia-link>

    <inertia-link
      v-if="notification.type === 'App\\Notifications\\UserYouAreMuted'"
      as="div"
      :href="route('profile.show')"
      class="flex cursor-pointer"
    >
      <img
        :src="notification.data.causer.profile_photo_url"
        alt="Profile Picture"
        class="w-10 h-10 rounded-full m-1"
      >
      <div class="m-1">
        <p><b>{{ notification.data.causer.name }}</b>(@{{ notification.data.causer.username }}) muted you.</p>
        <p class="text-xs">
          {{ formatTimeAgoToNow(notification.created_at) }}
        </p>
      </div>
    </inertia-link>

    <inertia-link
      v-if="notification.type === 'App\\Notifications\\UserYouAreBanned'"
      as="div"
      :href="route('profile.show')"
      class="flex cursor-pointer"
    >
      <img
        :src="notification.data.causer.profile_photo_url"
        alt="Profile Picture"
        class="w-10 h-10 rounded-full m-1"
      >
      <div class="m-1">
        <p><b>{{ notification.data.causer.name }}</b>(@{{ notification.data.causer.username }}) banned you.</p>
        <p class="text-xs">
          {{ formatTimeAgoToNow(notification.created_at) }}
        </p>
      </div>
    </inertia-link>

    <inertia-link
      v-if="notification.type === 'App\\Notifications\\CustomFormSubmissionCreatedNotification'"
      as="div"
      :href="route('admin.custom-form-submission.show', notification.data.id)"
      class="flex cursor-pointer"
    >
      <img
        v-if="notification.data.causer"
        :src="notification.data.causer.profile_photo_url"
        alt="Profile Picture"
        class="w-10 h-10 rounded-full m-1"
      >
      <img
        v-else
        src="/images/steve.png"
        alt="Anonymous"
        class="w-10 h-10 rounded-full m-1"
      >
      <div class="m-1">
        <p>
          <span v-if="notification.data.causer">
            <b>{{ notification.data.causer.name }}</b>(@{{ notification.data.causer.username }})
          </span>
          <span v-else>
            <b>An anonymous user</b>
          </span>
          submitted a custom form.
        </p>
        <p class="text-xs">
          {{ formatTimeAgoToNow(notification.created_at) }}
        </p>
      </div>
    </inertia-link>
  </div>
</template>

<script>
import { useHelpers } from '@/Composables/useHelpers';

export default {
    name: 'Notification',
    props: {
        notification: Object,
    },
    setup() {
        const {formatTimeAgoToNow, formatToDayDateString} = useHelpers();
        return {formatTimeAgoToNow, formatToDayDateString};
    },

    methods: {
        markAsRead() {
            axios.post(route('notification.mark-as-read'), {
                notifications: [this.notification.id]
            });
        }
    }
};
</script>
