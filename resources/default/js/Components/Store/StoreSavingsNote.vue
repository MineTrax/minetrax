<script setup>
import { computed } from "vue";
import { discountLabel } from "@/Composables/useStoreDiscount";

const props = defineProps({
    storePackage: {
        type: Object,
        required: true,
    },
});

/**
 * Why the price is down, for the layouts that have no artwork to hang a corner tag on.
 *
 * The comparison table and the stacked rows carry no image, so the discount tag the grid and the
 * listing put on the thumbnail has nowhere to go — which is why a store-wide sale reached neither
 * of them. The stacked rows showed no saving at all, and the comparison table struck the old price
 * through without ever saying what had reduced it.
 *
 * Stated as configured by the server, not inferred from the prices: a saving is rounded to whole
 * minor units, so a flat 15% sale read as "14.8% off" on one package and "14.9% off" on another.
 * The sale's name comes first because it is what makes the reduction feel finite.
 */
const discount = computed(() => discountLabel(props.storePackage));

const parts = computed(() => [props.storePackage.sale_name, discount.value].filter(Boolean));
</script>

<template>
  <p
    v-if="parts.length"
    class="text-xs font-semibold text-success"
  >
    {{ parts.join(" · ") }}
  </p>
</template>
