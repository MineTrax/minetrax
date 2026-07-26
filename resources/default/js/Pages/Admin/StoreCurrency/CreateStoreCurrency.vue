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

const { __ } = useTranslations();

const props = defineProps({
    roundingOptions: Array,
    countries: Array,
});

const breadcrumbItems = [
    {
        text: __("Admin"),
        current: false,
    },
    {
        text: __("Store Currencies"),
        url: route("admin.store.currency.index"),
        current: false,
    },
    {
        text: __("Create Currency"),
        current: true,
    }
];

const symbolPositionOptions = {
    prefix: __("Prefix"),
    suffix: __("Suffix"),
};

const roundingOptionsMap = props.roundingOptions.reduce((acc, option) => {
    acc[option.value] = option.label;
    return acc;
}, {});

const countriesOptions = props.countries.map(country => ({
    id: country.iso_code,
    name: country.name,
}));

const form = useForm({
    code: null,
    name: null,
    symbol: null,
    symbol_position: "suffix",
    exponent: 2,
    rate_to_base: 1,
    is_enabled: true,
    price_rounding: null,
    country_codes: [],
    sort_order: 0,
});

function createCurrency() {
    form.post(route("admin.store.currency.store"), {});
}
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Create Currency')" />

    <div class="px-10 py-8 mx-auto max-w-6xl text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
      </div>

      <div class="mt-6">
        <form @submit.prevent="createCurrency">
          <div class="shadow overflow-hidden rounded-lg">
            <div class="px-4 py-5 bg-card sm:p-6">
              <div class="grid grid-cols-6 gap-6">
                <div class="col-span-6 sm:col-span-2">
                  <XInput
                    id="code"
                    v-model="form.code"
                    :label="__('Currency Code')"
                    :help="__('ISO 4217 code (e.g., USD, EUR, GBP)')"
                    :error="form.errors.code"
                    type="text"
                    name="code"
                    maxlength="3"
                    required
                  />
                </div>

                <div class="col-span-6 sm:col-span-2">
                  <XInput
                    id="name"
                    v-model="form.name"
                    :label="__('Currency Name')"
                    :help="__('e.g., United States Dollar')"
                    :error="form.errors.name"
                    type="text"
                    name="name"
                    required
                  />
                </div>

                <div class="col-span-6 sm:col-span-2">
                  <XInput
                    id="symbol"
                    v-model="form.symbol"
                    :label="__('Symbol')"
                    :help="__('e.g., $, €, £')"
                    :error="form.errors.symbol"
                    type="text"
                    name="symbol"
                    required
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XSelect
                    id="symbol_position"
                    v-model="form.symbol_position"
                    :label="__('Symbol Position')"
                    :error="form.errors.symbol_position"
                    :select-list="symbolPositionOptions"
                    name="symbol_position"
                    required
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="exponent"
                    v-model.number="form.exponent"
                    :label="__('Exponent')"
                    :help="__('ISO-4217 minor units. Most currencies are 2; JPY is 0; KWD is 3. Getting this wrong mis-scales every price in this currency.')"
                    :error="form.errors.exponent"
                    type="number"
                    min="0"
                    max="4"
                    name="exponent"
                    required
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="rate_to_base"
                    v-model.number="form.rate_to_base"
                    :label="__('Rate to Base')"
                    :help="__('How many of this currency equal one unit of the base currency.')"
                    :error="form.errors.rate_to_base"
                    type="number"
                    step="any"
                    min="0"
                    name="rate_to_base"
                    required
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XSelect
                    id="price_rounding"
                    v-model="form.price_rounding"
                    :label="__('Price Rounding')"
                    :help="__('Applied only to prices converted from the base currency. Explicit per-package overrides are used exactly as entered.')"
                    :error="form.errors.price_rounding"
                    :select-list="roundingOptionsMap"
                    name="price_rounding"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="sort_order"
                    v-model.number="form.sort_order"
                    :label="__('Sort Order')"
                    :error="form.errors.sort_order"
                    type="number"
                    name="sort_order"
                  />
                </div>

                <div class="col-span-6">
                  <label
                    for="country_codes"
                    class="block text-sm font-medium text-foreground mb-2"
                  >{{ __("Countries") }}</label>
                  <Multiselect
                    id="country_codes"
                    v-model="form.country_codes"
                    class="block w-full border-input rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
                    :options="countriesOptions"
                    :multiple="true"
                    :close-on-select="false"
                    :clear-on-select="false"
                    :preserve-search="true"
                    track-by="id"
                    label="name"
                    :placeholder="__('Search countries')+'...'"
                  />
                  <p
                    v-if="form.errors.country_codes"
                    class="text-xs text-destructive mt-2"
                  >
                    {{ form.errors.country_codes }}
                  </p>
                </div>

                <div class="flex items-center col-span-6 sm:col-span-3">
                  <XSwitch
                    id="is_enabled"
                    v-model="form.is_enabled"
                    :label="__('Enabled')"
                    :help="__('Enable this currency for use.')"
                    name="is_enabled"
                    :error="form.errors.is_enabled"
                  />
                </div>
              </div>
            </div>
            <div class="px-4 py-3 bg-card border-t border-border sm:px-6 flex justify-end gap-2">
              <Button
                variant="outline"
                as-child
              >
                <Link :href="route('admin.store.currency.index')">
                  {{ __("Cancel") }}
                </Link>
              </Button>
              <Button
                type="submit"
                :disabled="form.processing"
              >
                <svg
                  v-if="form.processing"
                  class="animate-spin -ml-1 mr-2 h-4 w-4"
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
                {{ __("Create Currency") }}
              </Button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
