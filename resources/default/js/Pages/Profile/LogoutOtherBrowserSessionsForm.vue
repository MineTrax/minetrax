<template>
  <jet-action-section>
    <template #title>
      {{ __("Browser Sessions") }}
    </template>

    <template #description>
      {{ __("Manage and logout your active sessions on other browsers and devices.") }}
    </template>

    <template #content>
      <div class="max-w-xl text-sm text-gray-600 dark:text-gray-400">
        {{ __("If necessary, you may logout of all of your other browser sessions across all of your devices. Some of your recent sessions are listed below; however, this list may not be exhaustive. If you feel your account has been compromised, you should also update your password.") }}
      </div>

      <!-- Other Browser Sessions -->
      <div
        v-if="sessions.length > 0"
        class="mt-5 space-y-6"
      >
        <div
          v-for="(session, i) in sessions"
          :key="i"
          class="flex items-center"
        >
          <div>
            <svg
              v-if="session.agent.is_desktop"
              fill="none"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              viewBox="0 0 24 24"
              stroke="currentColor"
              class="w-8 h-8 text-gray-500"
            >
              <path d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>

            <svg
              v-else
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 24 24"
              stroke-width="2"
              stroke="currentColor"
              fill="none"
              stroke-linecap="round"
              stroke-linejoin="round"
              class="w-8 h-8 text-gray-500"
            >
              <path
                d="M0 0h24v24H0z"
                stroke="none"
              /><rect
                x="7"
                y="4"
                width="10"
                height="16"
                rx="1"
              /><path d="M11 5h2M12 17v.01" />
            </svg>
          </div>

          <div class="ml-3">
            <div class="text-sm text-gray-600 dark:text-gray-400">
              {{ session.agent.platform }} - {{ session.agent.browser }}
            </div>

            <div>
              <div class="text-xs text-gray-500">
                {{ session.ip_address }},

                <span
                  v-if="session.is_current_device"
                  class="text-green-500 font-semibold"
                >{{ __("This device") }}</span>
                <span v-else>{{ __("Last active") }}&nbsp;{{ session.last_active }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="flex items-center mt-5">
        <jet-button @click="confirmLogout">
          {{ __("Logout Other Browser Sessions") }}
        </jet-button>

        <jet-action-message
          :on="form.recentlySuccessful"
          class="ml-3"
        >
          {{ __("Done") }}.
        </jet-action-message>
      </div>

      <!-- Logout Other Devices Confirmation Modal -->
      <jet-dialog-modal
        :show="confirmingLogout"
        @close="closeModal"
      >
        <template #title>
          {{ __("Logout Other Browser Sessions") }}
        </template>

        <template #content>
          {{ __("Please enter your password to confirm you would like to logout of your other browser sessions across all of your devices.") }}

          <div class="mt-4">
            <jet-input
              ref="password"
              v-model="form.password"
              type="password"
              class="mt-1 block w-3/4"
              :placeholder="__('Password')"
              @keyup.enter.native="logoutOtherBrowserSessions"
            />

            <jet-input-error
              :message="form.errors.password"
              class="mt-2"
            />
          </div>
        </template>

        <template #footer>
          <jet-secondary-button @click="closeModal">
            {{ __("Nevermind") }}
          </jet-secondary-button>

          <jet-button
            class="ml-2"
            :class="{ 'opacity-25': form.processing }"
            :disabled="form.processing"
            @click="logoutOtherBrowserSessions"
          >
            {{ __("Logout Other Browser Sessions") }}
          </jet-button>
        </template>
      </jet-dialog-modal>
    </template>
  </jet-action-section>
</template>

<script>
import JetActionMessage from '@/Jetstream/ActionMessage.vue';
import JetActionSection from '@/Jetstream/ActionSection.vue';
import JetButton from '@/Jetstream/Button.vue';
import JetDialogModal from '@/Jetstream/DialogModal.vue';
import JetInput from '@/Jetstream/Input.vue';
import JetInputError from '@/Jetstream/InputError.vue';
import JetSecondaryButton from '@/Jetstream/SecondaryButton.vue';
import { useForm } from '@inertiajs/vue3';

export default {

    components: {
        JetActionMessage,
        JetActionSection,
        JetButton,
        JetDialogModal,
        JetInput,
        JetInputError,
        JetSecondaryButton,
    },
    props: ['sessions'],

    data() {
        return {
            confirmingLogout: false,

            form: useForm({
                password: '',
            })
        };
    },

    methods: {
        confirmLogout() {
            this.confirmingLogout = true;

            setTimeout(() => this.$refs.password.focus(), 250);
        },

        logoutOtherBrowserSessions() {
            this.form.delete(route('other-browser-sessions.destroy'), {
                preserveScroll: true,
                onSuccess: () => this.closeModal(),
                onError: () => this.$refs.password.focus(),
                onFinish: () => this.form.reset(),
            });
        },

        closeModal() {
            this.confirmingLogout = false;

            this.form.reset();
        },
    },
};
</script>
