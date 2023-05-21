<template>
  <app-layout>
    <app-head
      :title="__('Your Profile Settings')"
    />

    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __("Profile") }}
      </h2>
    </template>

    <div>
      <div class="max-w-7xl mx-auto py-10 px-2 sm:px-6 lg:px-8">
        <div v-if="$page.props.jetstream.canUpdateProfileInformation">
          <update-profile-information-form :user="$page.props.auth.user" />

          <jet-section-border />
        </div>

        <div>
          <update-notification-preferences-form :user="$page.props.auth.user" />

          <jet-section-border />
        </div>

        <div v-if="$page.props.jetstream.canUpdatePassword">
          <update-password-form class="mt-10 sm:mt-0" />

          <jet-section-border />
        </div>

        <div v-if="$page.props.jetstream.canManageTwoFactorAuthentication">
          <two-factor-authentication-form
            class="mt-10 sm:mt-0"
            :requires-confirmation="true"
          />

          <jet-section-border />
        </div>

        <logout-other-browser-sessions-form
          :sessions="sessions"
          class="mt-10 sm:mt-0"
        />

        <template v-if="$page.props.jetstream.hasAccountDeletionFeatures">
          <jet-section-border />

          <delete-user-form class="mt-10 sm:mt-0" />
        </template>
      </div>
    </div>
  </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import DeleteUserForm from './DeleteUserForm.vue';
import JetSectionBorder from '@/Jetstream/SectionBorder.vue';
import LogoutOtherBrowserSessionsForm from './LogoutOtherBrowserSessionsForm.vue';
import TwoFactorAuthenticationForm from './TwoFactorAuthenticationForm.vue';
import UpdatePasswordForm from './UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './UpdateProfileInformationForm.vue';
import UpdateNotificationPreferencesForm from '@/Pages/Profile/UpdateNotificationPreferencesForm.vue';

export default {

    components: {
        UpdateNotificationPreferencesForm,
        AppLayout,
        DeleteUserForm,
        JetSectionBorder,
        LogoutOtherBrowserSessionsForm,
        TwoFactorAuthenticationForm,
        UpdatePasswordForm,
        UpdateProfileInformationForm,
    },
    props: ['sessions'],
};
</script>
