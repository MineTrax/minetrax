<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import AppBreadcrumb from "@/Shared/AppBreadcrumb.vue";
import XInput from "@/Components/Form/XInput.vue";
import XSelect from "@/Components/Form/XSelect.vue";
import XSwitch from "@/Components/Form/XSwitch.vue";
import { Button } from "@/Components/ui/button";
import { useTranslations } from "@/Composables/useTranslations";
import { useForm } from "@inertiajs/vue3";
import { computed } from "vue";

const { __ } = useTranslations();

const props = defineProps({
    countries: { type: Array, required: true },
});

/**
 * The country options, with Global first.
 *
 * Keys are strings because a numeric id never preselects, and "global" rather than "" because
 * SelectItem refuses an empty value — the empty string is reserved for clearing the select, and
 * passing it throws, which silently renders the whole list blank.
 */
const GLOBAL = "global";

const countryList = computed(() => {
    const list = { [GLOBAL]: __("Global (everyone without a rule of their own)") };

    props.countries.forEach((country) => {
        list[String(country.id)] = country.name;
    });

    return list;
});

// Percentages are typed as percentages and sent as basis points, so nobody has to think in 2100.
const toPayload = (data) => ({
    ...data,
    country_id: data.country_id === GLOBAL ? null : Number(data.country_id),
    rate_bp: Math.round(Number(data.rate_percent || 0) * 100),
});

const breadcrumbItems = [
    { text: __("Admin"), current: false },
    { text: __("Store Taxes"), url: route("admin.store.tax.index"), current: false },
    { text: __("New Tax"), current: true },
];

const form = useForm({
    name: "",
    country_id: GLOBAL,
    rate_percent: "",
    is_inclusive: false,
    is_enabled: true,
});

const submit = () => {
    form.transform(toPayload).post(route("admin.store.tax.store"));
};
</script>

<template>
  <AdminLayout>
    <app-head :title="__('New Tax')" />

    <div class="px-10 py-8 mx-auto max-w-4xl text-foreground">
      <AppBreadcrumb
        class="mt-0 mb-4"
        breadcrumb-class="max-w-none px-0 md:px-0"
        :items="breadcrumbItems"
      />

      <div class="bg-card rounded-lg shadow p-6 space-y-4">
        <XInput
          id="name"
          v-model="form.name"
          :label="__('Name for tax')"
          :help="__('What the buyer sees on the receipt. Eg: VAT, GST, Sales Tax.')"
          :error="form.errors.name"
          placeholder="VAT"
          type="text"
          name="name"
          :required="true"
        />

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <XSelect
            id="country_id"
            v-model="form.country_id"
            :label="__('Select a country')"
            :help="__('Charged to buyers geolocated to this country. Pick Global to cover everyone no other rule matches.')"
            :select-list="countryList"
            :error="form.errors.country_id"
            name="country_id"
            :disable-null="true"
          />

          <XInput
            id="rate_bp"
            v-model="form.rate_percent"
            :label="__('Percentage')"
            :help="__('Eg: 21 for 21%.')"
            :error="form.errors.rate_bp"
            placeholder="10"
            type="number"
            step="0.01"
            min="0"
            max="100"
            name="rate_bp"
            :required="true"
          />
        </div>

        <XSwitch
          id="is_inclusive"
          v-model="form.is_inclusive"
          :label="__('Include the tax in the listed price?')"
          :help="__('On: the advertised price already contains the tax and the total does not change. Off: the tax is added at checkout.')"
          name="is_inclusive"
        />

        <XSwitch
          id="is_enabled"
          v-model="form.is_enabled"
          :label="__('Enabled')"
          :help="__('A disabled rule charges nobody, but is kept so it can be switched back on.')"
          name="is_enabled"
        />

        <div class="flex justify-end">
          <Button
            :disabled="form.processing"
            @click="submit"
          >
            {{ __("Create a Tax") }}
          </Button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
