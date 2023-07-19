<template>
  <app-layout>
    <app-head
      :title="__('Verify your Email')"
    />
    <jet-authentication-card>
      <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        {{ __("Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.") }}
      </div>

      <div
        v-if="verificationLinkSent"
        class="mb-4 font-medium text-sm text-green-600"
      >
        {{ __("A new verification link has been sent to the email address you provided during registration.") }}
      </div>

      <form @submit.prevent="submit">
        <div class="mt-4 flex items-center justify-between">
          <loading-button
            :loading="form.processing"
            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-light-blue-500 hover:bg-light-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 disabled:opacity-50"
          >
            {{ __("Resend Verification Email") }}
          </loading-button>

          <inertia-link
            :href="route('logout')"
            method="post"
            as="button"
            class="underline text-sm text-gray-600 hover:text-gray-900"
          >
            {{ __("Logout") }}
          </inertia-link>
        </div>
      </form>
    </jet-authentication-card>
  </app-layout>
</template>

<script>
import JetAuthenticationCard from '@/Jetstream/AuthenticationCard.vue';
import LoadingButton from '@/Components/LoadingButton.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';

export default {
    components: {
        AppLayout,
        LoadingButton,
        JetAuthenticationCard,
    },

    props: {
        status: String
    },

    data() {
        return {
            form: useForm({})
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
