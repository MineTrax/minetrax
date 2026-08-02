<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { useTranslations } from "@/Composables/useTranslations";
import AppBreadcrumb from "@/Shared/AppBreadcrumb.vue";
import { Button } from "@/Components/ui/button";
import { Link, useForm } from "@inertiajs/vue3";
import XInput from "@/Components/Form/XInput.vue";
import XSelect from "@/Components/Form/XSelect.vue";
import XSwitch from "@/Components/Form/XSwitch.vue";
import Multiselect from "vue-multiselect";
import { useFormErrors } from "@/Composables/useFormErrors";
import { computed } from "vue";

const { __ } = useTranslations();
// Laravel keys a per-item array failure as `packages.0`, not `packages`, so reading the bare field
// name renders nothing and a rejected save looks like a save that silently did nothing.
const { fieldError } = useFormErrors();

const props = defineProps({
    packages: Array,
    categories: Array,
    currencies: Array,
    baseCurrency: Object,
});

const breadcrumbItems = [
    {
        text: __("Admin"),
        current: false,
    },
    {
        text: __("Store Coupons"),
        url: route("admin.store.coupon.index"),
        current: false,
    },
    {
        text: __("Create Coupon"),
        current: true,
    }
];

const discountTypeOptions = {
    percent: __("Percentage Off"),
    fixed: __("Fixed Amount Off"),
};

const currencyOptions = props.currencies.reduce((acc, currency) => {
    return { ...acc, [currency.code]: `${currency.code} (${currency.symbol})` };
}, {});

// The step belongs to the currency the amount is typed in, never a hardcoded 0.01: JPY has no minor
// unit and KWD has three, so a fixed step either invites an amount the server refuses or rejects a
// legitimate one.
function stepFor(exponent) {
    return exponent === 0 ? "1" : (1 / (10 ** exponent)).toFixed(exponent);
}

const baseStep = computed(() => stepFor(props.baseCurrency?.exponent ?? 2));

const amountStep = computed(() => stepFor(amountExponent.value));

const form = useForm({
    code: null,
    description: null,
    discount_type: "percent",
    // Entered as a human figure and converted on submit: percentages are stored as basis points and
    // amounts as minor units, neither of which anyone wants to type.
    discount_percent: 10,
    discount_amount: null,
    currency_code: props.baseCurrency.code,
    min_basket: null,
    max_uses_total: null,
    max_uses_per_user: null,
    starts_at: null,
    expires_at: null,
    is_enabled: true,
    packages: [],
    categories: [],
});

const isPercent = computed(() => form.discount_type === "percent");

// The exponent belongs to the currency the amount is typed in, never a constant: JPY has no minor
// unit and KWD has three digits, so a fixed 100 would misprice both.
const amountExponent = computed(() => {
    const currency = props.currencies.find(item => item.code === form.currency_code);

    return currency?.exponent ?? props.baseCurrency.exponent ?? 2;
});

function toMinorUnits(decimalAmount, exponent) {
    if (decimalAmount === null || decimalAmount === "" || isNaN(parseFloat(decimalAmount))) {
        return null;
    }
    return Math.round(parseFloat(decimalAmount) * (10 ** exponent));
}

function toBasisPoints(percent) {
    if (percent === null || percent === "" || isNaN(parseFloat(percent))) {
        return null;
    }
    return Math.round(parseFloat(percent) * 100);
}

function couponPayload(form) {
    return {
        ...form.data(),
        discount_value: isPercent.value
            ? toBasisPoints(form.discount_percent)
            : toMinorUnits(form.discount_amount, amountExponent.value),
        currency_code: isPercent.value ? null : form.currency_code,
        // The minimum is compared against a basket subtotal converted to the base currency, so it is
        // always entered in the base currency however the buyer is paying.
        min_basket_amount: toMinorUnits(form.min_basket, props.baseCurrency.exponent),
        // Multiselect hands back whole objects; the API takes ids.
        packages: (form.packages ?? []).map(item => item.id),
        categories: (form.categories ?? []).map(item => item.id),
    };
}

