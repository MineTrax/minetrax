<script setup>
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from "@/Components/ui/dropdown-menu";
import { ScrollArea } from "@/Components/ui/scroll-area";
import { useTranslations } from "@/Composables/useTranslations";
import { Link, router, usePage } from "@inertiajs/vue3";
import { ShoppingCartIcon, XIcon } from "lucide-vue-next";
import { computed, ref } from "vue";

const { __ } = useTranslations();

const page = usePage();

// Shared by HandleInertiaRequests on every response, so the badge stays right after any add,
// remove or quantity change without this component fetching anything.
const cartCount = computed(() => Number(page.props.store?.cartCount ?? 0));

const open = ref(false);
const loading = ref(false);
const error = ref(null);
const quote = ref(null);
const removingId = ref(null);

const items = computed(() => quote.value?.items ?? []);

const itemsLabel = computed(() => (cartCount.value === 1
    ? __("1 item")
    : __(":count items", { count: cartCount.value })));

// Only worth a second line when something came off the total — a gift card, mostly. A basket
// with nothing applied would otherwise show the same figure twice.
const showsAmountDue = computed(() => quote.value && quote.value.amount_due !== quote.value.total);

/**
 * Fetched on open rather than shared with every page: quoting a basket is too expensive to ride
 * along on responses that never show it, and a mini-cart is opened rarely enough that a fetch per
 * open is the cheaper trade.
 */
const fetchQuote = () => {
    loading.value = true;
    error.value = null;

    axios.get(route("store.cart.show"))
        .then((response) => {
            quote.value = response.data.quote;
        })
        .catch((e) => {
            console.error("Error fetching cart: ", e);
            error.value = __("Failed to load your cart.");
        })
        .finally(() => {
            loading.value = false;
        });
};

const handleOpenChange = (isOpen) => {
    open.value = isOpen;

    if (isOpen) {
        fetchQuote();
    }
};

const handleRemoveItem = (item) => {
    removingId.value = item.cart_item_id;

    // preserveState keeps this layout — and the open dropdown — where it is; the badge updates from
    // the fresh shared props and the list is quoted again so its totals match.
    router.delete(route("store.cart.delete", item.cart_item_id), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => fetchQuote(),
        onFinish: () => {
            removingId.value = null;
        },
    });
};
</script>

<template>
  <DropdownMenu
    :open="open"
    @update:open="handleOpenChange"
  >
    <DropdownMenuTrigger as-child>
      <button
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
      </button>
    </DropdownMenuTrigger>

    <DropdownMenuContent
      class="w-[380px] max-w-md p-0"
      align="end"
    >
      <!-- Header -->
      <div class="flex items-center justify-between p-4 border-b">
        <div class="flex items-center gap-2">
          <ShoppingCartIcon class="w-5 h-5 text-foreground" />
          <h2 class="font-semibold text-foreground">
            {{ __("Your Cart") }}
          </h2>
        </div>
        <span
          v-if="cartCount > 0"
          class="text-sm text-muted-foreground"
        >
          {{ itemsLabel }}
        </span>
      </div>

      <!-- Content -->
      <ScrollArea class="max-h-[360px]">
        <!-- Loading Spinner -->
        <div
          v-if="loading && !quote"
          class="flex items-center justify-center py-8"
        >
          <svg
            class="animate-spin h-6 w-6 text-primary"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
          >
            <circle
              class="opacity-25"
              cx="12"
              cy="12"
              r="10"
              stroke="currentColor"
              stroke-width="4"
            />
            <path
              class="opacity-75"
              fill="currentColor"
              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            />
          </svg>
        </div>

        <!-- Error Message -->
        <div
          v-else-if="error"
          class="p-4 text-center text-destructive"
        >
          {{ error }}
        </div>

        <!-- Line Items -->
        <div
          v-else-if="items.length > 0"
          class="divide-y divide-border"
        >
          <div
            v-for="item in items"
            :key="item.cart_item_id"
            class="flex items-start gap-3 p-4"
            :class="{ 'opacity-50': removingId === item.cart_item_id }"
          >
            <div class="w-12 h-12 shrink-0 bg-muted rounded-md flex items-center justify-center overflow-hidden">
              <img
                v-if="item.photo_url"
                :src="item.photo_url"
                :alt="item.package_name"
                class="w-full h-full object-cover"
              >
              <ShoppingCartIcon
                v-else
                class="w-5 h-5 text-muted-foreground"
              />
            </div>

            <div class="flex-1 min-w-0">
              <Link
                :href="route('store.package', item.slug)"
                class="block text-sm font-semibold text-foreground truncate hover:text-primary"
                @click="open = false"
              >
                {{ item.package_name }}
              </Link>
              <p class="mt-0.5 text-xs text-muted-foreground">
                {{ item.quantity }} × {{ item.formatted.unit_price }}
              </p>
            </div>

            <div class="shrink-0 flex items-start gap-2">
              <span class="text-sm font-semibold text-foreground">
                {{ item.formatted.total }}
              </span>
              <button
                v-tippy
                :title="__('Remove')"
                :aria-label="__('Remove')"
                :disabled="removingId !== null"
                class="text-muted-foreground hover:text-destructive transition-colors disabled:opacity-50"
                @click="handleRemoveItem(item)"
              >
                <XIcon class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div
          v-else
          class="flex flex-col items-center justify-center py-12 px-4"
        >
          <div class="w-16 h-16 bg-muted rounded-full flex items-center justify-center mb-4">
            <ShoppingCartIcon class="w-8 h-8 text-muted-foreground" />
          </div>
          <p class="text-muted-foreground text-center">
            {{ __("Your cart is empty") }}
          </p>
          <Link
            :href="route('store.index')"
            class="mt-3 text-sm text-primary hover:text-primary/80 font-medium transition-colors"
            @click="open = false"
          >
            {{ __("Browse Store") }}
          </Link>
        </div>
      </ScrollArea>

      <!-- Footer -->
      <div
        v-if="items.length > 0"
        class="p-4 border-t space-y-3"
      >
        <div class="space-y-1 text-sm">
          <div class="flex justify-between">
            <span class="text-muted-foreground">{{ __("Total") }}</span>
            <span class="font-semibold text-foreground">{{ quote.formatted.total }}</span>
          </div>
          <div
            v-if="showsAmountDue"
            class="flex justify-between"
          >
            <span class="text-muted-foreground">{{ __("Amount Due") }}</span>
            <span class="font-bold text-foreground">{{ quote.formatted.amount_due }}</span>
          </div>
        </div>

        <!-- Two ways on: the cart page for codes and quantities, checkout for a basket that is
             already right. -->
        <div class="grid grid-cols-2 gap-2">
          <Link
            :href="route('store.cart.show')"
            class="px-4 py-2 text-sm font-semibold text-center rounded-lg border border-border text-foreground hover:bg-muted transition-colors"
            @click="open = false"
          >
            {{ __("View Cart") }}
          </Link>
          <Link
            :href="route('store.checkout.create')"
            class="px-4 py-2 text-sm font-semibold text-center rounded-lg bg-primary text-primary-foreground hover:bg-primary/90 transition-colors"
            @click="open = false"
          >
            {{ __("Checkout") }}
          </Link>
        </div>
      </div>
    </DropdownMenuContent>
  </DropdownMenu>
</template>
