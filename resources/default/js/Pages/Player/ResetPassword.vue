<script setup>
import Multiselect from 'vue-multiselect';
import XInput from '@/Components/Form/XInput.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import LoadingButton from '@/Components/LoadingButton.vue';
import AlertCard from '@/Components/AlertCard.vue';
import { computed } from 'vue';
import { ArrowPathIcon, CheckCircleIcon } from '@heroicons/vue/24/solid';
import { useHelpers } from '@/Composables/useHelpers';
const { generateRandomString, secondsToHMS } = useHelpers();

const props = defineProps({
    uuid: {
        type: String,
        required: false,
    },
    players: {
        type: Array,
        required: true,
    },
    cooldown: {
        type: Number,
    },
    cannotPlayerPasswordReset: {
        type: Boolean,
    }
});

let selectedPlayer = ref(props.players[0]);
const found = props.players.find((player) => player.uuid === props.uuid);
if (found) {
    selectedPlayer.value = found;
}

const form = useForm({
    player_uuid: null,
    new_password: null,
    new_password_confirmation: null,
    reason: null,
});

const submitForm = () => {
    form.player_uuid = selectedPlayer.value.uuid;
    form.post(route('reset-player-password.update'), {
        onSuccess: () => {
            form.reset();
        },
    });
};

const formDisabled = computed(() => {
    return form.processing || !selectedPlayer.value || props.cannotPlayerPasswordReset || props.cooldown > 0;
});

const success = computed(() => {
    return usePage().props?.toast?.type === 'success';
});

</script>

<template>
  <AppLayout>
    <AppHead :title="__('Reset Player Password')" />
    <div class="max-w-6xl px-2 py-3 mx-auto space-y-4 md:py-12 md:px-10">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <h2
          v-if="selectedPlayer"
          class="text-lg mb-2 md:mb-0 md:text-2xl font-bold text-foreground dark:text-foreground"
        >
          {{
            __("Reset password for :username", {
              username: selectedPlayer.username,
            })
          }}
        </h2>
        <h2
          v-else
          class="text-lg italic mb-2 md:mb-0 md:text-2xl font-bold text-foreground dark:text-foreground"
        >
          {{ __("No Linked Players") }}
        </h2>

        <Multiselect
          id="username"
          v-model="selectedPlayer"
          class="w-full md:w-1/3 bg-surface-300 border-foreground rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm dark:bg-surface-900"
          :options="players"
          :multiple="false"
          :placeholder="__('Search') + '...'"
          label="username"
          :allow-empty="false"
          :deselect-label="__('Can\'t remove')"
          track-by="id"
        />
      </div>

      <p
        v-if="selectedPlayer"
        class="text-xs text-foreground dark:text-foreground"
      >
        {{ __("Player Uuid") }}: {{ selectedPlayer.uuid }}
      </p>

      <AlertCard
        v-if="success"
        text-color="text-success-800 dark:text-success-500"
        border-color="border-success-500"
      >
        <template #icon>
          <CheckCircleIcon class="fill-current h-6 w-6 text-success-500 mr-4" />
        </template>
        <h2 class="text-xl">
          {{ __("Password changed successfully!") }}
        </h2>
        <template #body>
          <p class="text-sm">
            {{ ("Your player password has been changed successfully. You can now join the server with new password.") }}
          </p>
        </template>
      </AlertCard>

      <AlertCard
        v-if="!selectedPlayer"
        text-color="text-error-800 dark:text-error-500"
        border-color="border-error-500"
      >
        {{ __("No linked players found. Please link a player to your account.") }}
      </AlertCard>

      <AlertCard
        v-if="cannotPlayerPasswordReset"
        text-color="text-error-800 dark:text-error-500"
        border-color="border-error-500"
      >
        {{ __("Your are not allowed to reset your player passwords.") }}

        <template #body>
          <p class="text-sm">
            {{ __("Site administrator has explicitly disabled this feature for your role. Contact for more information.") }}
          </p>
        </template>
      </AlertCard>

      <div
        as="div"
        class="w-full p-6 text-foreground rounded-lg text-medium dark:text-foreground bg-white shadow dark:bg-surface-800"
      >
        <form
          class="w-full mt-4"
          @submit.prevent="submitForm"
        >
          <div class="grid grid-cols-6 gap-6">
            <div class="col-span-6 sm:col-span-6 flex space-x-2">
              <x-input
                id="new_password"
                v-model="form.new_password"
                :disabled="formDisabled"
                :label="__('New Password')"
                :error="form.errors.new_password"
                type="text"
                name="new_password"
                help-error-flex="flex-row"
              />

              <div>
                <button
                  :disabled="formDisabled"
                  type="button"
                  class="inline-flex items-center px-4 py-4 bg-white border border-foreground rounded-md font-semibold text-xs text-foreground tracking-widest whitespace-nowrap shadow-sm hover:text-foreground focus:outline-none active:text-foreground active:mt-0.5 active:bg-surface-50 transition ease-in-out duration-150 dark:bg-surface-700 dark:text-foreground dark:border-foreground dark:hover:text-white dark:hover:bg-surface-600 disabled:active:mt-0 disabled:cursor-not-allowed disabled:opacity-50"
                  @click="form.new_password = generateRandomString(16)"
                >
                  <ArrowPathIcon class="inline h-5 w-5 mr-1" />
                  <span class="hidden md:block">
                    {{ __("Generate Secure Password") }}
                  </span>
                </button>
              </div>
            </div>

            <div class="col-span-6 sm:col-span-6">
              <x-input
                id="reason"
                v-model="form.reason"
                :disabled="formDisabled"
                :label="__('Reason')"
                :error="form.errors.reason"
                type="text"
                name="reason"
                help-error-flex="flex-row"
                :help="__('Small reason why are you changing the password?')"
              />
            </div>
          </div>

          <div class="mt-6">
            <LoadingButton
              :loading="form.processing"
              class="inline-flex justify-center py-3 px-6 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50"
              :disabled="formDisabled"
              type="submit"
            >
              {{ __("Reset Player Password") }}
            </LoadingButton>
          </div>

          <span
            v-if="props.cooldown > 0"
            class="text-sm text-orange-500 dark:text-orange-500 flex mt-2"
          >
            {{ __("You are on a cooldown because you have recently changed your password. Refresh the page and try again after :cooldown.", { cooldown: secondsToHMS(props.cooldown, true) }) }}
          </span>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
