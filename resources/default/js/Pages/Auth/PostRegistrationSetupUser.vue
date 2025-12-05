<template>
  <app-layout>
    <app-head
      :title="__('Choose a username')"
    />
    <jet-authentication-card>
      <template #logo>
        <Icon name="person-badge" class="text-primary fill-current w-16 h-16" />
      </template>

      <div class="mb-4 text-sm text-foreground dark:text-foreground">
        {{ __("Choose a cool Username for yourself!") }}
      </div>

      <div
        v-if="status"
        class="mb-4 font-medium text-sm text-success-600 dark:text-success-400"
      >
        {{ status }}
      </div>

      <form @submit.prevent="submit">
        <div>
          <x-input
            id="username"
            v-model="form.username"
            :label="__('Username')"
            :required="true"
            :autofocus="true"
            :error="form.errors.username"
            type="text"
            name="username"
          />
        </div>

        <div class="flex items-center justify-end mt-4">
          <loading-button
            :loading="form.processing"
            class="ml-4"
          >
            {{ __("Continue") }}
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

    props: {
        status: String
    },

    data() {
        return {
            form: useForm({
                username: ''
            })
        };
    },

    methods: {
        submit() {
            this.form.post(this.route('auth.post-reg-setup'));
        }
    }
};
</script>
