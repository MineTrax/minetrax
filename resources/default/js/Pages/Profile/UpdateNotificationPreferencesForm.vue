<template>
  <jet-form-section @submitted="updateNotificationPreference">
    <template #title>
      {{ __("Notification Preferences") }}
    </template>

    <template #description>
      {{ __("Check what you want to receive notifications for.") }}
    </template>

    <template #form>
      <div class="col-span-6 sm:col-span-6">
        <table class="table-auto w-full">
          <thead>
            <tr>
              <th
                scope="col"
                class="text-lg text-left font-semibold text-gray-600 dark:text-gray-300"
              >
                {{ __("Notify me when") }}
              </th>
              <th
                class="font-semibold text-gray-600 dark:text-gray-300 text-sm"
              >
                {{ __("Email") }}
              </th>
              <th
                class="font-semibold text-gray-600 dark:text-gray-300 text-sm"
              >
                {{ __("Discord") }}
              </th>
            </tr>
          </thead>
          <tbody
            class="text-gray-700 dark:text-gray-400 text-sm leading-6"
          >
            <tr class="mt-5">
              <td>{{ __("Someone commented on my Post") }}</td>
              <td class="text-center">
                <input
                  v-model="form.comment_on_post"
                  type="checkbox"
                  value="mail"
                  class="rounded border-gray-300 dark:bg-cool-gray-900 dark:border-gray-900 text-light-blue-500 shadow-sm focus:border-light-blue-300 focus:ring focus:ring-light-blue-200 focus:ring-opacity-50 disabled:opacity-25 transition ease-in-out duration-150"
                  name="comment_on_post"
                >
              </td>
              <td class="text-center">
                <input
                  v-model="form.comment_on_post"
                  type="checkbox"
                  class="rounded border-gray-300 dark:bg-cool-gray-900 dark:border-gray-900 text-light-blue-500 shadow-sm focus:border-light-blue-300 focus:ring focus:ring-light-blue-200 focus:ring-opacity-50 disabled:opacity-25 transition ease-in-out duration-150"
                  name="comment_on_post"
                  value="discord"
                >
              </td>
            </tr>
            <tr>
              <td>{{ __("Someone liked my Post") }}</td>
              <td class="text-center">
                <input
                  v-model="form.like_on_post"
                  type="checkbox"
                  class="rounded border-gray-300 dark:bg-cool-gray-900 dark:border-gray-900 text-light-blue-500 shadow-sm focus:border-light-blue-300 focus:ring focus:ring-light-blue-200 focus:ring-opacity-50 disabled:opacity-25 transition ease-in-out duration-150"
                  name="like_on_post"
                  value="mail"
                >
              </td>
              <td class="text-center">
                <input
                  v-model="form.like_on_post"
                  type="checkbox"
                  class="rounded border-gray-300 dark:bg-cool-gray-900 dark:border-gray-900 text-light-blue-500 shadow-sm focus:border-light-blue-300 focus:ring focus:ring-light-blue-200 focus:ring-opacity-50 disabled:opacity-25 transition ease-in-out duration-150"
                  name="like_on_post"
                  value="discord"
                >
              </td>
            </tr>
            <tr>
              <td>{{ __("I am muted by Staff") }}</td>
              <td class="text-center">
                <input
                  v-model="form.you_are_muted"
                  type="checkbox"
                  class="rounded border-gray-300 dark:bg-cool-gray-900 dark:border-gray-900 text-light-blue-500 shadow-sm focus:border-light-blue-300 focus:ring focus:ring-light-blue-200 focus:ring-opacity-50 disabled:opacity-25 transition ease-in-out duration-150"
                  name="you_are_muted"
                  value="mail"
                >
              </td>
              <td class="text-center">
                <input
                  v-model="form.you_are_muted"
                  type="checkbox"
                  class="rounded border-gray-300 dark:bg-cool-gray-900 dark:border-gray-900 text-light-blue-500 shadow-sm focus:border-light-blue-300 focus:ring focus:ring-light-blue-200 focus:ring-opacity-50 disabled:opacity-25 transition ease-in-out duration-150"
                  name="you_are_muted"
                  value="discord"
                >
              </td>
            </tr>
            <tr>
              <td>{{ __("I am banned by Staff") }}</td>
              <td class="text-center">
                <input
                  v-model="form.you_are_banned"
                  type="checkbox"
                  class="rounded border-gray-300 dark:bg-cool-gray-900 dark:border-gray-900 text-light-blue-500 shadow-sm focus:border-light-blue-300 focus:ring focus:ring-light-blue-200 focus:ring-opacity-50 disabled:opacity-25 transition ease-in-out duration-150"
                  name="you_are_banned"
                  value="mail"
                >
              </td>
              <td class="text-center">
                <input
                  v-model="form.you_are_banned"
                  type="checkbox"
                  class="rounded border-gray-300 dark:bg-cool-gray-900 dark:border-gray-900 text-light-blue-500 shadow-sm focus:border-light-blue-300 focus:ring focus:ring-light-blue-200 focus:ring-opacity-50 disabled:opacity-25 transition ease-in-out duration-150"
                  name="you_are_banned"
                  value="discord"
                >
              </td>
            </tr>
            <tr>
              <td>
                {{
                  __("Recruitment submission status changed")
                }}
              </td>
              <td class="text-center">
                <input
                  v-model="
                    form.recruitment_submission_status_changed
                  "
                  type="checkbox"
                  class="rounded border-gray-300 dark:bg-cool-gray-900 dark:border-gray-900 text-light-blue-500 shadow-sm focus:border-light-blue-300 focus:ring focus:ring-light-blue-200 focus:ring-opacity-50 disabled:opacity-25 transition ease-in-out duration-150"
                  name="recruitment_submission_status_changed"
                  value="mail"
                >
              </td>
              <td class="text-center">
                <input
                  v-model="
                    form.recruitment_submission_status_changed
                  "
                  type="checkbox"
                  class="rounded border-gray-300 dark:bg-cool-gray-900 dark:border-gray-900 text-light-blue-500 shadow-sm focus:border-light-blue-300 focus:ring focus:ring-light-blue-200 focus:ring-opacity-50 disabled:opacity-25 transition ease-in-out duration-150"
                  name="recruitment_submission_status_changed"
                  value="discord"
                >
              </td>
            </tr>
            <tr>
              <td>
                {{
                  __("New message in recruitment submission")
                }}
              </td>
              <td class="text-center">
                <input
                  v-model="
                    form.recruitment_submission_comment_created
                  "
                  type="checkbox"
                  class="rounded border-gray-300 dark:bg-cool-gray-900 dark:border-gray-900 text-light-blue-500 shadow-sm focus:border-light-blue-300 focus:ring focus:ring-light-blue-200 focus:ring-opacity-50 disabled:opacity-25 transition ease-in-out duration-150"
                  name="recruitment_submission_comment_created"
                  value="mail"
                >
              </td>
              <td class="text-center">
                <input
                  v-model="
                    form.recruitment_submission_comment_created
                  "
                  type="checkbox"
                  class="rounded border-gray-300 dark:bg-cool-gray-900 dark:border-gray-900 text-light-blue-500 shadow-sm focus:border-light-blue-300 focus:ring focus:ring-light-blue-200 focus:ring-opacity-50 disabled:opacity-25 transition ease-in-out duration-150"
                  name="recruitment_submission_comment_created"
                  value="discord"
                >
              </td>
            </tr>
            <template v-if="user.is_staff">
              <tr class="flex mt-2">
                <span
                  class="font-bold dark:text-gray-300 text-gray-600"
                >
                  {{ __("For staff members") }}
                </span>
              </tr>
              <tr>
                <td>
                  {{ __("Recruitment submission received") }}
                </td>
                <td class="text-center">
                  <input
                    v-model="
                      form.recruitment_submission_created
                    "
                    type="checkbox"
                    class="rounded border-gray-300 dark:bg-cool-gray-900 dark:border-gray-900 text-light-blue-500 shadow-sm focus:border-light-blue-300 focus:ring focus:ring-light-blue-200 focus:ring-opacity-50 disabled:opacity-25 transition ease-in-out duration-150"
                    name="recruitment_submission_created"
                    value="mail"
                  >
                </td>
                <td class="text-center">
                  <input
                    v-model="
                      form.recruitment_submission_created
                    "
                    type="checkbox"
                    class="rounded border-gray-300 dark:bg-cool-gray-900 dark:border-gray-900 text-light-blue-500 shadow-sm focus:border-light-blue-300 focus:ring focus:ring-light-blue-200 focus:ring-opacity-50 disabled:opacity-25 transition ease-in-out duration-150"
                    name="recruitment_submission_created"
                    value="discord"
                  >
                </td>
              </tr>
              <tr>
                <td>
                  {{ __("Custom form submission received") }}
                </td>
                <td class="text-center">
                  <input
                    v-model="
                      form.custom_form_submission_created
                    "
                    type="checkbox"
                    class="rounded border-gray-300 dark:bg-cool-gray-900 dark:border-gray-900 text-light-blue-500 shadow-sm focus:border-light-blue-300 focus:ring focus:ring-light-blue-200 focus:ring-opacity-50 disabled:opacity-25 transition ease-in-out duration-150"
                    name="custom_form_submission_created"
                    value="mail"
                  >
                </td>
                <td class="text-center">
                  <input
                    v-model="
                      form.custom_form_submission_created
                    "
                    type="checkbox"
                    class="rounded border-gray-300 dark:bg-cool-gray-900 dark:border-gray-900 text-light-blue-500 shadow-sm focus:border-light-blue-300 focus:ring focus:ring-light-blue-200 focus:ring-opacity-50 disabled:opacity-25 transition ease-in-out duration-150"
                    name="custom_form_submission_created"
                    value="discord"
                  >
                </td>
              </tr>
              <tr>
                <td>{{ __("Someone commented on News") }}</td>
                <td class="text-center">
                  <input
                    v-model="
                      form.news_commented_by_user
                    "
                    type="checkbox"
                    class="rounded border-gray-300 dark:bg-cool-gray-900 dark:border-gray-900 text-light-blue-500 shadow-sm focus:border-light-blue-300 focus:ring focus:ring-light-blue-200 focus:ring-opacity-50 disabled:opacity-25 transition ease-in-out duration-150"
                    name="news_commented_by_user"
                    value="mail"
                  >
                </td>
                <td class="text-center">
                  <input
                    v-model="
                      form.news_commented_by_user
                    "
                    type="checkbox"
                    class="rounded border-gray-300 dark:bg-cool-gray-900 dark:border-gray-900 text-light-blue-500 shadow-sm focus:border-light-blue-300 focus:ring focus:ring-light-blue-200 focus:ring-opacity-50 disabled:opacity-25 transition ease-in-out duration-150"
                    name="news_commented_by_user"
                    value="discord"
                  >
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </template>

    <template #actions>
      <jet-action-message
        :on="form.recentlySuccessful"
        class="mr-3"
      >
        {{ __("Saved.") }}
      </jet-action-message>

      <jet-button
        :class="{ 'opacity-25': form.processing }"
        :disabled="form.processing"
        :loading="form.processing"
      >
        {{ __("Save") }}
      </jet-button>
    </template>
  </jet-form-section>
