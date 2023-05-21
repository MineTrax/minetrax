<script setup>
import {ref, computed, watch} from 'vue';
import { router } from '@inertiajs/vue3';
import {useForm, usePage} from '@inertiajs/vue3';
import JetActionSection from '@/Jetstream/ActionSection.vue';
import JetButton from '@/Jetstream/Button.vue';
import JetConfirmsPassword from '@/Jetstream/ConfirmsPassword.vue';
import JetDangerButton from '@/Jetstream/DangerButton.vue';
import JetSecondaryButton from '@/Jetstream/SecondaryButton.vue';
import XInput from '@/Components/Form/XInput.vue';

const props = defineProps({
    requiresConfirmation: Boolean,
});

const enabling = ref(false);
const confirming = ref(false);
const disabling = ref(false);
const qrCode = ref(null);
const setupKey = ref(null);
const recoveryCodes = ref([]);

const confirmationForm = useForm({
    code: '',
});

const twoFactorEnabled = computed(
    () => !enabling.value && usePage().props.auth?.user?.two_factor_enabled,
);

watch(twoFactorEnabled, () => {
    if (!twoFactorEnabled.value) {
        confirmationForm.reset();
        confirmationForm.clearErrors();
    }
});

const enableTwoFactorAuthentication = () => {
    enabling.value = true;

    router.post('/user/two-factor-authentication', {}, {
        preserveScroll: true,
        onSuccess: () => Promise.all([
            showQrCode(),
            showSetupKey(),
            showRecoveryCodes(),
        ]),
        onFinish: () => {
            enabling.value = false;
            confirming.value = props.requiresConfirmation;
        },
    });
};

const showQrCode = () => {
    return axios.get('/user/two-factor-qr-code').then(response => {
        qrCode.value = response.data.svg;
    });
};

const showSetupKey = () => {
    return axios.get('/user/two-factor-secret-key').then(response => {
        setupKey.value = response.data.secretKey;
    });
};

const showRecoveryCodes = () => {
    return axios.get('/user/two-factor-recovery-codes').then(response => {
        recoveryCodes.value = response.data;
    });
};

const confirmTwoFactorAuthentication = () => {
    confirmationForm.post('/user/confirmed-two-factor-authentication', {
        errorBag: 'confirmTwoFactorAuthentication',
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            confirming.value = false;
            qrCode.value = null;
            setupKey.value = null;
        },
    });
};

const regenerateRecoveryCodes = () => {
    axios
        .post('/user/two-factor-recovery-codes')
        .then(() => showRecoveryCodes());
};

const disableTwoFactorAuthentication = () => {
    disabling.value = true;

    router.delete('/user/two-factor-authentication', {
        preserveScroll: true,
        onSuccess: () => {
            disabling.value = false;
            confirming.value = false;
        },
    });
};
</script>

