<script setup>
import { Link, usePage } from "@inertiajs/vue3";
import { useTranslations } from "@/Composables/useTranslations";
import { ShoppingCartIcon } from "lucide-vue-next";
import { computed } from "vue";

const { __ } = useTranslations();

const page = usePage();

// Shared by HandleInertiaRequests on every response, so the badge stays right after any add,
// remove or quantity change without this component fetching anything.
const cartCount = computed(() => Number(page.props.store?.cartCount ?? 0));
</script>

<template>
  <Link
    :href="route('store.cart.show')"
    class="ml-4 relative hover:cursor-pointer"
    :title="__('Cart')"
  >
    <ShoppingCartIcon
      :class="[
        'w-5 h-5 transition-all duration-300',
        cartCount > 0 ? 'text-primary' : 'text-foreground hover:text-foreground'
      ]"
    />
    <span class="sr-only">{{ __("Cart") }}</span>

    <!-- Only when there is something in it: an empty cart needs no attention drawn to it. -->
    <div
      v-if="cartCount > 0"
      class="absolute inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-primary-foreground bg-primary border-2 border-background rounded-full -top-2 -end-2"
    >
      {{ cartCount > 99 ? "99+" : cartCount }}
    </div>
  </Link>
</template>
