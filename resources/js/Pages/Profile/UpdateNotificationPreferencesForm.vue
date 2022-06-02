<template>
  <jet-form-section @submitted="updateNotificationPreference">
    <template #title>
      Notification Preferences
    </template>

    <template #description>
      Check what you want to receive notifications for.
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
                Notify me when
              </th>
              <th class="font-semibold text-gray-600 dark:text-gray-300">
                via Email
              </th>
            </tr>
          </thead>
          <tbody class="text-gray-700 dark:text-gray-400">
            <tr class="mt-5">
              <td>Someone commented on my Post</td>
              <td>
                <x-checkbox
                  v-model="form.comment_on_post__email"
                  class="justify-center"
                  name="check1"
                />
              </td>
            </tr>
            <tr>
              <td>Someone liked my Post</td>
              <td>
                <x-checkbox
                  v-model="form.like_on_post__email"
                  class="justify-center"
                  name="check2"
                />
              </td>
            </tr>
            <tr>
              <td>I am muted by Staff</td>
              <td>
                <x-checkbox
                  v-model="form.you_are_muted__email"
                  class="justify-center"
                  name="check3"
                />
              </td>
            </tr>
            <tr>
              <td>I am banned by Staff</td>
              <td class="text-center">
                <x-checkbox
                  v-model="form.you_are_banned__email"
                  class="justify-center"
                  name="check4"
                />
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </template>

    <template #actions>
      <jet-action-message
        :on="form.recentlySuccessful"
        class="mr-3"
      >
        Saved.
      </jet-action-message>

      <jet-button
        :class="{ 'opacity-25': form.processing }"
        :disabled="form.processing"
        :loading="form.processing"
      >
        Save
      </jet-button>
    </template>
  </jet-form-section>
</template>

<script>
import JetActionMessage from '@/Jetstream/ActionMessage';
import JetButton from '@/Jetstream/Button';
import JetFormSection from '@/Jetstream/FormSection';
import JetInput from '@/Jetstream/Input';
import JetInputError from '@/Jetstream/InputError';
import JetLabel from '@/Jetstream/Label';
import Password from 'vue-password-strength-meter';
import XInput from '@/Components/Form/XInput';
import XCheckbox from '@/Components/Form/XCheckbox';

export default {
    components: {
        XCheckbox,
        XInput,
        JetActionMessage,
        JetButton,
        JetFormSection,
        JetInput,
        JetInputError,
        JetLabel,
        Password
    },

    props: ['user'],

    data() {
        return {
            form: this.$inertia.form({
                like_on_post__email: this.user.settings?.notifications?.like_on_post.includes('mail'),
                comment_on_post__email: this.user.settings?.notifications?.comment_on_post.includes('mail'),
                you_are_muted__email: this.user.settings?.notifications?.you_are_muted.includes('mail'),
                you_are_banned__email: this.user.settings?.notifications?.you_are_banned.includes('mail')
            }),
        };
    },

    methods: {
        updateNotificationPreference() {
            this.form.put(route('auth.put-notification-preferences'), {
                errorBag: 'updateNotificationPreference',
                preserveScroll: true,
            });
        },
    },
};
</script>
