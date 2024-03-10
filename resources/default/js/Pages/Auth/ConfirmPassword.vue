<template>
  <app-layout>
    <app-head
      :title="__('Confirm your Password')"
    />

    <jet-authentication-card>
      <template #logo>
        <Icon
          name="finger-print2"
          class="w-20 h-20 fill-current text-light-blue-500"
        />
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
            class="inline-flex justify-center px-4 py-2 ml-4 text-sm font-medium text-white border border-transparent rounded-md shadow-sm bg-light-blue-500 hover:bg-light-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 disabled:opacity-50"
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
import LoadingButton from '@/Components/LoadingButton.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import XInput from '@/Components/Form/XInput.vue';
import Icon from '@/Components/Icon.vue';
import { useForm } from '@inertiajs/vue3';

export default {
    components: {
        XInput,
        AppLayout,
        LoadingButton,
        JetAuthenticationCard,
        Icon,
    },

    data() {
        return {
            form: useForm({
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
