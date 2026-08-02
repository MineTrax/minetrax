<script setup>
import { computed } from "vue";
import { unlockLabel } from "@/Composables/useStoreDiscount";

const props = defineProps({
    storePackage: {
        type: Object,
        required: true,
    },
});

/**
 * A sale this package is eligible for but the shopper has not earned yet.
 *
 * A sale gated on a cart total cannot be priced into a card: a listing has no cart to measure, so
 * showing the reduced figure would advertise a price the cart then refuses to honour. The price
 * stays undiscounted and the offer is stated as the condition it is — which is also the only way a
 * "spend $50, get 20% off" promotion can do its job, since nobody spends $50 to find out.
 *
 * Muted rather than success-coloured, so it reads as an offer rather than as money already saved.
 */
const label = computed(() => unlockLabel(props.storePackage));
</script>

<template>
  <p
    v-if="label"
    class="text-xs font-medium text-muted-foreground"
  >
    {{ label }}
  </p>
</template>
