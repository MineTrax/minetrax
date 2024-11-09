<script setup>
import Multiselect from 'vue-multiselect';
import XInput from '@/Components/Form/XInput.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import LoadingButton from '@/Components/LoadingButton.vue';
import AlertCard from '@/Components/AlertCard.vue';
import { computed } from 'vue';

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
    return form.processing || !selectedPlayer.value;
});

</script>

<template>
  <AppLayout>
    <AppHead :title="__('Reset Player Password')" />
    <div class="max-w-6xl px-2 py-3 mx-auto space-y-4 md:py-12 md:px-10">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <h2
          v-if="selectedPlayer"
          class="text-lg mb-2 md:mb-0 md:text-2xl font-bold text-gray-700 dark:text-gray-200"
        >
          {{
            __("Reset password for :username", {
              username: selectedPlayer.username,
            })
          }}
        </h2>
        <h2
          v-else
          class="text-lg italic mb-2 md:mb-0 md:text-2xl font-bold text-gray-500 dark:text-gray-500"
        >
          {{ __("No Linked Players") }}
        </h2>

        <Multiselect
          id="username"
          v-model="selectedPlayer"
          class="w-full md:w-1/3 bg-gray-300 border-gray-300 rounded-md shadow-sm focus:ring-light-blue-500 focus:border-light-blue-500 sm:text-sm dark:bg-gray-900"
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
        class="text-xs text-gray-500 dark:text-gray-500"
      >
        {{ __("Player Uuid") }}: {{ selectedPlayer.uuid }}
      </p>

      <AlertCard
        v-if="cooldown"
        text-color="text-yellow-800 dark:text-yellow-500"
        border-color="border-yellow-500"
      >
        {{ __("You are on a cooldown! Please wait for :cooldown seconds before you can try again.", {
          cooldown: cooldown
        }) }}
      </AlertCard>

      <AlertCard
        v-if="!selectedPlayer"
        text-color="text-red-800 dark:text-red-500"
        border-color="border-red-500"
      >
        {{ __("No linked players found. Please link a player to your account.") }}
      </AlertCard>

      <div
        as="div"
        class="w-full p-6 text-gray-500 rounded-lg text-medium dark:text-gray-400 bg-white shadow dark:bg-gray-800"
      >
        <form
          class="w-full mt-4"
          @submit.prevent="submitForm"
        >
          <div class="grid grid-cols-6 gap-6">
            <div class="col-span-6 sm:col-span-3">
              <x-input
                id="new_password"
                v-model="form.new_password"
                :label="__('New Password')"
                :error="form.errors.new_password"
                type="password"
                name="new_password"
                help-error-flex="flex-row"
              />
            </div>

            <div class="col-span-6 sm:col-span-3">
              <x-input
                id="new_password_confirmation"
                v-model="form.new_password_confirmation"
                :label="__('Confirm Password')"
                type="password"
                name="new_password_confirmation"
                help-error-flex="flex-row"
              />
            </div>

            <div class="col-span-6 sm:col-span-6">
              <x-input
                id="reason"
                v-model="form.reason"
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
              class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-light-blue-500 hover:bg-light-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 disabled:opacity-50"
              :disabled="formDisabled"
              type="submit"
            >
              {{ __("Reset Player Password") }}
            </LoadingButton>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
