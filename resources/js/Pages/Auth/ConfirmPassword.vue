<template>
  <app-layout>
    <app-head
      :title="__('Confirm your Password')"
    />

    <jet-authentication-card>
      <template #logo>
        <jet-authentication-card-logo />
      </template>

      <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        {{ __("This is a secure area of the application. Please confirm your password before continuing.") }}
      </div>

      <form @submit.prevent="submit">
        <div>
          <x-input
            id="password"
            v-model="form.password"
            :label="__('Password')"
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
            {{ __("Confirm") }}
          </loading-button>
        </div>
      </form>
    </jet-authentication-card>
  </app-layout>
</template>

<script>
import JetAuthenticationCard from '@/Jetstream/AuthenticationCard.vue';
import JetAuthenticationCardLogo from '@/Jetstream/AuthenticationCardLogo.vue';
import LoadingButton from '@/Components/LoadingButton.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import XInput from '@/Components/Form/XInput.vue';

export default {
    components: {
        XInput,
        AppLayout,
        LoadingButton,
        JetAuthenticationCard,
        JetAuthenticationCardLogo,
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
