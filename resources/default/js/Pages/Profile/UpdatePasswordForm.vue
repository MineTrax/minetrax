<template>
  <jet-form-section @submitted="updatePassword">
    <template #title>
      {{ __("Update Password") }}
    </template>

    <template #description>
      {{ __("Ensure your account is using a long, random password to stay secure.") }}
    </template>

    <template #form>
      <div
        v-if="showCurrentPasswordConfirm"
        class="col-span-6 sm:col-span-4"
      >
        <x-input
          id="current_password"
          ref="current_password"
          v-model="form.current_password"
          :label="__('Current Password')"
          :error="form.errors.current_password"
          :required="true"
          autocomplete="current-password"
          type="password"
          name="current_password"
        />
      </div>

      <div class="col-span-6 sm:col-span-4">
        <x-input
          id="password"
          ref="password"
          v-model="form.password"
          :label="__('New Password')"
          :error="form.errors.password"
          :required="true"
          autocomplete="new-password"
          type="password"
          name="password"
        />
      </div>

      <div class="col-span-6 sm:col-span-4">
        <x-input
          id="password_confirmation"
          v-model="form.password_confirmation"
          :label="__('Confirm Password')"
          :error="form.errors.password_confirmation"
          :required="true"
          autocomplete="new-password"
          type="password"
          name="password_confirmation"
        />
      </div>

      <div class="col-span-6 sm:col-span-4">
        <password-strength-meter
          :value="form.password"
        />
      </div>
    </template>

    <template #actions>
      <jet-action-message
        :on="form.recentlySuccessful"
        class="mr-3"
      >
        {{ __("Saved.") }}
      </jet-action-message>

      <jet-button
        :class="{ 'opacity-25': form.processing }"
        :disabled="form.processing"
        :loading="form.processing"
      >
        {{ __("Save") }}
      </jet-button>
    </template>
  </jet-form-section>
</template>

<script>
import JetActionMessage from '@/Jetstream/ActionMessage.vue';
import JetButton from '@/Jetstream/Button.vue';
import JetFormSection from '@/Jetstream/FormSection.vue';
import XInput from '@/Components/Form/XInput.vue';
import PasswordStrengthMeter from '@/Components/PasswordStrengthMeter.vue';
import { useForm } from '@inertiajs/vue3';

export default {
    components: {
        PasswordStrengthMeter,
        XInput,
        JetActionMessage,
        JetButton,
        JetFormSection,
    },

    data() {
        return {
            form: useForm({
                current_password: '',
                password: '',
                password_confirmation: '',
            }),
        };
    },

    computed: {
        showCurrentPasswordConfirm() {
            return this.$page.props.authHasPassword ?? true;
        },
    },

    methods: {
        updatePassword() {
            this.form.put(route('user-password.update'), {
                errorBag: 'updatePassword',
                preserveScroll: true,
                onSuccess: () => this.form.reset(),
                onError: () => {
                    if (this.form.errors.password) {
                        this.form.reset('password', 'password_confirmation');
                        this.$refs.password.focus();
                    }

                    if (this.form.errors.current_password) {
                        this.form.reset('current_password');
                        this.$refs.current_password.focus();
                    }
                }
            });
        },
    },
};
</script>
