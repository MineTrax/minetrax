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
        text: __("Create Sale"),
        current: true,
    }
];

const discountTypeOptions = {
    percent: __("Percentage Off"),
    fixed: __("Fixed Amount Off"),
};

const form = useForm({
    name: null,
    discount_type: "percent",
    // Entered as a human figure and converted on submit: percentages are stored as basis points and
    // amounts as minor units, neither of which anyone wants to type.
    discount_percent: 10,
    discount_amount: null,
    starts_at: null,
    ends_at: null,
    is_enabled: true,
    packages: [],
    categories: [],
});

const isPercent = computed(() => form.discount_type === "percent");

// The factor is 10^exponent, never a literal 100: JPY has no minor unit and KWD has three digits.
const minorUnitFactor = 10 ** (props.baseCurrency?.exponent ?? 2);

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
        // Multiselect hands back whole objects; the API takes ids.
        packages: (form.packages ?? []).map(item => item.id),
        categories: (form.categories ?? []).map(item => item.id),
    };
}

function createSale() {
    form.transform(() => salePayload(form)).post(route("admin.store.sale.store"), {});
}
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Create Store Sale')" />

    <div class="px-10 py-8 mx-auto max-w-6xl text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
      </div>

      <div class="mt-6">
        <form @submit.prevent="createSale">
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
                    step="0.01"
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

          <div class="shadow overflow-hidden rounded-lg mb-6 card-clip-safe">
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
                <Link :href="route('admin.store.sale.index')">
                  {{ __("Cancel") }}
                </Link>
              </Button>
              <Button
                type="submit"
                :disabled="form.processing"
              >
                {{ __("Create Sale") }}
              </Button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
