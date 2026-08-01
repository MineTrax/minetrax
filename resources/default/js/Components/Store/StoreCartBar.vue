<script setup>
import { computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import { useTranslations } from "@/Composables/useTranslations";
import { ShoppingCartIcon } from "lucide-vue-next";

const { __ } = useTranslations();

const page = usePage();

// Shared by HandleInertiaRequests on every response, so this stays right after an add without
// fetching anything of its own.
const cartCount = computed(() => Number(page.props.store?.cartCount ?? 0));
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
        <div class="flex items-center gap-2 text-sm text-foreground min-w-0">
          <ShoppingCartIcon class="w-5 h-5 shrink-0 text-primary" />
          <span class="truncate font-medium">
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