<template>
  <JetActionSection>
    <template #title>
      {{ __("Two Factor Authentication") }}
    </template>

    <template #description>
      {{ __("Add additional security to your account using two factor authentication.") }}
    </template>

    <template #content>
      <h3
        v-if="twoFactorEnabled && ! confirming"
        class="text-lg font-medium text-gray-900 dark:text-gray-300"
      >
        {{ __("You have enabled two factor authentication.") }}
      </h3>

      <h3
        v-else-if="twoFactorEnabled && confirming"
        class="text-lg font-medium text-gray-900 dark:text-gray-400"
      >
        {{ __("Finish enabling two factor authentication.") }}
      </h3>

      <h3
        v-else
        class="text-lg font-medium text-gray-900 dark:text-gray-300"
      >
        {{ __("You have not enabled two factor authentication.") }}
      </h3>

      <div class="mt-3 max-w-xl text-sm text-gray-600 dark:text-gray-400">
        <p>
          {{ __("When two factor authentication is enabled, you will be prompted for a secure, random token during authentication. You may retrieve this token from your phone's Google Authenticator application.") }}
        </p>
      </div>

      <div v-if="twoFactorEnabled">
        <div v-if="qrCode">
          <div class="mt-4 max-w-xl text-sm text-gray-600 dark:text-gray-400">
            <p
              v-if="confirming"
              class="font-semibold"
            >
              {{ __("To finish enabling two factor authentication, scan the following QR code using your phone's authenticator application or enter the setup key and provide the generated OTP code.") }}
            </p>

            <p v-else>
              {{ __("Two factor authentication is now enabled. Scan the following QR code using your phone's authenticator application or enter the setup key.") }}
            </p>
          </div>

          <div
            class="mt-4 dark:bg-white dark:p-4 dark:rounded"
            v-html="qrCode"
          />

          <div
            v-if="setupKey"
            class="mt-4 max-w-xl text-sm text-gray-600 dark:text-gray-400"
          >
            <p class="font-semibold">
              {{ __("Setup Key") }}: <span v-html="setupKey" />
            </p>
          </div>

          <div
            v-if="confirming"
            class="mt-4"
          >
            <XInput
              id="code"
              v-model="confirmationForm.code"
              inputmode="numeric"
              :label="__('Code')"
              :error="confirmationForm.errors.code"
              autocomplete="one-time-code"
              autofocus
              class="block mt-1 w-1/2"
              type="text"
              name="code"
              @keyup.enter="confirmTwoFactorAuthentication"
            />
          </div>
        </div>

        <div v-if="recoveryCodes.length > 0 && ! confirming">
          <div class="mt-4 max-w-xl text-sm text-gray-600 dark:text-gray-400">
            <p class="font-semibold">
              {{ __("Store these recovery codes in a secure password manager. They can be used to recover access to your account if your two factor authentication device is lost.") }}
            </p>
          </div>

          <div class="grid gap-1 max-w-xl mt-4 px-4 py-4 font-mono text-sm bg-gray-100 dark:bg-gray-900 dark:text-gray-300 rounded-lg">
            <div
              v-for="code in recoveryCodes"
              :key="code"
            >
              {{ code }}
            </div>
          </div>
        </div>
      </div>

      <div class="mt-5">
        <div v-if="! twoFactorEnabled">
          <JetConfirmsPassword @confirmed="enableTwoFactorAuthentication">
            <JetButton
              type="button"
              :class="{ 'opacity-25': enabling }"
              :disabled="enabling"
            >
              {{ __("Enable") }}
            </JetButton>
          </JetConfirmsPassword>
        </div>

        <div v-else>
          <JetConfirmsPassword @confirmed="confirmTwoFactorAuthentication">
            <JetButton
              v-if="confirming"
              type="button"
              class="mr-3"
              :class="{ 'opacity-25': enabling }"
              :disabled="enabling"
            >
              {{ __("Confirm") }}
            </JetButton>
          </JetConfirmsPassword>

          <JetConfirmsPassword @confirmed="regenerateRecoveryCodes">
            <JetSecondaryButton
              v-if="recoveryCodes.length > 0 && ! confirming"
              class="mr-3"
            >
              {{ __("Regenerate Recovery Codes") }}
            </JetSecondaryButton>
          </JetConfirmsPassword>

          <JetConfirmsPassword @confirmed="showRecoveryCodes">
            <JetSecondaryButton
              v-if="recoveryCodes.length === 0 && ! confirming"
              class="mr-3"
            >
              {{ __("Show Recovery Codes") }}
            </JetSecondaryButton>
          </JetConfirmsPassword>

          <JetConfirmsPassword @confirmed="disableTwoFactorAuthentication">
            <JetSecondaryButton
              v-if="confirming"
              :class="{ 'opacity-25': disabling }"
              :disabled="disabling"
            >
              {{ __("Cancel") }}
            </JetSecondaryButton>
          </JetConfirmsPassword>

          <JetConfirmsPassword @confirmed="disableTwoFactorAuthentication">
            <JetDangerButton
              v-if="! confirming"
              :class="{ 'opacity-25': disabling }"
              :disabled="disabling"
            >
              {{ __("Disable") }}
            </JetDangerButton>
          </JetConfirmsPassword>
        </div>
      </div>
    </template>
  </JetActionSection>
</template>
