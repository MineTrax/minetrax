<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import AppHead from "@/Components/AppHead.vue";
import { Link, router } from "@inertiajs/vue3";
import { useTranslations } from "@/Composables/useTranslations";
import StoreCurrencySwitcher from "@/Components/Store/StoreCurrencySwitcher.vue";
import StorePackageCard from "@/Components/Store/StorePackageCard.vue";
import { HeartIcon, XMarkIcon } from "@heroicons/vue/24/outline";
import { computed, nextTick, ref, watch } from "vue";

const { __ } = useTranslations();

const props = defineProps({
    quote: {
        type: Object,
        required: true,
    },
    recommended: {
        type: Array,
        default: () => [],
    },
    currency: {
        type: Object,
        required: true,
    },
    // False for a store running no creator codes, which should not carry a permanently useless
    // field through its highest-intent screen.
    acceptsReferralCodes: {
        type: Boolean,
        default: false,
    },
});

// Whether a coupon or gift card is currently attached, however the server judged it. Read from
// `applied_code` rather than from coupon_code or the discount, because a coupon that was rejected —
// below its minimum, say — is still attached and still has to be removable.
const hasAppliedCode = computed(() => Boolean(props.quote.applied_code));

// Seeded from the server so the locked field shows what is actually on the cart, and re-synced
// whenever that changes: applying and clearing are both full visits, and typing a code then
// clearing it must not leave the old text sitting in a field that is editable again.
const codeInput = ref(props.quote.applied_code ?? "");
const codeLoading = ref(false);

watch(() => props.quote.applied_code, (code) => {
    codeInput.value = code ?? "";
});

const handleRemoveItem = (cartItemId) => {
    router.delete(route("store.cart.delete", cartItemId), {
        preserveScroll: true,
    });
};

/**
 * Clamp to the package's own bounds before asking the server to change anything.
 *
 * The stepper floored at 1 and had no ceiling, so a package sold in fives could be stepped down to
 * one and a capped one stepped past its cap. The server clamps either back silently, which reads
 * as the button being broken. Zero stays reachable: typing 0 is how a row is removed.
 */
const clampQuantity = (item, value) => {
    const min = item.min_quantity || 1;
    const max = item.max_quantity || Infinity;

    if (isNaN(value) || value < 0) {
        return min;
    }
    if (value === 0) {
        return 0;
    }
    return Math.min(max, Math.max(min, value));
};

