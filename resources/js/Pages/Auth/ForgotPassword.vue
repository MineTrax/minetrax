<template>
  <app-layout>
    <app-head
      :title="__('Forgot Password')"
    />

    <jet-authentication-card>
      <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        {{ __("Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.") }}
      </div>

      <div
        v-if="status"
        class="mb-4 font-medium text-sm text-green-600 dark:text-green-400"
      >
        {{ status }}
      </div>

      <!--<jet-validation-errors class="mb-4" />-->

      <form @submit.prevent="submit">
        <div>
          <x-input
            id="email"
            v-model="form.email"
            :label="__('Email Address')"
            :error="form.errors.email"
            :autofocus="true"
            :required="true"
            type="email"
            name="email"
          />
        </div>

        <div class="flex items-center justify-end mt-4">
          <loading-button
            :loading="form.processing"
            class="ml-4 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-light-blue-500 hover:bg-light-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 disabled:opacity-50"
          >
            {{ __("Email Password Reset Link") }}
          </loading-button>
        </div>
      </form>
    </jet-authentication-card>
  </app-layout>
</template>

<script>
import JetAuthenticationCard from '@/Jetstream/AuthenticationCard.vue';
import LoadingButton from '@/Components/LoadingButton.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import XInput from '@/Components/Form/XInput.vue';
import { useForm } from '@inertiajs/vue3';

export default {
    components: {
        XInput,
        AppLayout,
        LoadingButton,
        JetAuthenticationCard,
    },

    props: {
        status: String
    },

    data() {
        return {
            form: useForm({
                email: ''
            })
        };
    },

    methods: {
        submit() {
            this.form.post(this.route('password.email'));
        }
    }
};
</script>
