<template>
  <app-layout>
    <app-head title="Confirm your Password" />

    <jet-authentication-card>
      <template #logo>
        <jet-authentication-card-logo />
      </template>

      <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        This is a secure area of the application. Please confirm your password before continuing.
      </div>

      <form @submit.prevent="submit">
        <div>
          <x-input
            id="password"
            v-model="form.password"
            label="Password"
            :required="true"
            autocomplete="current-password"
            :error="form.errors.password"
            :autofocus="true"
            type="password"
            name="password"
          />
        </div>

        <div class="flex justify-end mt-4">
          <loading-button
            :loading="form.processing"
            class="ml-4 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-light-blue-500 hover:bg-light-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 disabled:opacity-50"
          >
            Confirm
          </loading-button>
        </div>
      </form>
    </jet-authentication-card>
  </app-layout>
</template>

<script>
import JetAuthenticationCard from '@/Jetstream/AuthenticationCard';
import JetAuthenticationCardLogo from '@/Jetstream/AuthenticationCardLogo';
import JetButton from '@/Jetstream/Button';
import JetInput from '@/Jetstream/Input';
import JetLabel from '@/Jetstream/Label';
import JetValidationErrors from '@/Jetstream/ValidationErrors';
import LoadingButton from '@/Components/LoadingButton';
import AppLayout from '@/Layouts/AppLayout';
import XInput from '@/Components/Form/XInput';

export default {
    components: {
        XInput,
        AppLayout,
        LoadingButton,
        JetAuthenticationCard,
        JetAuthenticationCardLogo,
        JetButton,
        JetInput,
        JetLabel,
        JetValidationErrors
    },

    data() {
        return {
            form: this.$inertia.form({
                password: '',
            })
        };
    },

    methods: {
        submit() {
            this.form.post(this.route('password.confirm'), {
                onFinish: () => this.form.reset(),
            });
        }
    }
};
</script>