</template>

<script setup>
import JetActionMessage from '@/Jetstream/ActionMessage.vue';
import JetButton from '@/Jetstream/Button.vue';
import JetFormSection from '@/Jetstream/FormSection.vue';
import { useForm } from '@inertiajs/vue3';
import { useTranslations } from '@/Composables/useTranslations';

const { __ } = useTranslations();

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
});

function notificationValueOrDefault(key) {
    // Default
    const defaultPref = ['database', 'mail', 'discord'];
    if (!props.user?.settings?.notifications) {
        return defaultPref;
    }

    if (props.user.settings.notifications[key] === undefined) {
        return defaultPref;
    }

    return props.user.settings?.notifications[key];
}

const form = useForm({
    like_on_post: notificationValueOrDefault('like_on_post'),
    comment_on_post: notificationValueOrDefault('comment_on_post'),
    you_are_muted: notificationValueOrDefault('you_are_muted'),
    you_are_banned: notificationValueOrDefault('you_are_banned'),
    recruitment_submission_comment_created: notificationValueOrDefault(
        'recruitment_submission_comment_created'
    ),
    recruitment_submission_status_changed: notificationValueOrDefault(
        'recruitment_submission_status_changed'
    ),
    // Staff
    custom_form_submission_created: notificationValueOrDefault(
        'custom_form_submission_created'
    ),
    news_commented_by_user: notificationValueOrDefault(
        'news_commented_by_user'
    ),
    recruitment_submission_created: notificationValueOrDefault(
        'recruitment_submission_created'
    ),
});

const updateNotificationPreference = () => {
    form.put(route('auth.put-notification-preferences'), {
        errorBag: 'updateNotificationPreference',
        preserveScroll: true,
    });
};
</script>
