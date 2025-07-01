<template>
  <Card v-if="enabled">
    <CardContent class="p-3 space-y-2">
      <h3 class="font-extrabold text-card-foreground">
        {{ __("Donate") }}
      </h3>

      <p class="rounded text-sm text-card-foreground text-center p-1">
        {{ donationText }}
      </p>

      <div class="mt-3 flex justify-center">
        <Button
          variant="default"
          size="lg"
          as="a"
          target="_blank"
          :href="$page.props.generalSettings.donation_box_url"
        >
          {{ __("Donate Now") }}
        </Button>
      </div>
    </CardContent>
  </Card>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { sample } from 'lodash';
import { useTranslations } from '@/Composables/useTranslations';
import {
  Card,
  CardContent,
} from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'

const { __ } = useTranslations();
const page = usePage();

const enabled = computed(() => {
  return page.props.generalSettings.enable_donation_box && page.props.generalSettings.donation_box_url;
});

const donationText = computed(() => {
  return sample([
    __('Help us run our servers!'),
    __('Your help mean everything to us!'),
    __('If you are capable, we would appreciate your contribution')
  ]);
});
</script>
