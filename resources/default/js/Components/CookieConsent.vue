<template>
  <div
    v-if="show"
    class="z-50 fixed p-2 bottom-0 bg-black w-full text-gray-300 text-center bg-opacity-90 dark:bg-opacity-60"
  >
    <div>
      <span>
        {{ __("We use cookies to enhance your browsing experience. By continuing, you consent to our cookie policy.") }}
      </span>
      <button
        class="text-light-blue-400 cursor-pointer ml-0.5 hover:text-light-blue-500"
        @click.prevent="close"
      >
        {{ __("I Understand") }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';

let show = ref(usePage().props?.showCookieConsent);

const close = () => {
    const oneYearFromNow = new Date(new Date().setFullYear(new Date().getFullYear() + 1)).toUTCString();
    document.cookie = `laravel_cookie_consent=1; expires=${oneYearFromNow}; path=/; samesite=lax`;
    show.value = false;
};
</script>
