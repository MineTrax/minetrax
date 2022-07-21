<template>
  <jet-form-section @submitted="updatePassword">
    <template #title>
      {{ __("Update Password") }}
    </template>

    <template #description>
      {{ __("Ensure your account is using a long, random password to stay secure.") }}
    </template>

    <template #form>
      <div class="col-span-6 sm:col-span-4">
        <x-input
          id="current_password"
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
        <password
          v-model="form.password"
          :strength-meter-only="true"
          strength-meter-class="Password__strength-meter dark:bg-cool-gray-700"
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
import Password from 'vue-password-strength-meter';
import XInput from '@/Components/Form/XInput.vue';

export default {
    components: {
        XInput,
        JetActionMessage,
        JetButton,
        JetFormSection,
        Password
    },

    data() {
        return {
            form: this.$inertia.form({
                current_password: '',
                password: '',
                password_confirmation: '',
            }),
        };
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
