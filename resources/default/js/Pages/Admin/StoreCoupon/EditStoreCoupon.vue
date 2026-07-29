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
import { computed } from "vue";

const { __ } = useTranslations();

const props = defineProps({
    storeCoupon: Object,
    selectedPackages: Array,
    selectedCategories: Array,
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
        text: __("Edit Coupon"),
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

// A datetime-local input wants "YYYY-MM-DDTHH:MM"; the server sends an ISO timestamp.
function toLocalInput(timestamp) {
    if (! timestamp) {
        return null;
    }
    const date = new Date(timestamp);
    const pad = (value) => String(value).padStart(2, "0");
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
}

function exponentFor(code) {
    return props.currencies.find(item => item.code === code)?.exponent ?? props.baseCurrency.exponent ?? 2;
}

function toDecimal(minorAmount, exponent) {
    if (minorAmount === null || minorAmount === undefined) {
        return null;
    }
    return (Number(minorAmount) / (10 ** exponent)).toFixed(exponent);
}

const discountType = props.storeCoupon.discount_type?.value ?? props.storeCoupon.discount_type;
const storedCurrency = props.storeCoupon.currency_code ?? props.baseCurrency.code;

const form = useForm({
    code: props.storeCoupon.code,
    description: props.storeCoupon.description,
    discount_type: discountType,
    discount_percent: discountType === "percent"
        ? Number(props.storeCoupon.discount_value) / 100
        : 10,
    discount_amount: discountType === "fixed"
        ? toDecimal(props.storeCoupon.discount_value, exponentFor(storedCurrency))
        : null,
    currency_code: storedCurrency,
    min_basket: toDecimal(props.storeCoupon.min_basket_amount, props.baseCurrency.exponent),
    max_uses_total: props.storeCoupon.max_uses_total,
    max_uses_per_user: props.storeCoupon.max_uses_per_user,
    starts_at: toLocalInput(props.storeCoupon.starts_at),
    expires_at: toLocalInput(props.storeCoupon.expires_at),
    is_enabled: !! props.storeCoupon.is_enabled,
    packages: props.packages.filter(item => props.selectedPackages.includes(item.id)),
    categories: props.categories.filter(item => props.selectedCategories.includes(item.id)),
});

const isPercent = computed(() => form.discount_type === "percent");

// The exponent belongs to the currency the amount is typed in, never a constant: JPY has no minor
// unit and KWD has three digits, so a fixed 100 would misprice both.
const amountExponent = computed(() => exponentFor(form.currency_code));

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
        min_basket_amount: toMinorUnits(form.min_basket, props.baseCurrency.exponent),
        packages: (form.packages ?? []).map(item => item.id),
        categories: (form.categories ?? []).map(item => item.id),
    };
}

function updateCoupon() {
    form.transform(() => couponPayload(form)).put(route("admin.store.coupon.update", props.storeCoupon.id), {});
}
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Edit Store Coupon')" />

    <div class="px-10 py-8 mx-auto max-w-6xl text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
      </div>

      <div class="mt-6">
        <form @submit.prevent="updateCoupon">
          <!-- Discount Section -->
          <div class="shadow overflow-hidden rounded-lg mb-6 card-clip-safe">
            <div class="px-4 py-5 bg-card sm:p-6 border-b border-border">
              <div class="flex items-start justify-between mb-4">
                <h3 class="text-lg font-medium text-foreground">
                  {{ __("Discount") }}
                </h3>
                <p class="text-sm text-muted-foreground">
                  {{ __("Redeemed :count times", { count: storeCoupon.orders_count ?? storeCoupon.used_count }) }}
                </p>
              </div>
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
                      step="0.01"
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
                    step="0.01"
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
          <div class="shadow overflow-hidden rounded-lg mb-6 card-clip-safe">
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
                    v-if="form.errors.packages"
                    class="text-xs text-destructive mt-2"
                  >
                    {{ form.errors.packages }}
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
                    v-if="form.errors.categories"
                    class="text-xs text-destructive mt-2"
                  >
                    {{ form.errors.categories }}
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
                {{ __("Update Coupon") }}
              </Button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
