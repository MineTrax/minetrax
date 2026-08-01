<script setup>
import { Link } from "@inertiajs/vue3";
import { useTranslations } from "@/Composables/useTranslations";
import { addToCart, canAddToCart, useCartMembership } from "@/Composables/useStoreCart";
import { CheckIcon } from "lucide-vue-next";
import { computed } from "vue";

const { __ } = useTranslations();

const props = defineProps({
    storePackage: {
        type: Object,
        required: true,
    },
    // The stacked layout picks a quantity before adding; everywhere else adds the minimum.
    quantity: {
        type: Number,
        default: null,
    },
    // Bulk rows keep a real add button even once the package is in the cart, because buying more
    // of the same thing is the whole point of that layout.
    keepAdding: {
        type: Boolean,
        default: false,
    },
});

/**
 * The one control that answers "can I buy this, and have I already?".
 *
 * The state lives here rather than in a badge above: the action area is where the question gets
 * asked, it costs no extra space, and a card that already carries a discount tag, a featured tag,
 * a sale name and a stock warning cannot afford another pill.
 */
const { quantityInCart, isInCart } = useCartMembership(() => props.storePackage);

const canAdd = computed(() => canAddToCart(props.storePackage));

// In cart and not a bulk row: the button becomes the state and the way to the cart. Adding a
// second copy is still possible from the package page and from the cart's own stepper.
const showsCartState = computed(() => isInCart.value && !props.keepAdding);

const add = () => addToCart(props.storePackage, props.quantity);
</script>

<template>
  <Link
    v-if="showsCartState"
    :href="route('store.cart.show')"
    class="inline-flex items-center justify-center gap-1.5 px-3 py-2 text-sm font-semibold rounded-lg border border-success/50 bg-success/10 text-success hover:bg-success/20 transition-colors"
  >
    <CheckIcon class="w-4 h-4 shrink-0" />
    {{ quantityInCart > 1 ? __(":count in cart", { count: quantityInCart }) : __("In cart") }}
  </Link>

  <button
    v-else-if="canAdd"
    type="button"
    class="px-3 py-2 text-sm font-medium rounded-lg bg-primary text-primary-foreground hover:bg-primary/90 transition-colors"
    @click="add"
  >
    {{ isInCart ? __("Add another") : __("Add to Cart") }}
  </button>

  <!-- Out of stock is said here rather than in a badge above, so it is never stated twice. -->
  <span
    v-else-if="storePackage.is_out_of_stock"
    class="px-3 py-2 text-sm font-medium text-center rounded-lg bg-destructive/10 text-destructive"
  >
    {{ __("Out of Stock") }}
  </span>
</template>
