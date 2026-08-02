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
    storeSale: Object,
    selectedPackages: Array,
    selectedCategories: Array,
    packages: Array,
    categories: Array,
    baseCurrency: Object,
});

const breadcrumbItems = [
    {
        text: __("Admin"),
        current: false,
    },
    {
        text: __("Store Sales"),
        url: route("admin.store.sale.index"),
        current: false,
    },
    {
        text: __("Edit Sale"),
        current: true,
    }
];

const discountTypeOptions = {
    percent: __("Percentage Off"),
    fixed: __("Fixed Amount Off"),
};

// The factor is 10^exponent, never a literal 100: JPY has no minor unit and KWD has three digits.
const minorUnitFactor = 10 ** (props.baseCurrency?.exponent ?? 2);

// A datetime-local input wants "YYYY-MM-DDTHH:MM"; the server sends an ISO timestamp.
function toLocalInput(timestamp) {
    if (! timestamp) {
        return null;
    }
    const date = new Date(timestamp);
    const pad = (value) => String(value).padStart(2, "0");
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
}

const discountType = props.storeSale.discount_type?.value ?? props.storeSale.discount_type;

// The step belongs to the currency the amount is typed in, never a hardcoded 0.01: JPY has no minor
// unit and KWD has three, so a fixed step either invites an amount the server refuses or rejects a
// legitimate one.
function stepFor(exponent) {
    return exponent === 0 ? "1" : (1 / (10 ** exponent)).toFixed(exponent);
}

const baseStep = computed(() => stepFor(props.baseCurrency?.exponent ?? 2));

const form = useForm({
    name: props.storeSale.name,
    discount_type: discountType,
    discount_percent: discountType === "percent"
        ? Number(props.storeSale.discount_value) / 100
        : 10,
    discount_amount: discountType === "fixed"
        ? (Number(props.storeSale.discount_value) / minorUnitFactor).toFixed(props.baseCurrency.exponent)
        : null,
    starts_at: toLocalInput(props.storeSale.starts_at),
    ends_at: toLocalInput(props.storeSale.ends_at),
    is_enabled: !! props.storeSale.is_enabled,
    packages: props.packages.filter(item => props.selectedPackages.includes(item.id)),
    categories: props.categories.filter(item => props.selectedCategories.includes(item.id)),
});

const isPercent = computed(() => form.discount_type === "percent");

function toMinorUnits(decimalAmount) {
    if (decimalAmount === null || decimalAmount === "" || isNaN(parseFloat(decimalAmount))) {
        return null;
    }
    return Math.round(parseFloat(decimalAmount) * minorUnitFactor);
}

function toBasisPoints(percent) {
    if (percent === null || percent === "" || isNaN(parseFloat(percent))) {
        return null;
    }
    return Math.round(parseFloat(percent) * 100);
}

function salePayload(form) {
    return {
        ...form.data(),
        discount_value: isPercent.value
            ? toBasisPoints(form.discount_percent)
            : toMinorUnits(form.discount_amount),
        packages: (form.packages ?? []).map(item => item.id),
        categories: (form.categories ?? []).map(item => item.id),
    };
}

function updateSale() {
    form.transform(() => salePayload(form)).put(route("admin.store.sale.update", props.storeSale.id), {});
}
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Edit Store Sale')" />

    <div class="px-10 py-8 mx-auto max-w-6xl text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
      </div>

      <div class="mt-6">
        <form @submit.prevent="updateSale">
          <div class="shadow overflow-hidden rounded-lg mb-6 card-clip-safe">
            <div class="px-4 py-5 bg-card sm:p-6 border-b border-border">
              <h3 class="text-lg font-medium text-foreground mb-4">
                {{ __("Discount") }}
              </h3>
              <div class="grid grid-cols-6 gap-6">
                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="name"
                    v-model="form.name"
                    :label="__('Sale Name')"
                    :help="__('Shown to the customer as the badge on discounted packages.')"
                    :error="form.errors.name"
                    type="text"
                    name="name"
                    required
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
                    :help="__('Applied after the package\'s own discount.')"
                    :error="form.errors.discount_value"
                    type="number"
                    step="0.01"
                    name="discount_percent"
                    min="0.01"
                    max="100"
                    required
                  />
                </div>

                <div
                  v-else
                  class="col-span-6 sm:col-span-2"
                >
                  <XInput
                    id="discount_amount"
                    v-model="form.discount_amount"
                    :label="__('Amount Off')"
                    :help="__('In :currency, converted for customers paying in another currency.', { currency: baseCurrency.code })"
                    :error="form.errors.discount_value"
                    type="number"
                    :step="baseStep"
                    name="discount_amount"
                    min="0"
                    required
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="starts_at"
                    v-model="form.starts_at"
                    :label="__('Starts At')"
                    :help="__('Prices are untouched before this moment. Leave empty to start immediately.')"
                    :error="form.errors.starts_at"
                    type="datetime-local"
                    name="starts_at"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="ends_at"
                    v-model="form.ends_at"
                    :label="__('Ends At')"
                    :help="__('Prices return to normal after this moment. Leave empty to run until disabled.')"
                    :error="form.errors.ends_at"
                    type="datetime-local"
                    name="ends_at"
                  />
                </div>

                <div class="flex items-center col-span-6 sm:col-span-3">
                  <XSwitch
                    id="is_enabled"
                    v-model="form.is_enabled"
                    :label="__('Enabled')"
                    :help="__('A disabled sale discounts nothing even inside its window.')"
                    name="is_enabled"
                    :error="form.errors.is_enabled"
                  />
                </div>
              </div>
            </div>
          </div>

          <div class="shadow rounded-lg mb-6 card-clip-safe">
            <div class="px-4 py-5 bg-card sm:p-6 border-b border-border">
              <h3 class="text-lg font-medium text-foreground mb-1">
                {{ __("Applies To") }}
              </h3>
              <p class="text-sm text-muted-foreground mb-4">
                {{ __("Leave both empty and the sale discounts the whole store. Otherwise only the packages and categories picked here go on sale.") }}
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
                <Link :href="route('admin.store.sale.index')">
                  {{ __("Cancel") }}
                </Link>
              </Button>
              <Button
                type="submit"
                :disabled="form.processing"
              >
                {{ __("Update Sale") }}
              </Button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
