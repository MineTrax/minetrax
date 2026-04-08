<template>
  <Card v-if="enabled">
    <CardContent class="p-4 sm:px-5 space-y-3">
      <div class="flex items-center justify-between">
        <h3 class="font-extrabold text-card-foreground">
          {{ __("Donate") }}
        </h3>
        <span class="inline-flex items-center gap-1 rounded-full bg-pink-500/10 px-2.5 py-0.5 text-xs font-semibold text-pink-500">
          <!-- heart icon -->
          <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 16 16"
            fill="currentColor"
            class="h-3 w-3"
          >
            <path
              d="M2 6.342a3.375 3.375 0 0 1 6-2.088 3.375 3.375 0 0 1 5.997 2.26c-.063 2.134-1.618 3.76-2.955 4.86a14.2 14.2 0 0 1-2.345 1.583c-.276.149-.476.256-.573.31a.75.75 0 0 1-.722-.004 14.2 14.2 0 0 1-2.92-1.896C3.612 10.098 2.065 8.47 2.001 6.342Z"
            />
          </svg>
          {{ __("Support") }}
        </span>
      </div>

      <p class="text-sm text-muted-foreground">
        {{ donationText }}
      </p>

      <Button
        class="w-full font-bold gap-2"
        size="lg"
        as="a"
        target="_blank"
        :href="$page.props.generalSettings.donation_box_url"
      >
        <!-- gift icon -->
        <svg
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 16 16"
          fill="currentColor"
          class="h-4 w-4 shrink-0"
        >
          <path
            fill-rule="evenodd"
            d="M3.75 3.5c0 .563.186 1.082.5 1.5H2a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1h5.25V5H6.081a1.745 1.745 0 0 1-1.173-.454A1.74 1.74 0 0 1 4.25 3.5a1.75 1.75 0 0 1 3.5 0c0 .164-.023.323-.066.474h.632A2.24 2.24 0 0 1 8.25 3.5a1.75 1.75 0 1 1 3.5 0c0 .398-.133.764-.358 1.046A1.745 1.745 0 0 1 9.919 5H8.75v2H14a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1h-2.25a2.5 2.5 0 0 0 .5-1.5 2.75 2.75 0 0 0-5.5 0c0 .164.015.324.042.48A2.76 2.76 0 0 0 6.5.75a2.75 2.75 0 0 0-2.75 2.75ZM7.25 5V3.5a.75.75 0 0 0-1.5 0 .745.745 0 0 0 .75.75h.375V5h.375Zm1.5-1.5V5h.375a.745.745 0 0 0 .561-.25.745.745 0 0 0 .189-.5.75.75 0 0 0-1.5 0 .73.73 0 0 0 .375.625V3.5Z"
            clip-rule="evenodd"
          />
          <path d="M7.25 8H2v4.25A2.75 2.75 0 0 0 4.75 15h2.5V8ZM8.75 15V8H14v4.25A2.75 2.75 0 0 1 11.25 15h-2.5Z" />
        </svg>
        {{ __("Donate Now") }}
      </Button>
    </CardContent>
  </Card>
</template>

<script setup>
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import { sample } from "lodash";
import { useTranslations } from "@/Composables/useTranslations";
import {
    Card,
    CardContent,
} from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";

const { __ } = useTranslations();
const page = usePage();

const enabled = computed(() => {
    return page.props.generalSettings.enable_donation_box && page.props.generalSettings.donation_box_url;
});

const donationText = computed(() => {
    return sample([
        __("Help us run our servers!"),
        __("Your help mean everything to us!"),
        __("If you are capable, we would appreciate your contribution")
    ]);
});
</script>