const handleQuantityChange = (item, newQuantity) => {
    const quantity = clampQuantity(item, newQuantity);

    // Nothing to ask the server for if the value has not actually moved — stepping at the bound
    // would otherwise fire a request per click and flash the page for no change.
    if (quantity === item.quantity) {
        return;
    }

    router.patch(route("store.cart.update", item.cart_item_id), {
        quantity,
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

const referralInput = ref("");
const referralLoading = ref(false);
const showReferralField = ref(false);
const referralField = ref(null);

// Opening it and then having to click into it is two actions for one intent.
const revealReferralField = async () => {
    showReferralField.value = true;
    await nextTick();
    referralField.value?.focus();
};

const handleApplyReferral = () => {
    referralLoading.value = true;
    router.post(route("store.cart.referral.store"), {
        code: referralInput.value,
    }, {
        preserveScroll: true,
        // Keeps the panel open and the typed code in place when the server rejects it. Without
        // this a mistyped code is answered with a toast *and* a field that has closed and emptied
        // itself, so the buyer has to start again to fix one character.
        preserveState: true,
        onFinish: () => {
            referralLoading.value = false;
        },
    });
};

const handleClearReferral = () => {
    referralLoading.value = true;
    router.delete(route("store.cart.referral.delete"), {
        preserveScroll: true,
        onFinish: () => {
            referralLoading.value = false;
        },
    });
};

const handleClearCode = () => {
    codeLoading.value = true;
    router.post(route("store.cart.code"), {
        code: "",
    }, {
        preserveScroll: true,
        // The field is not emptied here: the watch above follows `applied_code`, so whatever the
        // server ends up holding is what the box shows. Clearing it by hand as well would fight
        // that on any response where the code survived.
        onFinish: () => {
            codeLoading.value = false;
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

                    <!-- What they already own in a cumulative category is knocked off here -->
                    <p
                      v-if="item.upgrade_credit > 0"
                      class="mt-2 text-xs text-success"
                    >
                      {{ __("Upgrade credit") }}: -{{ item.formatted.upgrade_credit }}
                    </p>

                    <!-- Variables the buyer filled in -->
                    <dl
                      v-if="item.variables?.length"
                      class="mt-2 space-y-0.5"
                    >
                      <div
                        v-for="variable in item.variables"
                        :key="variable.identifier"
                        class="text-xs text-muted-foreground"
                      >
                        <dt class="inline font-medium">
                          {{ variable.name }}:
                        </dt>
                        <dd class="inline ml-1">
                          {{ variable.value }}
                        </dd>
                      </div>
                    </dl>

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
                        class="px-2 py-1 text-muted-foreground hover:text-foreground transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                        :aria-label="__('Decrease quantity')"
                        :disabled="item.quantity <= (item.min_quantity || 1)"
                        @click="handleQuantityChange(item, item.quantity - 1)"
                      >
                        −
                      </button>
                      <input
                        :value="item.quantity"
                        type="number"
                        :min="item.min_quantity || 1"
                        :max="item.max_quantity || undefined"
                        class="w-12 text-center border-none bg-transparent text-foreground focus:outline-none"
                        @change="(e) => handleQuantityChange(item, parseInt(e.target.value, 10))"
                      >
                      <button
                        class="px-2 py-1 text-muted-foreground hover:text-foreground transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                        :aria-label="__('Increase quantity')"
                        :disabled="!!item.max_quantity && item.quantity >= item.max_quantity"
                        @click="handleQuantityChange(item, item.quantity + 1)"
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

            <!-- A sale the cart qualifies for on price but has not reached the minimum for. Stated
                 as what is still missing rather than as a discount, because it is not one yet. -->
            <div
              v-for="unlockable in (quote.unlockable_sales ?? [])"
              :key="unlockable.name"
              class="flex justify-between items-center gap-2 pb-4 border-b border-border"
            >
              <span class="text-xs text-muted-foreground">
                {{ __("Spend :amount more to unlock :name", { amount: unlockable.remaining_formatted, name: unlockable.name }) }}
              </span>
            </div>

            <!-- Upgrade Credit -->
            <div
              v-if="quote.upgrade_credit > 0"
              class="flex justify-between items-center pb-4 border-b border-border"
            >
              <span class="text-muted-foreground">{{ __("Upgrade Credit") }}</span>
              <span class="text-success font-semibold">-{{ quote.formatted.upgrade_credit }}</span>
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
              v-if="quote.tax_amount > 0"
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

              <!-- One row, one button. Clear used to sit full-width underneath, which read as
                   "clear the cart" rather than "remove this code" — and the field stayed editable
                   beside a code that was already applied, so it was never obvious which of the two
                   was in force. While a code is on, the field shows it and is locked; clearing
                   hands it back. -->
              <div class="flex gap-2">
                <!-- Enter submits. A code field that only responds to a button click is the most
                     reliably mistyped control in a checkout. -->
                <input
                  v-model="codeInput"
                  :disabled="hasAppliedCode"
                  :placeholder="__('Enter coupon or gift card code')"
                  :aria-label="__('Enter coupon or gift card code')"
                  class="flex-1 min-w-0 px-3 py-2 border border-border rounded-lg bg-card text-foreground placeholder-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 disabled:opacity-70 disabled:cursor-not-allowed"
                  @keyup.enter="!hasAppliedCode && codeInput.trim() && handleApplyCode()"
                >
                <button
                  v-if="hasAppliedCode"
                  :disabled="codeLoading"
                  class="shrink-0 px-4 py-2 border border-border text-muted-foreground font-semibold rounded-lg transition-colors hover:bg-muted disabled:opacity-50 disabled:cursor-not-allowed"
                  @click="handleClearCode"
                >
                  {{ __("Clear") }}
                </button>
                <button
                  v-else
                  :disabled="codeLoading || !codeInput.trim()"
                  class="shrink-0 px-4 py-2 bg-primary text-primary-foreground font-semibold rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed hover:bg-primary/90"
                  @click="handleApplyCode"
                >
                  {{ __("Apply") }}
                </button>
              </div>

              <!-- Referral
                   Its own control, because a referral is not a discount and does not consume the
                   coupon slot — sharing the box above made "Clear" mean two things at once. But it
                   is also the least-used field on the highest-intent screen, so it does not get a
                   second identical row: unapplied it is one line of text, applied it is a chip. -->
              <template v-if="acceptsReferralCodes || quote.referral">
                <!-- Applied. Not a locked text input — a referral picked up from a link was never
                     typed, so a disabled field showing it is a control that does nothing. -->
                <div
                  v-if="quote.referral"
                  class="flex items-center justify-between gap-2 rounded-lg bg-muted/60 px-3 py-2"
                >
                  <span class="flex items-center gap-2 min-w-0 text-sm">
                    <HeartIcon class="w-4 h-4 shrink-0 text-primary" />
                    <span class="truncate text-muted-foreground">
                      {{ __("Supporting") }}
                      <span class="font-semibold text-foreground">{{ quote.referral.referrer_name }}</span>
                    </span>
                  </span>
                  <button
                    v-tippy
                    :disabled="referralLoading"
                    :title="__('Remove')"
                    :aria-label="__('Remove')"
                    class="shrink-0 text-muted-foreground hover:text-foreground disabled:opacity-50"
                    @click="handleClearReferral"
                  >
                    <XMarkIcon class="w-4 h-4" />
                  </button>
                </div>

                <!-- Not applied. Most buyers have no code, so it stays a link until asked for. -->
                <button
                  v-else-if="!showReferralField"
                  class="text-xs text-muted-foreground underline underline-offset-2 hover:text-foreground"
                  @click="revealReferralField"
                >
                  {{ __("Have a referral code?") }}
                </button>

                <div
                  v-else
                  class="flex gap-2"
                >
                  <input
                    ref="referralField"
                    v-model="referralInput"
                    :placeholder="__('Enter a creator referral code')"
                    :aria-label="__('Enter a creator referral code')"
                    class="flex-1 min-w-0 px-3 py-2 border border-border rounded-lg bg-card text-foreground placeholder-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50"
                    @keyup.enter="referralInput.trim() && handleApplyReferral()"
                  >
                  <button
                    :disabled="referralLoading || !referralInput.trim()"
                    class="shrink-0 px-4 py-2 border border-border text-foreground font-semibold rounded-lg transition-colors hover:bg-muted disabled:opacity-50 disabled:cursor-not-allowed"
                    @click="handleApplyReferral"
                  >
                    {{ __("Apply") }}
                  </button>
                </div>
              </template>
            </div>

            <!-- Checkout Button -->
            <Link
              :href="route('store.checkout.create')"
              class="block w-full px-6 py-3 bg-primary text-primary-foreground font-semibold rounded-lg text-center hover:bg-primary/90 transition-colors"
            >
              {{ __("Checkout") }}
            </Link>

            <!-- A cart is not a decision. Sending someone who wants one more thing back through
                 the browser's history is how the second item never gets added. -->
            <Link
              :href="route('store.index')"
              class="block w-full px-6 py-2 text-sm text-center text-muted-foreground hover:text-foreground transition-colors"
            >
              &larr; {{ __("Continue Shopping") }}
            </Link>
          </div>
        </div>
      </div>

      <!-- Outside the empty/filled branch, so it shows either way: to a shopper with a basket it
           is the last chance to add something, and to one with an empty cart it is the only thing
           on the page worth clicking. -->
      <section
        v-if="recommended.length"
        class="mt-10"
      >
        <h2 class="text-xl font-bold text-foreground mb-4">
          {{ quote.items.length ? __("Add one more thing") : __("Popular right now") }}
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <StorePackageCard
            v-for="item in recommended"
            :key="item.id"
            :store-package="item"
          />
        </div>
      </section>
    </div>
  </AppLayout>
</template>
