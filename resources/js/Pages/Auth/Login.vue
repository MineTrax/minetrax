<template>
  <app-layout>
    <app-head
      :title="__('Login')"
    />

    <jet-authentication-card>
      <div
        v-if="status"
        class="mb-4 font-medium text-sm text-green-600"
      >
        {{ status }}
      </div>

      <form
        class="mt-5"
        @submit.prevent="submit"
      >
        <div>
          <x-input
            id="email"
            v-model="form.email"
            :label="__('Email or Username')"
            :error="form.errors.email"
            :autofocus="true"
            :required="true"
            type="text"
            name="email"
          />
        </div>

        <div class="mt-4">
          <x-input
            id="password"
            v-model="form.password"
            :label="__('Password')"
            :error="form.errors.password"
            :autofocus="true"
            :required="true"
            type="password"
            name="password"
            autocomplete="current-password"
          />
        </div>

        <div class="mt-4 block">
          <x-checkbox
            id="remember"
            v-model="form.remember"
            :label="__('Remember me')"
            name="remember"
          />
        </div>

        <div class="flex items-center justify-end mt-4">
          <inertia-link
            v-if="canResetPassword"
            :href="route('password.request')"
            class="underline text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200"
          >
            {{ __("Forgot your password?") }}
          </inertia-link>


          <loading-button
            :loading="form.processing"
            class="ml-4 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-light-blue-500 hover:bg-light-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 disabled:opacity-50"
          >
            {{ __("Login") }}
          </loading-button>
        </div>

        <social-auth-buttons />
      </form>
    </jet-authentication-card>
  </app-layout>
</template>

<script>
import JetAuthenticationCard from '@/Jetstream/AuthenticationCard.vue';
import LoadingButton from '@/Components/LoadingButton.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import SocialAuthButtons from '@/Components/SocialAuthButtons.vue';
import XInput from '@/Components/Form/XInput.vue';
import XCheckbox from '@/Components/Form/XCheckbox.vue';
import { useForm } from '@inertiajs/vue3';

export default {
    components: {
        XCheckbox,
        XInput,
        SocialAuthButtons,
        LoadingButton,
        JetAuthenticationCard,
        AppLayout
    },

    props: {
        canResetPassword: Boolean,
        status: String
    },

    data() {
        return {
            form: useForm({
                email: '',
                password: '',
                remember: false
            })
        };
    },

    methods: {
        submit() {
            this.form
                .transform(data => ({
                    ...data,
                    remember: this.form.remember ? 'on' : ''
                }))
                .post(this.route('login'), {
                    onFinish: () => this.form.reset('password'),
                });
        }
    }
};
</script>
