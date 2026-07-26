<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import AppHead from "@/Components/AppHead.vue";
import { Link, router } from "@inertiajs/vue3";
import { useTranslations } from "@/Composables/useTranslations";
import StoreCurrencySwitcher from "@/Components/Store/StoreCurrencySwitcher.vue";
import { ref } from "vue";

const { __ } = useTranslations();

defineProps({
    quote: {
        type: Object,
        required: true,
    },
    currency: {
        type: Object,
        required: true,
    },
});

const codeInput = ref("");
const codeLoading = ref(false);

const handleRemoveItem = (cartItemId) => {
    router.delete(route("store.cart.delete", cartItemId), {
        preserveScroll: true,
    });
};

const handleQuantityChange = (cartItemId, newQuantity) => {
    router.patch(route("store.cart.update", cartItemId), {
        quantity: newQuantity,
    }, {
        preserveScroll: true,
    });
};

const handleApplyCode = () => {
    codeLoading.value = true;
    router.post(route("store.cart.code"), {
        code: codeInput.value,
    }, {
        preserveScroll: true,
        onFinish: () => {
            codeLoading.value = false;
        },
    });
};

const handleClearCode = () => {
    codeLoading.value = true;
    router.post(route("store.cart.code"), {
        code: "",
    }, {
        preserveScroll: true,
        onFinish: () => {
            codeLoading.value = false;
            codeInput.value = "";
        },
    });
};
</script>

