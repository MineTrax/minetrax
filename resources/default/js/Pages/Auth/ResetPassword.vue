<template>
  <app-layout>
    <app-head
      :title="__('Reset Password')"
    />
    <jet-authentication-card>
      <form @submit.prevent="submit">
        <div>
          <x-input
            id="email"
            v-model="form.email"
            :label="__('Email')"
            :required="true"
            :autofocus="true"
            :error="form.errors.email"
            type="email"
            name="email"
          />
        </div>

        <div class="mt-4">
          <x-input
            id="password"
            v-model="form.password"
            :label="__('Password')"
            :required="true"
            autocomplete="new-password"
            :error="form.errors.password"
            type="password"
            name="password"
          />
        </div>

        <div class="mt-4">
          <x-input
            id="password_confirmation"
            v-model="form.password_confirmation"
            :label="__('Confirm Password')"
            :required="true"
            autocomplete="new-password"
            :error="form.errors.password_confirmation"
            type="password"
            name="password_confirmation"
          />
        </div>

        <div class="flex items-center justify-end mt-4">
          <loading-button
            :loading="form.processing"
            class="ml-4 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-light-blue-500 hover:bg-light-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 disabled:opacity-50"
          >
            {{ __("Reset Password") }}
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
        email: String,
        token: String,
    },

    data() {
        return {
            form: useForm({
                token: this.token,
                email: this.email,
                password: '',
                password_confirmation: '',
            })
        };
    },

    methods: {
        submit() {
            this.form.post(this.route('password.update'), {
                onFinish: () => this.form.reset('password', 'password_confirmation'),
            });
        }
    }
};
</script>
