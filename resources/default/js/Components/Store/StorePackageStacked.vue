<script setup>
import { Link } from "@inertiajs/vue3";
import { useTranslations } from "@/Composables/useTranslations";
import { canAddToCart, useCartMembership } from "@/Composables/useStoreCart";
import StoreBuyButton from "@/Components/Store/StoreBuyButton.vue";
import StoreSavingsNote from "@/Components/Store/StoreSavingsNote.vue";
import StoreUrgencyNote from "@/Components/Store/StoreUrgencyNote.vue";
import { CheckIcon } from "lucide-vue-next";
import { computed, ref } from "vue";

const { __ } = useTranslations();

const props = defineProps({
    storePackage: {
        type: Object,
        required: true,
    },
});

const quantity = ref(props.storePackage.min_quantity || 1);

const clamp = (value) => {
    const min = props.storePackage.min_quantity || 1;
    const max = props.storePackage.max_quantity || Infinity;

    if (isNaN(value)) {
        return min;
    }
    return Math.min(max, Math.max(min, value));
};

const step = (by) => {
    quantity.value = clamp(quantity.value + by);
};

const onInput = (event) => {
    quantity.value = clamp(parseInt(event.target.value, 10));
};

// Bulk items are the point of this layout, so the quantity is chosen here rather than on the
// package page. Anything that has to be configured first links through to its own page instead —
// and gets no quantity picker, since there is nothing here to add.
const canAdd = computed(() => canAddToCart(props.storePackage));

// Whether to strike the old price through. Compared rather than taken from discount_bp, so it
// covers a package discount, a sale, or both.
const isDiscounted = computed(
    () => Number(props.storePackage.price_original ?? 0) > Number(props.storePackage.price ?? 0)
);

// The only layout that keeps a real add button once the package is in the cart: buying more of the
// same thing is what a bulk row is for, so the state is a line of text beside the price rather
// than taking the button over.
const { quantityInCart, isInCart } = useCartMembership(() => props.storePackage);

// The link's label doubles as the explanation for the missing add button: a package that has to be
// answered for says "Configure", so a shopper is not left wondering why they cannot buy it here.
const detailLabel = computed(
    () => (props.storePackage.needs_configuring && !props.storePackage.is_out_of_stock
        ? __("Configure")
        : __("View"))
);
</script>

<template>
  <div class="bg-card text-card-foreground border border-border rounded-lg shadow p-4 flex flex-col sm:flex-row sm:items-center gap-4">
    <!-- Detail -->
    <div class="flex-1 min-w-0">
      <div class="flex flex-wrap items-center gap-2">
        <Link
          :href="route('store.package', storePackage.slug)"
          class="font-semibold text-foreground hover:text-primary"
        >
          {{ storePackage.name }}
        </Link>
        <span
          v-if="storePackage.is_featured"
          class="px-2 py-0.5 text-xs font-medium bg-primary/10 text-primary rounded"
        >
          {{ __("Featured") }}
        </span>
      </div>
      <p
        v-if="storePackage.short_description"
        class="text-sm text-muted-foreground mt-1 line-clamp-2"
      >
        {{ storePackage.short_description }}
      </p>

      <p class="flex flex-wrap items-center gap-x-2 text-xs text-muted-foreground mt-1">
        <span>
          {{ __("Each") }}:
          <span class="font-semibold text-foreground">{{ storePackage.price_formatted }}</span>
          <!-- This row showed the reduced price with nothing to compare it against, so a sale
               looked like the everyday price. -->
          <span
            v-if="isDiscounted"
            class="ml-1 line-through"
          >
            {{ storePackage.price_original_formatted }}
          </span>
        </span>
        <Link
          v-if="isInCart"
          :href="route('store.cart.show')"
          class="inline-flex items-center gap-1 font-semibold text-success hover:underline"
        >
          <CheckIcon class="w-3 h-3" />
          {{ __(":count in cart", { count: quantityInCart }) }}
        </Link>
      </p>

      <StoreSavingsNote
        :store-package="storePackage"
        class="mt-1"
      />

      <StoreUrgencyNote
        :store-package="storePackage"
        class="mt-1.5"
      />
    </div>

    <!-- Quantity tier -->
    <div class="flex flex-wrap items-center gap-3">
      <div
        v-if="canAdd"
        class="flex items-center border border-border rounded-lg bg-card"
      >
        <button
          type="button"
          class="px-3 py-2 text-muted-foreground hover:text-foreground transition-colors"
          :aria-label="__('Decrease quantity')"
          @click="step(-1)"
        >
          &minus;
        </button>
        <input
          :value="quantity"
          type="number"
          :min="storePackage.min_quantity || 1"
          :max="storePackage.max_quantity || undefined"
          class="w-16 text-center bg-transparent text-foreground border-0 focus:outline-none focus:ring-0"
          @input="onInput"
        >
        <button
          type="button"
          class="px-3 py-2 text-muted-foreground hover:text-foreground transition-colors"
          :aria-label="__('Increase quantity')"
          @click="step(1)"
        >
          &plus;
        </button>
      </div>

      <!-- View stays a link so it can be opened in a new tab; adding is a post, so it is a button.
           Out of stock keeps the link, because the page is still worth reading. -->
      <Link
        :href="route('store.package', storePackage.slug)"
        class="px-4 py-2 text-sm font-medium rounded-lg border border-border bg-card text-foreground hover:bg-muted transition-colors whitespace-nowrap"
      >
        {{ detailLabel }}
      </Link>

      <StoreBuyButton
        :store-package="storePackage"
        :quantity="quantity"
        keep-adding
        class="whitespace-nowrap"
      />
    </div>
  </div>
</template>
