<script setup>
import Icon from '@/Components/Icon.vue';
import LoadingButton from '@/Components/LoadingButton.vue';
import LoadingSpinner from '@/Components/LoadingSpinner.vue';
import { useTranslations } from '@/Composables/useTranslations';
import JetActionSection from '@/Jetstream/ActionSection.vue';
import JetConfirmsPassword from '@/Jetstream/ConfirmsPassword.vue';
import { XMarkIcon } from '@heroicons/vue/24/outline';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';

const { __ } = useTranslations();

const loading = ref(true);
const socialAccounts = ref([]);

const getSocialAccountList = () => {
    loading.value = true;
    axios.get(route('auth.social-account.index')).then(response => {
        socialAccounts.value = response.data;
    }).finally(() => {
        loading.value = false;
    });
};
getSocialAccountList();

const unlinkAccount = (id) => {
    router.delete(route('auth.social-account.delete', id), {
        preserveScroll: true,
        onSuccess: () => {
            getSocialAccountList();
        }
    });
};

</script>

<template>
  <JetActionSection>
    <template #title>
      {{ __("Linked Social Accounts") }}
    </template>

    <template #description>
      {{ __("View & Manage your linked social OAuth accounts.") }}
    </template>

    <template #content>
      <h3
        class="text-lg font-medium text-foreground dark:text-foreground"
      >
        {{ __("Social Accounts") }}
      </h3>

      <div
        v-if="loading"
        class="flex justify-center mt-3"
      >
        <LoadingSpinner :loading="loading" />
      </div>

      <div
        v-else
        class="mt-3 max-w-xl text-sm text-foreground dark:text-foreground"
      >
        <ul
          v-if="socialAccounts.length"
          class="space-y-4"
        >
          <li
            v-for="account in socialAccounts"
            :key="account.id"
          >
            <div class="flex space-x-2 items-center">
              <Icon
                class="h-8 w-8 fill-current"
                :name="account.provider"
              />

              <div>
                <p>
                  {{ account.name }}
                  <span v-if="account.nickname">
                    ({{ account.nickname }})
                  </span>
                </p>
                <p class="text-xs">
                  {{ account.email }}
                </p>
              </div>

              <JetConfirmsPassword @confirmed="unlinkAccount(account.id)">
                <LoadingButton
                  v-tippy
                  :title="__('Unlink Account')"
                >
                  <XMarkIcon class="h-5 w-5 text-foreground rounded hover:bg-error-400 hover:text-white" />
                </LoadingButton>
              </JetConfirmsPassword>
            </div>
          </li>
        </ul>
        <span
          v-else
          class="italic"
        >
          {{ __("You don't have any linked social accounts.") }}
        </span>
      </div>
    </template>
  </JetActionSection>
</template>
