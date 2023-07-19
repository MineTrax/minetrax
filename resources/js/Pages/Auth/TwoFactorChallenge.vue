<template>
  <app-layout>
    <app-head
      :title="__('2FA Challenge confirmation')"
    />
    <jet-authentication-card>
      <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        <template v-if="! recovery">
          {{ __("Please confirm access to your account by entering the authentication code provided by your authenticator application.") }}
        </template>

        <template v-else>
          {{ __("Please confirm access to your account by entering one of your emergency recovery codes.") }}
        </template>
      </div>

      <jet-validation-errors class="mb-4" />

      <form @submit.prevent="submit">
        <div v-if="! recovery">
          <jet-label
            for="code"
            :value="__('Code')"
          />
          <jet-input
            id="code"
            ref="code"
            v-model="form.code"
            type="text"
            inputmode="numeric"
            class="mt-1 block w-full"
            autofocus
            autocomplete="one-time-code"
          />
        </div>

        <div v-else>
          <jet-label
            for="recovery_code"
            :value="__('Recovery Code')"
          />
          <jet-input
            id="recovery_code"
            ref="recovery_code"
            v-model="form.recovery_code"
            type="text"
            class="mt-1 block w-full"
            autocomplete="one-time-code"
          />
        </div>

        <div class="flex items-center justify-end mt-4">
          <button
            type="button"
            class="text-sm text-gray-600 hover:text-gray-900 underline cursor-pointer"
            @click.prevent="toggleRecovery"
          >
            <template v-if="! recovery">
              {{ __("Use a recovery code") }}
            </template>

            <template v-else>
              {{ __("Use an authentication code") }}
            </template>
          </button>

          <loading-button
            :loading="form.processing"
            class="ml-4 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-light-blue-500 hover:bg-light-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 disabled:opacity-50"
          >
            {{ __("Login") }}
          </loading-button>
        </div>
      </form>
    </jet-authentication-card>
  </app-layout>
</template>

<script>
import JetAuthenticationCard from '@/Jetstream/AuthenticationCard.vue';
import JetInput from '@/Jetstream/Input.vue';
import JetLabel from '@/Jetstream/Label.vue';
import JetValidationErrors from '@/Jetstream/ValidationErrors.vue';
import LoadingButton from '@/Components/LoadingButton.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';

export default {
    components: {
        AppLayout,
        LoadingButton,
        JetAuthenticationCard,
        JetInput,
        JetLabel,
        JetValidationErrors,
    },

    data() {
        return {
            recovery: false,
            form: useForm({
                code: '',
                recovery_code: '',
            })
        };
    },

    methods: {
        toggleRecovery() {
            this.recovery ^= true;

            this.$nextTick(() => {
                if (this.recovery) {
                    this.$refs.recovery_code.focus();
                    this.form.code = '';
                } else {
                    this.$refs.code.focus();
                    this.form.recovery_code = '';
                }
            });
        },

        submit() {
            this.form.post(this.route('two-factor.login'));
        }
    }
};
</script>