<template>
  <AppLayout>
    <AppHead :title="__('Cart')" />

    <!-- Header -->
    <div class="bg-card text-card-foreground border-b border-border">
      <div class="px-2 py-6 md:px-10 max-w-screen-2xl mx-auto">
        <div class="flex justify-between items-start gap-4">
          <div>
            <h1 class="text-3xl md:text-4xl font-bold text-foreground mb-2">
              {{ __("Shopping Cart") }}
            </h1>
          </div>
          <div class="flex-shrink-0">
            <StoreCurrencySwitcher
              :currencies="currency.available"
              :current="currency.current"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="px-2 py-6 md:px-10 max-w-screen-2xl mx-auto">
      <!-- Empty State -->
      <div
        v-if="quote.items.length === 0"
        class="bg-card text-card-foreground border border-border rounded-lg shadow p-12 text-center"
      >
        <svg
          class="w-16 h-16 mx-auto text-muted-foreground mb-4"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"
          />
        </svg>
        <h3 class="text-lg font-semibold text-foreground mb-4">
          {{ __("Your cart is empty") }}
        </h3>
        <p class="text-muted-foreground mb-6">
          {{ __("Browse our store and add some items to get started.") }}
        </p>
        <Link
          :href="route('store.index')"
          class="inline-block px-6 py-3 bg-primary text-primary-foreground font-semibold rounded-lg transition-colors hover:bg-primary/90"
        >
          {{ __("Continue Shopping") }}
        </Link>
      </div>

      <!-- Cart Contents -->
      <div v-else class="flex flex-col lg:flex-row gap-6">
        <!-- Line Items -->
        <div class="lg:w-2/3 flex-1">
          <div class="space-y-4">
            <div
              v-for="item in quote.items"
              :key="item.cart_item_id"
              class="bg-card text-card-foreground border border-border rounded-lg shadow p-4 flex gap-4"
            >
              <!-- Product Image -->
              <div class="flex-shrink-0">
                <div class="w-20 h-20 bg-muted rounded-lg flex items-center justify-center overflow-hidden">
                  <img
                    v-if="item.photo_url"
                    :src="item.photo_url"
                    :alt="item.package_name"
                    class="w-full h-full object-cover"
                  >
                  <svg
                    v-else
                    class="w-12 h-12 text-muted-foreground"
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
              </div>

              <!-- Product Info -->
              <div class="flex-1 min-w-0">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                  <div class="flex-1 min-w-0">
                    <!-- Product Name -->
                    <Link
                      :href="route('store.package', item.slug)"
                      class="text-lg font-semibold text-primary hover:underline"
                    >
                      {{ item.package_name }}
                    </Link>

                    <!-- Options -->
                    <div v-if="item.options.length > 0" class="mt-2 flex flex-wrap gap-2">
                      <span
                        v-for="(option, idx) in item.options"
                        :key="idx"
                        class="inline-block px-2 py-1 bg-muted text-muted-foreground text-xs rounded"
                      >
                        {{ option.name }}
                      </span>
                    </div>

                    <!-- Sale Badge -->
                    <div v-if="item.sale_name" class="mt-2">
                      <span class="inline-block px-2 py-1 bg-success/10 text-success text-xs font-semibold rounded">
                        {{ item.sale_name }}
                      </span>
                    </div>

                    <!-- Price -->
                    <div class="mt-3 flex items-center gap-2">
                      <span v-if="item.unit_price_original !== item.unit_price" class="text-sm text-muted-foreground line-through">
                        {{ item.formatted.unit_price_original }}
                      </span>
                      <span class="text-lg font-semibold text-foreground">
                        {{ item.formatted.unit_price }}
                      </span>
                    </div>
                  </div>

                  <!-- Quantity & Remove -->
                  <div class="flex items-center gap-3">
                    <!-- Quantity Input -->
                    <div class="flex items-center border border-border rounded-lg bg-card">
                      <button
                        class="px-2 py-1 text-muted-foreground hover:text-foreground transition-colors"
                        @click="handleQuantityChange(item.cart_item_id, Math.max(1, item.quantity - 1))"
                      >
                        −
                      </button>
                      <input
                        :value="item.quantity"
                        type="number"
                        min="1"
                        class="w-12 text-center border-none bg-transparent text-foreground focus:outline-none"
                        @change="(e) => handleQuantityChange(item.cart_item_id, parseInt(e.target.value) || 1)"
                      >
                      <button
                        class="px-2 py-1 text-muted-foreground hover:text-foreground transition-colors"
                        @click="handleQuantityChange(item.cart_item_id, item.quantity + 1)"
                      >
                        +
                      </button>
                    </div>

                    <!-- Remove Button -->
                    <button
                      v-confirm="{ message: __('Are you sure you want to remove this item from your cart?') }"
                      class="px-3 py-2 text-destructive hover:bg-destructive/10 rounded-lg transition-colors"
                      @click="handleRemoveItem(item.cart_item_id)"
                    >
                      <svg
                        class="w-5 h-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                        />
                      </svg>
                    </button>
                  </div>
                </div>

                <!-- Total -->
                <div class="mt-4 pt-4 border-t border-border md:hidden">
                  <div class="text-right">
                    <p class="text-xs text-muted-foreground mb-1">
                      {{ __("Line Total") }}
                    </p>
                    <p class="text-lg font-bold text-foreground">
                      {{ item.formatted.total }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Total (Desktop) -->
              <div class="hidden md:block flex-shrink-0 text-right">
                <p class="text-xs text-muted-foreground mb-1">
                  {{ __("Line Total") }}
                </p>
                <p class="text-lg font-bold text-foreground">
                  {{ item.formatted.total }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Summary Panel -->
        <div class="lg:w-1/3 flex-shrink-0">
          <div class="bg-card text-card-foreground border border-border rounded-lg shadow p-4 md:p-6 sticky top-6 space-y-4">
            <!-- Subtotal -->
            <div class="flex justify-between items-center pb-4 border-b border-border">
              <span class="text-muted-foreground">{{ __("Subtotal") }}</span>
              <span class="text-foreground font-semibold">{{ quote.formatted.subtotal }}</span>
            </div>

            <!-- Sale Discount -->
            <div
              v-if="quote.sale_discount > 0"
              class="flex justify-between items-center pb-4 border-b border-border"
            >
              <span class="text-muted-foreground">{{ __("Sale Discount") }}</span>
              <span class="text-success font-semibold">-{{ quote.formatted.sale_discount }}</span>
            </div>

            <!-- Coupon Discount -->
            <div
              v-if="quote.coupon_discount > 0"
              class="flex justify-between items-center pb-4 border-b border-border"
            >
              <span class="text-muted-foreground">
                {{ __("Coupon Discount") }}
                <span v-if="quote.coupon_code" class="text-xs font-semibold text-success ml-1">
                  ({{ quote.coupon_code }})
                </span>
              </span>
              <span class="text-success font-semibold">-{{ quote.formatted.coupon_discount }}</span>
            </div>

            <!-- Tax -->
            <div
              v-if="quote.tax_mode !== 'none' && quote.tax_amount > 0"
              class="flex justify-between items-center pb-4 border-b border-border"
            >
              <span class="text-muted-foreground">{{ quote.tax_label }}</span>
              <span class="text-foreground font-semibold">{{ quote.formatted.tax_amount }}</span>
            </div>

            <!-- Gift Card -->
            <div
              v-if="quote.gift_card_amount > 0"
              class="flex justify-between items-center pb-4 border-b border-border"
            >
              <span class="text-muted-foreground">{{ __("Gift Card") }}</span>
              <span class="text-success font-semibold">-{{ quote.formatted.gift_card_amount }}</span>
            </div>

            <!-- Total -->
            <div class="flex justify-between items-center py-4 border-b border-border">
              <span class="text-lg font-semibold text-foreground">{{ __("Total") }}</span>
              <span class="text-2xl font-bold text-foreground">{{ quote.formatted.total }}</span>
            </div>

            <!-- Amount Due -->
            <div class="flex justify-between items-center mb-6 p-3 bg-muted rounded-lg">
              <span class="font-semibold text-foreground">{{ __("Amount Due") }}</span>
              <span class="text-xl font-bold text-foreground">{{ quote.formatted.amount_due }}</span>
            </div>

            <!-- Code Form -->
            <div class="space-y-3 pb-4 border-b border-border">
              <div v-if="quote.coupon_error" class="text-sm text-destructive bg-destructive/10 rounded p-2">
                {{ quote.coupon_error }}
              </div>

              <div class="flex gap-2">
                <input
                  v-model="codeInput"
                  :placeholder="__('Enter coupon or gift card code')"
                  class="flex-1 px-3 py-2 border border-border rounded-lg bg-card text-foreground placeholder-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50"
                >
                <button
                  :disabled="codeLoading || !codeInput.trim()"
                  class="px-4 py-2 bg-primary text-primary-foreground font-semibold rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed hover:bg-primary/90"
                  @click="handleApplyCode"
                >
                  {{ __("Apply") }}
                </button>
              </div>

              <button
                v-if="quote.coupon_code || quote.gift_card_amount > 0"
                :disabled="codeLoading"
                class="w-full px-3 py-2 text-muted-foreground hover:bg-muted rounded-lg transition-colors text-sm disabled:opacity-50"
                @click="handleClearCode"
              >
                {{ __("Clear") }}
              </button>
            </div>

            <!-- Checkout Button -->
            <button
              disabled
              class="w-full px-6 py-3 bg-primary/50 text-primary-foreground font-semibold rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ __("Checkout") }}
            </button>
            <p class="text-xs text-muted-foreground text-center">
              {{ __("Coming soon") }}
            </p>
            <!-- TODO: wire to checkout in slice 8 -->
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
