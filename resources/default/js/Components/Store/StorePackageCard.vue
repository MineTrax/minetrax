<script setup>
import { Link } from "@inertiajs/vue3";
import { useTranslations } from "@/Composables/useTranslations";
import StoreBuyButton from "@/Components/Store/StoreBuyButton.vue";
import StorePackageImageTags from "@/Components/Store/StorePackageImageTags.vue";
import StoreUrgencyNote from "@/Components/Store/StoreUrgencyNote.vue";
import StoreUnlockNote from "@/Components/Store/StoreUnlockNote.vue";
import { computed } from "vue";

const { __ } = useTranslations();

const props = defineProps({
    storePackage: {
        type: Object,
        required: true,
    },
});

// Whether to strike the old price through. Compared rather than taken from discount_bp, so it covers
// a package discount, a sale, or both.
const isDiscounted = computed(
    () => Number(props.storePackage.price_original ?? 0) > Number(props.storePackage.price ?? 0)
);

// The link's label doubles as the explanation for the missing add button: a package that has to be
// answered for says "Configure", so a shopper is not left wondering why they cannot buy it here.
const detailLabel = computed(
    () => (props.storePackage.needs_configuring && !props.storePackage.is_out_of_stock
        ? __("Configure")
        : __("View"))
);
</script>

<template>
  <!-- The card used to be one big link. It now carries its own actions, so the clickable areas are
       the image and the title rather than the whole surface — a button inside a link cannot post. -->
  <div class="h-full flex flex-col bg-card text-card-foreground border border-border rounded-lg shadow hover:shadow-lg transition-shadow overflow-hidden">
    <!-- Image. Relative so the discount and featured tags can sit on it instead of queueing up in
         a pill row underneath. -->
    <div class="relative">
      <Link
        :href="route('store.package', storePackage.slug)"
        class="block aspect-video bg-muted overflow-hidden"
        :aria-label="storePackage.name"
      >
        <img
          v-if="storePackage.photo_url"
          :src="storePackage.photo_url"
          :alt="storePackage.name"
          class="w-full h-full object-cover"
          :class="{ 'opacity-40': storePackage.is_out_of_stock }"
        >
        <div
          v-else
          class="w-full h-full flex items-center justify-center text-muted-foreground"
        >
          <svg
            class="w-12 h-12"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
            />
          </svg>
        </div>
      </Link>

      <StorePackageImageTags :store-package="storePackage" />
    </div>

    <!-- Content. flex-1 so the price and buttons can sit at the bottom of the tallest card in the
         row, keeping the action buttons on one line across the grid. -->
    <div class="p-4 flex flex-1 flex-col gap-2">
      <!-- Name -->
      <h3 class="font-semibold text-foreground line-clamp-2">
        <Link
          :href="route('store.package', storePackage.slug)"
          class="hover:text-primary transition-colors"
        >
          {{ storePackage.name }}
        </Link>
      </h3>

      <!-- Short Description -->
      <p
        v-if="storePackage.short_description"
        class="text-sm text-muted-foreground line-clamp-2"
      >
        {{ storePackage.short_description }}
      </p>

      <!-- The one pill left. A sale's name is free text of unknown length, so it cannot become a
           corner tag, and it is worth keeping: it tells a permanent discount from a limited one. -->
      <div v-if="storePackage.sale_name">
        <span class="inline-block px-2 py-1 text-xs font-medium bg-success/10 text-success rounded">
          {{ storePackage.sale_name }}
        </span>
      </div>

      <StoreUnlockNote :store-package="storePackage" />

      <StoreUrgencyNote :store-package="storePackage" />

      <!-- Price and actions -->
      <div class="mt-auto pt-3 border-t border-border space-y-3">
        <div class="flex items-baseline justify-between gap-2">
          <div class="flex items-baseline gap-2 min-w-0">
            <span
              v-if="storePackage.is_pay_what_you_want"
              class="text-xs text-muted-foreground"
            >
              {{ __("from") }}
            </span>
            <span class="font-bold text-lg text-foreground">
              {{ storePackage.price_formatted }}
            </span>
            <span
              v-if="isDiscounted"
              class="text-sm text-muted-foreground line-through"
            >
              {{ storePackage.price_original_formatted }}
            </span>
          </div>

          <!-- Plain text beside the price, where a duration reads as part of what you are buying
               rather than as another status flag. -->
          <span
            v-if="storePackage.expiry_duration_days"
            class="text-xs text-muted-foreground whitespace-nowrap"
          >
            {{ __(":days days", { days: storePackage.expiry_duration_days }) }}
          </span>
        </div>

        <!-- View stays a link so it can be opened in a new tab; adding is a post, so it is a
             button — and once the package is in the cart that button becomes the way back to it. -->
        <div class="grid grid-cols-2 gap-2">
          <Link
            :href="route('store.package', storePackage.slug)"
            class="px-3 py-2 text-sm font-medium text-center rounded-lg border border-border bg-card text-foreground hover:bg-muted transition-colors"
          >
            {{ detailLabel }}
          </Link>

          <StoreBuyButton
            :store-package="storePackage"
            class="w-full"
          />
        </div>
      </div>
    </div>
  </div>
</template>
