<template>
  <app-layout>
    <app-head
      :title="__('Confirm your Password')"
    />

    <jet-authentication-card>
      <template #logo>
        <Icon
          name="finger-print2"
          class="w-20 h-20 fill-current text-primary"
        />
      </template>

      <div class="mb-4 text-sm text-foreground dark:text-foreground">
        {{ __("This is a secure area of the application. Please confirm your password before continuing.") }}
      </div>

      <form @submit.prevent="submit">
        <div>
          <x-input
            id="password"
            v-model="form.password"
            :label="__('Password')"
            :required="false"
            autocomplete="current-password"
            :error="form.errors.password"
            :autofocus="true"
            type="password"
            name="password"
          />

          <span class="text-foreground text-xs italic">
            {{ __("Continue with empty password if you have no password.") }}
          </span>
        </div>

        <div class="flex justify-end mt-4">
          <loading-button
            :loading="form.processing"
            class="ml-4"
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
