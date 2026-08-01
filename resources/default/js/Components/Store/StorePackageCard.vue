<script setup>
import { Link } from "@inertiajs/vue3";
import { useTranslations } from "@/Composables/useTranslations";
import { discountLabel } from "@/Composables/useStoreDiscount";
import { addToCart, canAddToCart } from "@/Composables/useStoreCart";
import StoreUrgencyBadges from "@/Components/Store/StoreUrgencyBadges.vue";
import { computed } from "vue";

const { __ } = useTranslations();

const props = defineProps({
    storePackage: {
        type: Object,
        required: true,
    },
});

// Stated as configured by the server, not inferred from the prices: a saving is rounded to whole
// minor units, so a flat 15% sale read as "14.8% off" on one package and "14.9% off" on another.
// A package discount and a sale both applying are listed as "10% + 15% off".
const discountBadge = computed(() => discountLabel(props.storePackage));

// Whether to strike the old price through. Compared rather than taken from discount_bp, so it covers
// a package discount, a sale, or both.
const isDiscounted = computed(
    () => Number(props.storePackage.price_original ?? 0) > Number(props.storePackage.price ?? 0)
);

const canAdd = computed(() => canAddToCart(props.storePackage));

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
    <!-- Image -->
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

    <!-- Content. flex-1 so the price and buttons can sit at the bottom of the tallest card in the
         row, keeping the action buttons on one line across the grid. -->
    <div class="p-4 flex flex-1 flex-col gap-3">
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

      <!-- Badges -->
      <div class="flex flex-wrap gap-2">
        <!-- Featured Badge -->
        <span
          v-if="storePackage.is_featured"
          class="inline-block px-2 py-1 text-xs font-medium bg-primary/10 text-primary rounded"
        >
          {{ __("Featured") }}
        </span>

        <!-- Discount Badge -->
        <span
          v-if="discountBadge"
          class="inline-block px-2 py-1 text-xs font-medium bg-success/10 text-success rounded"
        >
          {{ discountBadge }}
        </span>

        <!-- Names the sale responsible, so a shopper can tell a permanent discount from a sale -->
        <span
          v-if="storePackage.sale_name"
          class="inline-block px-2 py-1 text-xs font-medium bg-success/10 text-success rounded"
        >
          {{ storePackage.sale_name }}
        </span>

        <!-- Out of Stock Badge -->
        <span
          v-if="storePackage.is_out_of_stock"
          class="inline-block px-2 py-1 text-xs font-medium bg-destructive/10 text-destructive rounded"
        >
          {{ __("Out of Stock") }}
        </span>

        <!-- Duration Badge -->
        <span
          v-if="storePackage.expiry_duration_days"
          class="inline-block px-2 py-1 text-xs font-medium bg-muted text-muted-foreground rounded"
        >
          {{ __(":days days", { days: storePackage.expiry_duration_days }) }}
        </span>
      </div>

      <!-- Scarcity and deadline, kept out of the badge row above so they read as a warning rather
           than as another attribute of the package. -->
      <StoreUrgencyBadges :store-package="storePackage" />

      <!-- Price and actions -->
      <div class="mt-auto pt-3 border-t border-border space-y-3">
        <div class="flex items-baseline gap-2">
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

        <!-- View stays a link so it can be opened in a new tab; adding is a post, so it is a
             button. A package that has to be configured says so instead of offering two links to
             the same page, and an out of stock one is told so where the add button would have been. -->
        <div class="flex items-center gap-2">
          <Link
            :href="route('store.package', storePackage.slug)"
            class="flex-1 px-3 py-2 text-sm font-medium text-center rounded-lg border border-border bg-card text-foreground hover:bg-muted transition-colors"
          >
            {{ detailLabel }}
          </Link>

          <button
            v-if="canAdd"
            type="button"
            class="flex-1 px-3 py-2 text-sm font-medium rounded-lg bg-primary text-primary-foreground hover:bg-primary/90 transition-colors"
            @click="addToCart(storePackage)"
          >
            {{ __("Add to Cart") }}
          </button>
          <span
            v-else-if="storePackage.is_out_of_stock"
            class="flex-1 px-3 py-2 text-sm font-medium text-center rounded-lg bg-destructive/10 text-destructive"
          >
            {{ __("Out of Stock") }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>
