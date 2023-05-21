<template>
  <AlertCard
    text-color="text-orange-800 dark:text-orange-500"
    border-color="border-orange-500"
  >
    {{ __("Your email address is not verified.") }}
    <template
      #body
    >
      <div class="text-gray-700 text-sm dark:text-gray-200">
        <p>
          {{ __("Email verification is required to perform any action on the site. Please check your email inbox or ") }}
        </p>

        <InertiaLink
          :href="route('verification.send')"
          method="post"
          as="button"
          class="underline text-light-blue-500 hover:text-light-blue-700"
          @click.prevent="sendEmailVerification"
        >
          {{ __("Click here to re-send the verification email") }}
        </InertiaLink>

        <div
          v-show="verificationLinkSent"
          class="mt-2 font-medium text-sm text-green-600"
        >
          {{ __("A new verification link has been sent to your email address.") }}
        </div>
      </div>
    </template>
  </AlertCard>
</template>

<script setup>
import { ref } from 'vue';
import AlertCard from '@/Components/AlertCard.vue';

const verificationLinkSent = ref(null);

const sendEmailVerification = () => {
    verificationLinkSent.value = true;
};
</script>

