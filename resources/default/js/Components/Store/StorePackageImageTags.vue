<script setup>
import { computed } from "vue";
import { useTranslations } from "@/Composables/useTranslations";
import { discountLabel } from "@/Composables/useStoreDiscount";
import { StarIcon } from "lucide-vue-next";

const { __ } = useTranslations();

const props = defineProps({
    storePackage: {
        type: Object,
        required: true,
    },
});

/**
 * The two facts that belong on the artwork rather than in a list under it.
 *
 * A saving and a "featured" flag are what a shopper scans a grid for, and both were competing for
 * attention in a row that also held the sale name, the duration, the stock warning and the
 * out-of-stock state. Moved onto the image they are unmissable and cost no vertical space — the
 * corner price tag every catalogue has used for a century.
 *
 * Stated as configured by the server, not inferred from the prices: a saving is rounded to whole
 * minor units, so a flat 15% sale read as "14.8% off" on one package and "14.9% off" on another.
 */
const discount = computed(() => discountLabel(props.storePackage));
</script>

<template>
  <div class="pointer-events-none absolute inset-x-0 top-0 flex items-start justify-between gap-2 p-2">
    <span
      v-if="discount"
      class="inline-block px-2 py-1 text-xs font-bold rounded bg-success text-white shadow-sm"
    >
      {{ discount }}
    </span>
    <!-- Holds the right-hand tag in place when there is no discount to show. -->
    <span v-else />

    <span
      v-if="storePackage.is_featured"
      class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold rounded bg-background/90 text-primary shadow-sm"
    >
      <StarIcon class="w-3 h-3 fill-current" />
      {{ __("Featured") }}
    </span>
  </div>
</template>
