<script setup>
import { computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import { useTranslations } from "@/Composables/useTranslations";
import { ShoppingCartIcon } from "lucide-vue-next";

const { __ } = useTranslations();

const props = defineProps({
    // Priced by the page that renders this bar, because quoting a basket is too expensive to share
    // on every response site-wide. Null when the cart is empty, or when a page shows the bar
    // without a total to give.
    total: {
        type: [String, null],
        default: null,
    },
});

const page = usePage();

// Shared by HandleInertiaRequests on every response, so this stays right after an add without
// fetching anything of its own.
const cartCount = computed(() => Number(page.props.store?.cartCount ?? 0));

const label = computed(() => (cartCount.value === 1
    ? __("1 item")
    : __(":count items", { count: cartCount.value })));

const hasTotal = computed(() => !!props.total);
</script>

<template>
  <!-- Adding no longer sends a shopper to the cart, so this is how they get there. Fixed to the
       bottom rather than in the page flow: it has to stay reachable however far down the
       catalogue they have scrolled, and it is the whole checkout path on a phone, where the
       navbar badge is behind a menu toggle. -->
  <Transition
    enter-active-class="transition duration-200 ease-out"
    enter-from-class="translate-y-full opacity-0"
    enter-to-class="translate-y-0 opacity-100"
    leave-active-class="transition duration-150 ease-in"
    leave-from-class="translate-y-0 opacity-100"
    leave-to-class="translate-y-full opacity-0"
  >
    <div
      v-if="cartCount > 0"
      class="fixed bottom-0 inset-x-0 z-40 border-t border-border bg-card/95 backdrop-blur shadow-lg"
    >
      <div class="px-4 py-3 md:px-10 max-w-screen-2xl mx-auto flex items-center justify-between gap-4">
        <div class="flex items-center gap-3 min-w-0">
          <ShoppingCartIcon class="w-5 h-5 shrink-0 text-primary" />

          <!-- The running total is the point of the bar: a shopper adding a fourth thing wants to
               know what they are up to before they commit to the cart page. Stacked under the
               count so the money is the larger of the two. -->
          <div
            v-if="hasTotal"
            class="min-w-0 leading-tight"
          >
            <p class="text-xs text-muted-foreground truncate">
              {{ label }}
            </p>
            <p class="font-bold text-foreground">
              {{ total }}
            </p>
          </div>

          <span
            v-else
            class="truncate text-sm font-medium text-foreground"
          >
            {{ cartCount === 1 ? __("1 item in your cart") : __(":count items in your cart", { count: cartCount }) }}
          </span>
        </div>

        <Link
          :href="route('store.cart.show')"
          class="shrink-0 px-5 py-2 text-sm font-semibold rounded-lg bg-primary text-primary-foreground hover:bg-primary/90 transition-colors"
        >
          {{ __("View Cart") }}
        </Link>
      </div>
    </div>
  </Transition>
</template>
