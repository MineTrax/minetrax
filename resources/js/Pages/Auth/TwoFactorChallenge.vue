<template>
  <app-layout>
    <app-head title="2FA Challenge confirmation" />
    <jet-authentication-card>
      <template #logo>
        <jet-authentication-card-logo />
      </template>

      <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        <template v-if="! recovery">
          Please confirm access to your account by entering the authentication code provided by your authenticator application.
        </template>

        <template v-else>
          Please confirm access to your account by entering one of your emergency recovery codes.
        </template>
      </div>

      <jet-validation-errors class="mb-4" />

      <form @submit.prevent="submit">
        <div v-if="! recovery">
          <jet-label
            for="code"
            value="Code"
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
            value="Recovery Code"
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
              Use a recovery code
            </template>

            <template v-else>
              Use an authentication code
            </template>
          </button>

          <loading-button
            :loading="form.processing"
            class="ml-4 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-light-blue-500 hover:bg-light-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 disabled:opacity-50"
          >
            Login
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

export default {
    components: {
        AppLayout,
        LoadingButton,
        JetAuthenticationCard,
        JetAuthenticationCardLogo,
        JetButton,
        JetInput,
        JetLabel,
        JetValidationErrors,
    },

    data() {
        return {
            recovery: false,
            form: this.$inertia.form({
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