function createCoupon() {
    form.transform(() => couponPayload(form)).post(route("admin.store.coupon.store"), {});
}
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Create Store Coupon')" />

    <div class="px-10 py-8 mx-auto max-w-6xl text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
      </div>

      <div class="mt-6">
        <form @submit.prevent="createCoupon">
          <!-- Discount Section -->
          <div class="shadow overflow-hidden rounded-lg mb-6 card-clip-safe">
            <div class="px-4 py-5 bg-card sm:p-6 border-b border-border">
              <h3 class="text-lg font-medium text-foreground mb-4">
                {{ __("Discount") }}
              </h3>
              <div class="grid grid-cols-6 gap-6">
                <div class="col-span-6 sm:col-span-2">
                  <XInput
                    id="code"
                    v-model="form.code"
                    :label="__('Coupon Code')"
                    :help="__('What the customer types in. Saved in capitals.')"
                    :error="form.errors.code"
                    type="text"
                    name="code"
                    required
                  />
                </div>

                <div class="col-span-6 sm:col-span-4">
                  <XInput
                    id="description"
                    v-model="form.description"
                    :label="__('Description')"
                    :help="__('Staff-facing note. Never shown to the customer.')"
                    :error="form.errors.description"
                    type="text"
                    name="description"
                  />
                </div>

                <div class="col-span-6 sm:col-span-2">
                  <XSelect
                    id="discount_type"
                    v-model="form.discount_type"
                    name="discount_type"
                    :label="__('Discount Type')"
                    :select-list="discountTypeOptions"
                    :error="form.errors.discount_type"
                    :disable-null="true"
                  />
                </div>

                <div
                  v-if="isPercent"
                  class="col-span-6 sm:col-span-2"
                >
                  <XInput
                    id="discount_percent"
                    v-model="form.discount_percent"
                    :label="__('Percentage Off')"
                    :help="__('Applied to the lines the coupon covers, after any sale.')"
                    :error="form.errors.discount_value"
                    type="number"
                    step="0.01"
                    name="discount_percent"
                    min="0.01"
                    max="100"
                    required
                  />
                </div>

                <template v-else>
                  <div class="col-span-6 sm:col-span-2">
                    <XInput
                      id="discount_amount"
                      v-model="form.discount_amount"
                      :label="__('Amount Off')"
                      :help="__('Decimal amount, e.g. 5.00')"
                      :error="form.errors.discount_value"
                      type="number"
                      :step="amountStep"
                      name="discount_amount"
                      min="0"
                      required
                    />
                  </div>

                  <div class="col-span-6 sm:col-span-2">
                    <XSelect
                      id="currency_code"
                      v-model="form.currency_code"
                      name="currency_code"
                      :label="__('Amount Currency')"
                      :help="__('Converted at the order\'s rate when the customer pays in another currency.')"
                      :select-list="currencyOptions"
                      :error="form.errors.currency_code"
                      :disable-null="true"
                    />
                  </div>
                </template>
              </div>
            </div>
          </div>

          <!-- Conditions Section -->
          <div class="shadow overflow-hidden rounded-lg mb-6 card-clip-safe">
            <div class="px-4 py-5 bg-card sm:p-6 border-b border-border">
              <h3 class="text-lg font-medium text-foreground mb-4">
                {{ __("Conditions") }}
              </h3>
              <div class="grid grid-cols-6 gap-6">
                <div class="col-span-6 sm:col-span-2">
                  <XInput
                    id="min_basket"
                    v-model="form.min_basket"
                    :label="__('Minimum Cart Total')"
                    :help="__('In :currency. Leave empty for no minimum.', { currency: baseCurrency.code })"
                    :error="form.errors.min_basket_amount"
                    type="number"
                    :step="baseStep"
                    name="min_basket"
                    min="0"
                  />
                </div>

                <div class="col-span-6 sm:col-span-2">
                  <XInput
                    id="max_uses_total"
                    v-model="form.max_uses_total"
                    :label="__('Total Redemptions')"
                    :help="__('Across all customers. Leave empty for unlimited.')"
                    :error="form.errors.max_uses_total"
                    type="number"
                    name="max_uses_total"
                    min="1"
                  />
                </div>

                <div class="col-span-6 sm:col-span-2">
                  <XInput
                    id="max_uses_per_user"
                    v-model="form.max_uses_per_user"
                    :label="__('Redemptions Per Account')"
                    :help="__('Setting this requires the customer to be signed in.')"
                    :error="form.errors.max_uses_per_user"
                    type="number"
                    name="max_uses_per_user"
                    min="1"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="starts_at"
                    v-model="form.starts_at"
                    :label="__('Valid From')"
                    :help="__('Rejected before this moment. Leave empty to start immediately.')"
                    :error="form.errors.starts_at"
                    type="datetime-local"
                    name="starts_at"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="expires_at"
                    v-model="form.expires_at"
                    :label="__('Valid Until')"
                    :help="__('Rejected after this moment. Leave empty for no expiry.')"
                    :error="form.errors.expires_at"
                    type="datetime-local"
                    name="expires_at"
                  />
                </div>

                <div class="flex items-center col-span-6 sm:col-span-3">
                  <XSwitch
                    id="is_enabled"
                    v-model="form.is_enabled"
                    :label="__('Enabled')"
                    :help="__('A disabled coupon is refused even inside its window.')"
                    name="is_enabled"
                    :error="form.errors.is_enabled"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Scope Section -->
          <div class="shadow rounded-lg mb-6 card-clip-safe">
            <div class="px-4 py-5 bg-card sm:p-6 border-b border-border">
              <h3 class="text-lg font-medium text-foreground mb-1">
                {{ __("Applies To") }}
              </h3>
              <p class="text-sm text-muted-foreground mb-4">
                {{ __("Leave both empty and the coupon discounts everything in the cart. Otherwise only the packages and categories picked here are discounted.") }}
              </p>
              <div class="grid grid-cols-6 gap-6">
                <div class="col-span-6 sm:col-span-3">
                  <label
                    for="packages"
                    class="block text-sm font-medium text-foreground mb-2"
                  >{{ __("Packages") }}</label>
                  <Multiselect
                    id="packages"
                    v-model="form.packages"
                    class="block w-full border-input rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
                    :options="packages"
                    :multiple="true"
                    :close-on-select="false"
                    :clear-on-select="false"
                    :preserve-search="true"
                    track-by="id"
                    label="name"
                    :placeholder="__('Search packages')+'...'"
                  />
                  <p
                    v-if="fieldError(form.errors, 'packages')"
                    class="text-xs text-destructive mt-2"
                  >
                    {{ fieldError(form.errors, 'packages') }}
                  </p>
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <label
                    for="categories"
                    class="block text-sm font-medium text-foreground mb-2"
                  >{{ __("Categories") }}</label>
                  <Multiselect
                    id="categories"
                    v-model="form.categories"
                    class="block w-full border-input rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
                    :options="categories"
                    :multiple="true"
                    :close-on-select="false"
                    :clear-on-select="false"
                    :preserve-search="true"
                    track-by="id"
                    label="name"
                    :placeholder="__('Search categories')+'...'"
                  />
                  <p
                    v-if="fieldError(form.errors, 'categories')"
                    class="text-xs text-destructive mt-2"
                  >
                    {{ fieldError(form.errors, 'categories') }}
                  </p>
                </div>
              </div>
            </div>
            <div class="px-4 py-3 bg-card border-t border-border sm:px-6 flex justify-end gap-2">
              <Button
                variant="outline"
                as-child
              >
                <Link :href="route('admin.store.coupon.index')">
                  {{ __("Cancel") }}
                </Link>
              </Button>
              <Button
                type="submit"
                :disabled="form.processing"
              >
                {{ __("Create Coupon") }}
              </Button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
