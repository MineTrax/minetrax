<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import AppHead from "@/Components/AppHead.vue";
import { Link, usePage, router } from "@inertiajs/vue3";
import { useTranslations } from "@/Composables/useTranslations";
import { discountLabel } from "@/Composables/useStoreDiscount";
import { useHelpers } from "@/Composables/useHelpers";
import { useFormKit } from "@/Composables/useFormKit";
import { FormKitSchema } from "@formkit/vue";
import { computed, ref } from "vue";
import AppBreadcrumb from "@/Shared/AppBreadcrumb.vue";
import StoreCurrencySwitcher from "@/Components/Store/StoreCurrencySwitcher.vue";
import StorePackageCard from "@/Components/Store/StorePackageCard.vue";
import StoreUrgencyNote from "@/Components/Store/StoreUrgencyNote.vue";
import { useCartMembership } from "@/Composables/useStoreCart";
import { truncate } from "lodash";

const { __ } = useTranslations();
// Package descriptions are raw TipTap HTML authored in the admin, so they are sanitised before
// being injected. Other v-html usages in this codebase render server-generated markdown instead.
const { purifyText } = useHelpers();

const props = defineProps({
    storePackage: {
        type: Object,
        required: true,
    },
    variableSchema: {
        type: Array,
        default: () => [],
    },
    relatedPackages: {
        type: Array,
        default: () => [],
    },
    currency: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const quantity = ref(props.storePackage.min_quantity || 1);

// Prefilled with the minimum, which is also what the server falls back to if it is cleared.
const customPrice = ref(
    props.storePackage.is_pay_what_you_want
        ? (props.storePackage.price / (10 ** props.currency.exponent)).toFixed(props.currency.exponent)
        : null
);

const minorFactor = computed(() => 10 ** props.currency.exponent);

// The floor and the ceiling as decimals, so the input can enforce both. The cap was already sent
// and already enforced server-side, but the field had no `max`: a buyer could type past it and
// only learn on submit, which is the one moment in a pay-what-you-want flow you do not want to
// interrupt.
const minimumAmount = computed(() => props.storePackage.price / minorFactor.value);
const maximumAmount = computed(() => (props.storePackage.pay_what_you_want_max
    ? props.storePackage.pay_what_you_want_max / minorFactor.value
    : null));

/**
 * A ladder of suggested amounts, so naming a price is one tap rather than a blank field.
 *
 * Built off the minimum rather than hardcoded, so it works whatever the currency and whatever the
 * floor. Anything above the configured cap is dropped rather than clamped, which would show the
 * same number twice.
 */
const suggestedAmounts = computed(() => {
    if (!props.storePackage.is_pay_what_you_want) {
        return [];
    }

    const minimum = minimumAmount.value;

    if (minimum <= 0) {
        return [];
    }

    return [1, 2, 5, 10]
        .map((multiplier) => minimum * multiplier)
        .filter((amount) => maximumAmount.value === null || amount <= maximumAmount.value)
        .map((amount) => amount.toFixed(props.currency.exponent));
});

const chooseAmount = (amount) => {
    customPrice.value = amount;
};

const isChosenAmount = (amount) => Number(customPrice.value) === Number(amount);

// The step belongs to the currency, never a hardcoded 0.01: JPY has no minor unit, so 0.01 would
// invite ¥1000.50 — which the server refuses outright rather than rounding — and KWD has three
// digits, where a 0.01 step rejects a legitimate 1.234.
const priceStep = computed(() =>
    props.currency.exponent === 0
        ? "1"
        : (1 / (10 ** props.currency.exponent)).toFixed(props.currency.exponent)
);

const currentUser = computed(() => page.props.auth?.user || null);
const isGuest = computed(() => !currentUser.value);

// Stated as configured by the server, not inferred from the prices: a saving is rounded to whole
// minor units, so a flat 15% sale read as "14.8% off" on one package and "14.9% off" on another.
// A package discount and a sale both applying are listed as "10% + 15% off".
const discountBadge = computed(() => discountLabel(props.storePackage));

// Whether to strike the old price through. Compared rather than taken from discount_bp, so it covers
// a package discount, a sale, or both.
const isDiscounted = computed(
    () => Number(props.storePackage.price_original ?? 0) > Number(props.storePackage.price ?? 0)
);

const sellsGiftCard = computed(() => ["giftcard", "both"].includes(props.storePackage.type?.value));

// The package's variables, rendered from a server-built FormKit schema. v-model keeps the values
// available to the Add to Cart button, which lives outside the form.
const variableValues = ref({});
const formSchema = computed(() => useFormKit().generateSchemaFromFieldsArray(props.variableSchema));

// The server validates every value again, so these are the errors that actually matter.
const variableErrors = computed(() =>
    Object.entries(page.props.errors ?? {})
        .filter(([key]) => key.startsWith("variables."))
        .map(([, message]) => message)
);

const breadcrumbItems = [
    {
        text: __("Home"),
        url: route("home"),
        current: false,
    },
    {
        text: __("Store"),
        url: route("store.index"),
        current: false,
    },
];

const categoryBreadcrumb = computed(() => {
    if (props.storePackage.category) {
        return {
            text: props.storePackage.category.name,
            url: route("store.category", props.storePackage.category.slug),
            current: false,
        };
    }
    return null;
});

const finalBreadcrumbs = computed(() => {
    const items = [...breadcrumbItems];
    if (categoryBreadcrumb.value) {
        items.push(categoryBreadcrumb.value);
    }
    items.push({
        text: truncate(props.storePackage.name, { length: 50 }),
        current: true,
    });
    return items;
});

const isOutOfStock = computed(() => props.storePackage.is_out_of_stock);

const handleAddToCart = () => {
    router.post(route("store.cart.store"), {
        package_id: props.storePackage.id,
        quantity: quantity.value,
        custom_price: props.storePackage.is_pay_what_you_want ? customPrice.value : null,
        variables: variableValues.value,
    }, {
        preserveScroll: true,
    });
};

const clampQuantity = (value) => {
    const min = props.storePackage.min_quantity || 1;
    const max = props.storePackage.max_quantity || Infinity;

    if (isNaN(value)) {
        return min;
    }
    return Math.min(max, Math.max(min, value));
};

const handleQuantityChange = (e) => {
    quantity.value = clampQuantity(parseInt(e.target.value, 10));
};

// Buttons as well as a bare number field, matching the cart and the stacked layout. Typing a
// number is a poor way to change one on a phone, and this is the page the purchase happens on.
const stepQuantity = (by) => {
    quantity.value = clampQuantity(quantity.value + by);
};

const atMinQuantity = computed(() => quantity.value <= (props.storePackage.min_quantity || 1));
const atMaxQuantity = computed(
    () => !!props.storePackage.max_quantity && quantity.value >= props.storePackage.max_quantity
);

const isBlocked = computed(
    () => isOutOfStock.value || (props.storePackage.requires_login && isGuest.value)
);

const { quantityInCart, isInCart } = useCartMembership(() => props.storePackage);
</script>

<template>
  <AppLayout>
    <AppHead :title="storePackage.name" />

    <AppBreadcrumb :items="finalBreadcrumbs" />

    <div class="py-4 px-2 md:px-10 max-w-screen-2xl mx-auto">
      <div class="flex flex-col lg:flex-row gap-6">
        <!-- Left Column: Image & Options -->
        <div class="lg:w-1/2">
          <!-- Image -->
          <div class="bg-card text-card-foreground border border-border rounded-lg shadow overflow-hidden mb-6">
            <div class="aspect-video bg-muted flex items-center justify-center">
              <img
                v-if="storePackage.photo_url"
                :src="storePackage.photo_url"
                :alt="storePackage.name"
                class="w-full h-full object-cover"
              >
              <div
                v-else
                class="w-full h-full flex items-center justify-center text-muted-foreground"
              >
                <svg
                  class="w-20 h-20"
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
          </div>

          <!-- Pay What You Want -->
          <div
            v-if="storePackage.is_pay_what_you_want"
            class="bg-card text-card-foreground border border-border rounded-lg shadow p-4 md:p-6 space-y-2"
          >
            <label
              for="custom_price"
              class="block text-sm font-medium text-foreground"
            >
              {{ __("Name your price") }}
            </label>
            <div class="flex items-center gap-2">
              <span class="text-muted-foreground">{{ currency.symbol }}</span>
              <input
                id="custom_price"
                v-model="customPrice"
                type="number"
                :step="priceStep"
                :min="minimumAmount"
                :max="maximumAmount ?? undefined"
                class="w-full px-3 py-2 border border-border rounded-lg bg-card text-foreground placeholder-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50"
                :disabled="isOutOfStock"
              >
            </div>

            <!-- One tap instead of a blank field. Pay-what-you-want packages that offer no anchor
                 get paid the minimum, because the minimum is the only number on the screen. -->
            <div
              v-if="suggestedAmounts.length > 1"
              class="flex flex-wrap gap-2 pt-1"
            >
              <button
                v-for="amount in suggestedAmounts"
                :key="amount"
                type="button"
                class="px-3 py-1.5 text-sm rounded-lg border transition-colors cursor-pointer"
                :class="isChosenAmount(amount)
                  ? 'bg-primary text-primary-foreground border-primary'
                  : 'border-border text-foreground hover:bg-muted'"
                :disabled="isOutOfStock"
                @click="chooseAmount(amount)"
              >
                {{ currency.symbol }}{{ amount }}
              </button>
            </div>

            <p class="text-xs text-muted-foreground">
              {{ __("Minimum:") }} {{ storePackage.price_formatted }}
            </p>
            <p
              v-if="$page.props.errors?.custom_price"
              class="text-xs text-destructive"
            >
              {{ $page.props.errors.custom_price }}
            </p>
          </div>

          <!-- Quantity -->
          <div
            v-else
            class="bg-card text-card-foreground border border-border rounded-lg shadow p-4 md:p-6 space-y-2"
          >
            <label
              for="quantity"
              class="block text-sm font-medium text-foreground"
            >
              {{ __("Quantity") }}
            </label>
            <div class="inline-flex items-center border border-border rounded-lg bg-card">
              <button
                type="button"
                class="px-3 py-2 text-muted-foreground hover:text-foreground transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                :aria-label="__('Decrease quantity')"
                :disabled="isOutOfStock || atMinQuantity"
                @click="stepQuantity(-1)"
              >
                &minus;
              </button>
              <input
                id="quantity"
                :value="quantity"
                type="number"
                :min="storePackage.min_quantity || 1"
                :max="storePackage.max_quantity || undefined"
                class="w-16 text-center bg-transparent text-foreground border-0 focus:outline-none focus:ring-0"
                :disabled="isOutOfStock"
                @input="handleQuantityChange"
              >
              <button
                type="button"
                class="px-3 py-2 text-muted-foreground hover:text-foreground transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                :aria-label="__('Increase quantity')"
                :disabled="isOutOfStock || atMaxQuantity"
                @click="stepQuantity(1)"
              >
                &plus;
              </button>
            </div>
            <p class="text-xs text-muted-foreground">
              <span v-if="storePackage.min_quantity">
                {{ __("Minimum:") }} {{ storePackage.min_quantity }}
              </span>
              <span v-if="storePackage.min_quantity && storePackage.max_quantity">•</span>
              <span v-if="storePackage.max_quantity">
                {{ __("Maximum:") }} {{ storePackage.max_quantity }}
              </span>
            </p>
          </div>

          <!-- Variables the buyer fills in -->
          <div
            v-if="variableSchema.length"
            class="bg-card text-card-foreground border border-border rounded-lg shadow p-4 md:p-6 mt-6"
          >
            <h3 class="text-sm font-semibold text-foreground mb-3">
              {{ __("Your details") }}
            </h3>
            <FormKit
              v-model="variableValues"
              type="form"
              :actions="false"
            >
              <FormKitSchema :schema="formSchema" />
            </FormKit>
            <ul
              v-if="variableErrors.length"
              class="mt-2 space-y-1"
            >
              <li
                v-for="(error, index) in variableErrors"
                :key="index"
                class="text-xs text-destructive"
              >
                {{ error }}
              </li>
            </ul>
          </div>

          <!-- Requirements -->
          <div
            v-if="storePackage.required_packages?.length"
            class="bg-card text-card-foreground border border-border rounded-lg shadow p-4 md:p-6 mt-6"
          >
            <h3 class="text-sm font-semibold text-foreground mb-2">
              {{ storePackage.required_packages_mode?.value === "any"
                ? __("Requires one of these packages")
                : __("Requires all of these packages") }}
            </h3>
            <ul class="space-y-1">
              <li
                v-for="requirement in storePackage.required_packages"
                :key="requirement.id"
              >
                <Link
                  :href="route('store.package', requirement.slug)"
                  class="text-sm text-primary hover:underline"
                >
                  {{ requirement.name }}
                </Link>
              </li>
            </ul>
            <p class="text-xs text-muted-foreground mt-2">
              {{ __("Checked at checkout against the player you are buying for.") }}
            </p>
          </div>
        </div>

        <!-- Right Column: Details -->
        <div class="lg:w-1/2">
          <!-- Title & Price -->
          <div class="mb-6">
            <div class="flex justify-between items-start gap-4 mb-4">
              <h1 class="text-3xl md:text-4xl font-bold text-foreground">
                {{ storePackage.name }}
              </h1>
              <!-- The storefront and the cart both offer this; the page where the price is
                   actually read did not, so a shopper in the wrong currency had to navigate away
                   and come back to change it. -->
              <div class="shrink-0">
                <StoreCurrencySwitcher
                  :currencies="currency.available"
                  :current="currency.current"
                />
              </div>
            </div>

            <div class="flex items-baseline gap-2 mb-4">
              <span
                v-if="storePackage.is_pay_what_you_want"
                class="text-sm text-muted-foreground"
              >
                {{ __("from") }}
              </span>
              <span class="text-4xl font-bold text-foreground">
                {{ storePackage.price_formatted }}
              </span>
              <span
                v-if="isDiscounted"
                class="text-xl text-muted-foreground line-through"
              >
                {{ storePackage.price_original_formatted }}
              </span>
            </div>

            <!-- Status Badges. No "in cart" pill here either — the buy button below says it, and
                 this row is long enough already. -->
            <div class="flex flex-wrap gap-2 mb-6">
              <span
                v-if="storePackage.is_featured"
                class="inline-block px-3 py-1 text-sm font-medium bg-primary/10 text-primary rounded-lg"
              >
                {{ __("Featured") }}
              </span>

              <span
                v-if="discountBadge"
                class="inline-block px-3 py-1 text-sm font-medium bg-success/10 text-success rounded-lg"
              >
                {{ discountBadge }}
              </span>

              <span
                v-if="storePackage.sale_name"
                class="inline-block px-3 py-1 text-sm font-medium bg-success/10 text-success rounded-lg"
              >
                {{ storePackage.sale_name }}
              </span>

              <span
                v-if="sellsGiftCard"
                class="inline-block px-3 py-1 text-sm font-medium bg-muted text-muted-foreground rounded-lg"
              >
                {{ __("Includes store credit") }}
              </span>

              <span
                v-if="isOutOfStock"
                class="inline-block px-3 py-1 text-sm font-medium bg-destructive/10 text-destructive rounded-lg"
              >
                {{ __("Out of Stock") }}
              </span>

              <span
                v-if="storePackage.expiry_duration_days"
                class="inline-block px-3 py-1 text-sm font-medium bg-muted text-muted-foreground rounded-lg"
              >
                {{ __(":days days", { days: storePackage.expiry_duration_days }) }}
              </span>
            </div>

            <StoreUrgencyNote :store-package="storePackage" />
          </div>

          <!-- Description -->
          <div
            v-if="storePackage.description"
            class="bg-card text-card-foreground border border-border rounded-lg shadow p-4 md:p-6 mb-6"
          >
            <h3 class="text-lg font-semibold text-foreground mb-4">
              {{ __("Description") }}
            </h3>
            <div
              class="prose dark:prose-invert max-w-none text-card-foreground/90 prose-headings:text-card-foreground prose-p:text-card-foreground/90 prose-strong:text-card-foreground prose-a:text-primary prose-a:no-underline hover:prose-a:underline prose-blockquote:border-primary/30 prose-blockquote:text-card-foreground/70 prose-code:text-primary prose-code:bg-muted prose-code:rounded prose-code:px-1 prose-pre:bg-muted prose-img:rounded-lg"
              v-html="purifyText(storePackage.description)"
            />
          </div>

          <!-- Login Required Message. The button below is disabled, so this block has to carry the
               way forward — it used to state the rule and stop, leaving a guest on a dead page
               with no sign-in link anywhere near the thing they were trying to buy. -->
          <div
            v-if="storePackage.requires_login && isGuest"
            class="bg-yellow-50 dark:bg-yellow-950 border border-yellow-200 dark:border-yellow-800 text-yellow-900 dark:text-yellow-100 rounded-lg p-4 mb-6"
          >
            <p class="font-medium mb-2">
              {{ __("Login Required") }}
            </p>
            <p class="text-sm mb-3">
              {{ __("You must be logged in to purchase this package.") }}
            </p>
            <div class="flex flex-wrap gap-2">
              <Link
                :href="route('login')"
                class="px-4 py-2 text-sm font-semibold rounded-lg bg-primary text-primary-foreground hover:bg-primary/90 transition-colors"
              >
                {{ __("Sign in") }}
              </Link>
              <Link
                :href="route('register')"
                class="px-4 py-2 text-sm font-semibold rounded-lg border border-yellow-300 dark:border-yellow-700 hover:bg-yellow-100 dark:hover:bg-yellow-900 transition-colors"
              >
                {{ __("Create an account") }}
              </Link>
            </div>
          </div>

          <!-- Add to Cart Button -->
          <button
            :disabled="isBlocked"
            class="w-full px-6 py-3 bg-primary text-primary-foreground font-semibold rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed hover:bg-primary/90"
            @click="handleAddToCart"
          >
            <span v-if="isOutOfStock">
              {{ __("Out of Stock") }}
            </span>
            <span v-else-if="storePackage.requires_login && isGuest">
              {{ __("Login Required") }}
            </span>
            <span v-else-if="isInCart">
              {{ __("Add another") }}
            </span>
            <span v-else>
              {{ __("Add to Cart") }}
            </span>
          </button>

          <!-- Only once it is in there. A permanent second button competing with the primary one
               would cost more conversions than it saves. -->
          <Link
            v-if="isInCart"
            :href="route('store.cart.show')"
            class="block w-full mt-2 px-6 py-3 text-center font-semibold rounded-lg border border-success/50 text-success hover:bg-success/10 transition-colors"
          >
            {{ __("Go to cart") }}
          </Link>
        </div>
      </div>

      <!-- More to buy, from the category the shopper is already reading. Without it the package
           page is a cul-de-sac: the only ways on are the back button and the breadcrumb. -->
      <section
        v-if="relatedPackages.length"
        class="mt-12"
      >
        <h2 class="text-xl font-bold text-foreground mb-4">
          {{ __("You might also like") }}
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <StorePackageCard
            v-for="related in relatedPackages"
            :key="related.id"
            :store-package="related"
          />
        </div>
      </section>
    </div>

    <!-- On a phone the buy button sits under an image, a price, a form and a description, so the
         page's own call to action is a scroll away from wherever the shopper stopped reading. -->
    <div
      v-if="!isBlocked"
      class="lg:hidden fixed bottom-0 inset-x-0 z-40 border-t border-border bg-card/95 backdrop-blur shadow-lg px-4 py-3 flex items-center justify-between gap-4"
    >
      <div class="min-w-0">
        <p class="text-xs text-muted-foreground truncate">
          {{ storePackage.name }}
        </p>
        <p class="font-bold text-foreground">
          {{ storePackage.price_formatted }}
        </p>
      </div>
      <div class="flex shrink-0 items-center gap-2">
        <Link
          v-if="isInCart"
          :href="route('store.cart.show')"
          class="px-4 py-2.5 text-sm font-semibold rounded-lg border border-success/50 text-success"
        >
          {{ __("Cart") }} · {{ quantityInCart }}
        </Link>
        <button
          class="px-6 py-2.5 bg-primary text-primary-foreground font-semibold rounded-lg hover:bg-primary/90 transition-colors"
          @click="handleAddToCart"
        >
          {{ isInCart ? __("Add another") : __("Add to Cart") }}
        </button>
      </div>
    </div>
    <div
      v-if="!isBlocked"
      class="lg:hidden h-20"
    />
  </AppLayout>
</template>
