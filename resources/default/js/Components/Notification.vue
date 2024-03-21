<template>
  <div
    :class="{
      'bg-light-blue-50 rounded dark:bg-cool-gray-600 dark:bg-opacity-25':
        notification.read_at == null,
    }"
    @click="markAsRead"
  >
    <inertia-link
      v-if="
        notification.type === 'App\\Notifications\\PostCommentedByUser'
      "
      as="div"
      :href="route('post.show', notification.data.post_id)"
      class="flex cursor-pointer"
    >
      <img
        :src="notification.data.causer.profile_photo_url"
        alt="Profile Picture"
        class="w-10 h-10 rounded-full m-1"
      >
      <div class="m-1">
        <p>
          <b>{{ notification.data.causer.name }}</b>(@{{ notification.data.causer.username }}) commented on
          your post.
        </p>
        <p class="text-xs">
          {{ formatTimeAgoToNow(notification.created_at) }}
        </p>
      </div>
    </inertia-link>

    <inertia-link
      v-if="notification.type === 'App\\Notifications\\PostLikedByUser'"
      as="div"
      :href="route('post.show', notification.data.post_id)"
      class="flex cursor-pointer"
    >
      <img
        :src="notification.data.causer.profile_photo_url"
        alt="Profile Picture"
        class="w-10 h-10 rounded-full m-1"
      >
      <div class="m-1">
        <p>
          <b>{{ notification.data.causer.name }}</b>(@{{ notification.data.causer.username }}) liked your post.
        </p>
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
        <p>
          <b>{{ notification.data.causer.name }}</b>(@{{ notification.data.causer.username }}) muted you.
        </p>
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
        <p>
          <b>{{ notification.data.causer.name }}</b>(@{{ notification.data.causer.username }}) banned you.
        </p>
        <p class="text-xs">
          {{ formatTimeAgoToNow(notification.created_at) }}
        </p>
      </div>
    </inertia-link>

    <inertia-link
      v-if="
        notification.type ===
          'App\\Notifications\\CustomFormSubmissionCreatedNotification'
      "
      as="div"
      :href="
        route('admin.custom-form-submission.show', notification.data.id)
      "
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
            <b>{{ __("An anonymous user") }}</b>
          </span>
          {{ __("submitted a custom form.") }}
        </p>
        <p class="text-xs">
          {{ formatTimeAgoToNow(notification.created_at) }}
        </p>
      </div>
    </inertia-link>

    <inertia-link
      v-if="
        notification.type ===
          'App\\Notifications\\NewsCommentedByUserNotification'
      "
      as="div"
      :href="route('news.show', notification.data.news.slug)"
      class="flex cursor-pointer"
    >
      <img
        :src="notification.data.causer.profile_photo_url"
        alt="Profile Picture"
        class="w-10 h-10 rounded-full m-1"
      >
      <div class="m-1">
        <p>
          <b>{{ notification.data.causer.name }}</b>(@{{ notification.data.causer.username }}) commented on a
          news.
        </p>
        <p class="text-xs">
          {{ formatTimeAgoToNow(notification.created_at) }}
        </p>
      </div>
    </inertia-link>

    <inertia-link
      v-if="
        notification.type ===
          'App\\Notifications\\RecruitmentSubmissionCreatedNotification'
      "
      as="div"
      :href="
        route('admin.recruitment-submission.show', notification.data.id)
      "
      class="flex cursor-pointer"
    >
      <img
        :src="notification.data.causer.profile_photo_url"
        alt="Profile Picture"
        class="w-10 h-10 rounded-full m-1"
      >
      <div class="m-1">
        <p>
          <span>
            <b>{{ notification.data.causer.name }}</b>(@{{ notification.data.causer.username }})
          </span>
          {{ __("applied for a recruitment.") }}
        </p>
        <p class="text-xs">
          {{ formatTimeAgoToNow(notification.created_at) }}
        </p>
      </div>
    </inertia-link>

    <inertia-link
      v-if="
        notification.type ===
          'App\\Notifications\\RecruitmentSubmissionStatusChangedNotification'
      "
      as="div"
      :href="
        notification.data.for_staff
          ? route(
            'admin.recruitment-submission.show',
            notification.data.id
          )
          : route('recruitment-submission.show', {
            recruitment: notification.data.recruitment.slug,
            submission: notification.data.id,
          })
      "
      class="flex cursor-pointer"
    >
      <img
        :src="notification.data.causer.profile_photo_url"
        alt="Profile Picture"
        class="w-10 h-10 rounded-full m-1"
      >
      <div class="m-1">
        <p>
          <span>
            <b>{{ notification.data.causer.name }}</b>(@{{ notification.data.causer.username }})
          </span>
          <span v-if="notification.data.status == 'withdrawn'">
            {{ __(" has withdrawn his application.") }}
          </span>
          <span v-else-if="notification.data.status == 'rejected'">
            {{ __(" rejected your application.") }}
          </span>
          <span v-else-if="notification.data.status == 'approved'">
            {{ __(" approved your application.") }}
          </span>
          <span v-else-if="notification.data.status == 'onhold'">
            {{ __(" has put your application on-hold.") }}
          </span>
          <span v-else-if="notification.data.status == 'inprogress'">
            {{ __(" has started processing your application.") }}
          </span>
          <span v-else>
            {{ __(" has changed status of your application") }}
          </span>
        </p>
        <p class="text-xs">
          {{ formatTimeAgoToNow(notification.created_at) }}
        </p>
      </div>
    </inertia-link>

    <inertia-link
      v-if="
        notification.type ===
          'App\\Notifications\\RecruitmentSubmissionCommentCreatedNotification'
      "
      as="div"
      :href="
        notification.data.for_staff
          ? route(
            'admin.recruitment-submission.show',
            notification.data.submission_id
          )
          : route('recruitment-submission.show', {
            recruitment: notification.data.recruitment.slug,
            submission: notification.data.submission_id,
          })
      "
      class="flex cursor-pointer"
    >
      <img
        :src="notification.data.causer.profile_photo_url"
        alt="Profile Picture"
        class="w-10 h-10 rounded-full m-1"
      >
      <div class="m-1">
        <p>
          <span v-if="notification.data.for_staff">
            {{ __("New message received on a recruitment application.") }}
          </span>
          <span v-else>
            {{ __("Your application has a new message from @:username", {
              username: notification.data.causer.username
            }) }}
          </span>
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
        const { formatTimeAgoToNow, formatToDayDateString } = useHelpers();
        return { formatTimeAgoToNow, formatToDayDateString };
    },

    methods: {
        markAsRead() {
            axios.post(route('notification.mark-as-read'), {
                notifications: [this.notification.id],
            });
        },
    },
};
</script>
