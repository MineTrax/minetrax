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
  <div class="bg-card text-card-foreground border border-border rounded-lg shadow hover:shadow-md transition-shadow p-4 flex flex-col sm:flex-row sm:items-center gap-4">
    <!-- Thumbnail, carrying the same corner tags the grid uses. -->
    <div class="relative w-full sm:w-24 h-24 shrink-0 rounded-lg bg-muted overflow-hidden">
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
          class="w-8 h-8"
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

      <StorePackageImageTags :store-package="storePackage" />
    </div>

    <!-- Detail -->
    <div class="flex-1 min-w-0">
      <div class="flex flex-wrap items-center gap-2">
        <Link
          :href="route('store.package', storePackage.slug)"
          class="font-semibold text-foreground hover:text-primary"
        >
          {{ storePackage.name }}
        </Link>
        <!-- The one pill left. A sale's name is free text of unknown length, so it cannot become
             a corner tag, and it is worth keeping: it tells a permanent discount from a sale. -->
        <span
          v-if="storePackage.sale_name"
          class="px-2 py-0.5 text-xs font-medium bg-success/10 text-success rounded"
        >
          {{ storePackage.sale_name }}
        </span>
      </div>

      <p
        v-if="storePackage.short_description"
        class="text-sm text-muted-foreground mt-1 line-clamp-2"
      >
        {{ storePackage.short_description }}
      </p>

      <StoreUnlockNote
        :store-package="storePackage"
        class="mt-1.5"
      />

      <StoreUrgencyNote
        :store-package="storePackage"
        class="mt-1.5"
      />
    </div>

    <!-- Price and action -->
    <div class="flex flex-wrap items-center gap-3 sm:flex-col sm:items-end sm:gap-2">
      <div class="flex items-baseline gap-2">
        <span
          v-if="storePackage.is_pay_what_you_want"
          class="text-xs text-muted-foreground"
        >
          {{ __("from") }}
        </span>
        <span class="font-bold text-lg text-foreground whitespace-nowrap">
          {{ storePackage.price_formatted }}
        </span>
        <span
          v-if="isDiscounted"
          class="text-sm text-muted-foreground line-through whitespace-nowrap"
        >
          {{ storePackage.price_original_formatted }}
        </span>
        <span
          v-if="storePackage.expiry_duration_days"
          class="text-xs text-muted-foreground whitespace-nowrap"
        >
          {{ __(":days days", { days: storePackage.expiry_duration_days }) }}
        </span>
      </div>

      <!-- View stays a link so it can be opened in a new tab; adding is a post, so it is a button
           — and once the package is in the cart that button becomes the way back to it. -->
      <div class="flex items-center gap-2 whitespace-nowrap">
        <Link
          :href="route('store.package', storePackage.slug)"
          class="px-4 py-2 text-sm font-medium rounded-lg border border-border bg-card text-foreground hover:bg-muted transition-colors"
        >
          {{ detailLabel }}
        </Link>

        <StoreBuyButton :store-package="storePackage" />
      </div>
    </div>
  </div>
</template>
