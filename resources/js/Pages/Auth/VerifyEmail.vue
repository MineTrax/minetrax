<template>
  <app-layout>
    <app-head title="Verify your Email" />
    <jet-authentication-card>
      <template #logo>
        <jet-authentication-card-logo />
      </template>

      <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.
      </div>

      <div
        v-if="verificationLinkSent"
        class="mb-4 font-medium text-sm text-green-600"
      >
        A new verification link has been sent to the email address you provided during registration.
      </div>

      <form @submit.prevent="submit">
        <div class="mt-4 flex items-center justify-between">
          <loading-button
            :loading="form.processing"
            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-light-blue-500 hover:bg-light-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 disabled:opacity-50"
          >
            Resend Verification Email
          </loading-button>

          <inertia-link
            :href="route('logout')"
            method="post"
            as="button"
            class="underline text-sm text-gray-600 hover:text-gray-900"
          >
            Logout
          </inertia-link>
        </div>
      </form>
    </jet-authentication-card>
  </app-layout>
</template>

<script>
import JetAuthenticationCard from '@/Jetstream/AuthenticationCard';
import JetAuthenticationCardLogo from '@/Jetstream/AuthenticationCardLogo';
import JetButton from '@/Jetstream/Button';
import LoadingButton from '@/Components/LoadingButton';
import AppLayout from '@/Layouts/AppLayout';

export default {
    components: {
        AppLayout,
        LoadingButton,
        JetAuthenticationCard,
        JetAuthenticationCardLogo,
        JetButton,
    },

    props: {
        status: String
    },

    data() {
        return {
            form: this.$inertia.form()
        };
    },

    computed: {
        verificationLinkSent() {
            return this.status === 'verification-link-sent';
        }
    },

    methods: {
        submit() {
            this.form.post(this.route('verification.send'));
        },
    }
};
</script>
