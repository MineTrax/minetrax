<template>
  <Head
    :title="generatedTitle + ' ' + generatedTitleSuffix"
  >
    <slot />
  </Head>
</template>

<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import {useTranslations} from '@/Composables/useTranslations';
import { computed } from 'vue';
const { __ } = useTranslations();

const props = defineProps({
    title: String,
});

const generatedTitle = computed(() => {
    if (props.title) {
        return props.title;
    }

    return window._seo?.titleHome ?? __('Minecraft Servers & Players Tracking');
});

const generatedTitleSuffix = computed(() => {
    return window._seo?.titleSuffix ?? ' - ' + usePage().props.appName;
});

</script>
