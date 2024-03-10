<template>
  <JetDropdown>
    <template #trigger>
      <button
        v-tippy
        :title="__('Change Language')"
        class="flex items-center text-sm font-medium text-gray-400 hover:text-gray-300"
      >
        <img
          :src="`/images/flags/flags-iso/shiny/48/${$page.props.localeIsoCode.toUpperCase()}.png`"
          class="w-6 mr-2"
        >
      </button>
    </template>

    <template #content>
      <div class="flex flex-col">
        <button
          v-for="(locale) in availableLocales"
          :key="locale.code"
          class="flex items-center w-full px-4 py-1 text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
          @click="setLocale(locale.code)"
        >
          <img
            :src="`/images/flags/flags-iso/shiny/48/${locale.iso_code.toUpperCase()}.png`"
            class="w-8 mr-2"
          >
          <span>{{ locale.display }}</span>
        </button>
      </div>
    </template>
  </JetDropdown>
</template>

<script setup>
import JetDropdown from '@/Jetstream/Dropdown.vue';
import { usePage, useForm } from '@inertiajs/vue3';
import { onMounted } from 'vue';

const form = useForm({
    locale: usePage().props.locale,
});
let availableLocales = {};

const getAvailableLocales = () => {
    axios.get(route('locale.list')).then(response => {
        availableLocales = response.data;
    }).catch(error => {
        console.log(error);
    });
};

onMounted(() => {
    getAvailableLocales();
});

const setLocale = (locale) => {
    form.locale = locale;
    form.post(route('locale.set'), {
        preserveScroll: true,
        onSuccess: () => {
            location.reload();
        }
    });
};
</script>
