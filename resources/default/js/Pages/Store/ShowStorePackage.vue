<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import AppHead from "@/Components/AppHead.vue";
import { Link, usePage, router } from "@inertiajs/vue3";
import { useTranslations } from "@/Composables/useTranslations";
import { useHelpers } from "@/Composables/useHelpers";
import { useFormKit } from "@/Composables/useFormKit";
import { FormKitSchema } from "@formkit/vue";
import { computed, ref } from "vue";
import AppBreadcrumb from "@/Shared/AppBreadcrumb.vue";
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

const currentUser = computed(() => page.props.auth?.user || null);
const isGuest = computed(() => !currentUser.value);

// Basis points back to a percentage for display: 1250 reads as 12.5%.
// Derived from the two prices rather than from discount_bp, which only knows about the package's own
// discount — a store-wide sale moved the price but left this null, so neither the badge nor the
// strike-through ever appeared for one.
const discountPercent = computed(() => {
    const original = Number(props.storePackage.price_original ?? 0);
    const price = Number(props.storePackage.price ?? 0);

    if (! original || price >= original) {
        return null;
    }

    return Math.round(((original - price) / original) * 1000) / 10;
});

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

const handleQuantityChange = (e) => {
    let value = parseInt(e.target.value, 10);

    if (isNaN(value)) {
        value = props.storePackage.min_quantity || 1;
    } else if (props.storePackage.min_quantity && value < props.storePackage.min_quantity) {
        value = props.storePackage.min_quantity;
    } else if (props.storePackage.max_quantity && value > props.storePackage.max_quantity) {
        value = props.storePackage.max_quantity;
    }

    quantity.value = value;
};
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
                step="0.01"
                :min="storePackage.price / (10 ** currency.exponent)"
                class="w-full px-3 py-2 border border-border rounded-lg bg-card text-foreground placeholder-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50"
                :disabled="isOutOfStock"
              >
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
            <label class="block text-sm font-medium text-foreground">
              {{ __("Quantity") }}
            </label>
            <input
              :value="quantity"
              type="number"
              :min="storePackage.min_quantity || 1"
              :max="storePackage.max_quantity || undefined"
              class="w-full px-3 py-2 border border-border rounded-lg bg-card text-foreground placeholder-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50"
              :disabled="isOutOfStock"
              @input="handleQuantityChange"
            >
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
            <h1 class="text-3xl md:text-4xl font-bold text-foreground mb-4">
              {{ storePackage.name }}
            </h1>

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
                v-if="discountPercent"
                class="text-xl text-muted-foreground line-through"
              >
                {{ storePackage.price_original_formatted }}
              </span>
            </div>

            <!-- Status Badges -->
            <div class="flex flex-wrap gap-2 mb-6">
              <span
                v-if="storePackage.is_featured"
                class="inline-block px-3 py-1 text-sm font-medium bg-primary/10 text-primary rounded-lg"
              >
                {{ __("Featured") }}
              </span>

              <span
                v-if="discountPercent"
                class="inline-block px-3 py-1 text-sm font-medium bg-success/10 text-success rounded-lg"
              >
                {{ __(":percent% off", { percent: discountPercent }) }}
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

          <!-- Login Required Message -->
          <div
            v-if="storePackage.requires_login && isGuest"
            class="bg-yellow-50 dark:bg-yellow-950 border border-yellow-200 dark:border-yellow-800 text-yellow-900 dark:text-yellow-100 rounded-lg p-4 mb-6"
          >
            <p class="font-medium mb-2">
              {{ __("Login Required") }}
            </p>
            <p class="text-sm">
              {{ __("You must be logged in to purchase this package.") }}
            </p>
          </div>

          <!-- Add to Cart Button -->
          <button
            :disabled="isOutOfStock || (storePackage.requires_login && isGuest)"
            class="w-full px-6 py-3 bg-primary text-primary-foreground font-semibold rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed hover:bg-primary/90"
            @click="handleAddToCart"
          >
            <span v-if="isOutOfStock">
              {{ __("Out of Stock") }}
            </span>
            <span v-else-if="storePackage.requires_login && isGuest">
              {{ __("Login Required") }}
            </span>
            <span v-else>
              {{ __("Add to Cart") }}
            </span>
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
