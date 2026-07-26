<script setup>
import { computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import AppBreadcrumb from "@/Shared/AppBreadcrumb.vue";
import { Button } from "@/Components/ui/button";
import XInput from "@/Components/Form/XInput.vue";
import XSelect from "@/Components/Form/XSelect.vue";
import XSwitch from "@/Components/Form/XSwitch.vue";
import XTextarea from "@/Components/Form/XTextarea.vue";
import XCheckbox from "@/Components/Form/XCheckbox.vue";
import { useTranslations } from "@/Composables/useTranslations";

const { __ } = useTranslations();

const props = defineProps({
    settings: Object,
    gateways: Array,
    currencies: Array,
    hasOrders: Boolean,
    webhookUrlTemplate: String,
});

const breadcrumbItems = [
    { text: __("Admin"), current: false },
    { text: __("Settings"), current: false },
    { text: __("Store Settings"), current: true },
];

// The form binds to the masked copy the server sent. A secret left untouched goes back as the
// mask and the server keeps what it already had, so a real credential never reaches the browser.
const form = useForm({
    store_name: props.settings.store_name,
    store_description: props.settings.store_description,

    base_currency: props.settings.base_currency,
    currency_rate_source: props.settings.currency_rate_source,

    tax_mode: props.settings.tax_mode,
    tax_rate_bp: props.settings.tax_rate_bp,
    tax_label: props.settings.tax_label,

    enable_guest_checkout: props.settings.enable_guest_checkout,
    require_email_on_guest_checkout: props.settings.require_email_on_guest_checkout,
    mojang_username_verification: props.settings.mojang_username_verification,
    terms_text: props.settings.terms_text,

    enabled_gateways: [...(props.settings.enabled_gateways ?? [])],
    gateway_credentials: Object.fromEntries(
        props.gateways.map((gateway) => [gateway.key, { ...gateway.credentials }]),
    ),

    show_recent_purchases: props.settings.show_recent_purchases,
    hide_buyer_identity: props.settings.hide_buyer_identity,
    notify_staff_on_purchase: props.settings.notify_staff_on_purchase,
});

const currencyList = computed(() =>
    Object.fromEntries(props.currencies.map((c) => [c.code, `${c.code} — ${c.name}`])),
);

const rateSourceList = {
    manual: __("Manual — I will set rates myself"),
    api: __("Automatic — refresh daily from an exchange rate API"),
};

const taxModeList = {
    none: __("No tax"),
    inclusive: __("Inclusive — tax is already in the listed price"),
    exclusive: __("Exclusive — tax is added at checkout"),
};

const isGatewayOn = (key) => form.enabled_gateways.includes(key);

const toggleGateway = (key, on) => {
    if (on && !isGatewayOn(key)) {
        form.enabled_gateways.push(key);
    } else if (!on) {
        form.enabled_gateways = form.enabled_gateways.filter((k) => k !== key);
    }
};

const webhookUrlFor = (key) => props.webhookUrlTemplate.replace("__GATEWAY__", key);

const copyWebhookUrl = (key) => navigator.clipboard?.writeText(webhookUrlFor(key));

const saveSetting = () => {
    form.post(route("admin.setting.store.update"), { preserveScroll: true });
};
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Store Settings')" />

    <div class="px-10 py-8 mx-auto max-w-6xl text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
      </div>

      <form
        autocomplete="off"
        @submit.prevent="saveSetting"
      >
        <div class="shadow rounded-lg">
          <div class="px-4 py-5 bg-card sm:p-6 space-y-8">
            <!-- Storefront -->
            <fieldset>
              <legend class="text-sm font-medium text-foreground mb-4">
                {{ __("Storefront") }}
              </legend>

              <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <XInput
                  id="store_name"
                  v-model="form.store_name"
                  :label="__('Store Name')"
                  :error="form.errors.store_name"
                  type="text"
                  name="store_name"
                />
                <div class="sm:col-span-2">
                  <XInput
                    id="store_description"
                    v-model="form.store_description"
                    :label="__('Store Description')"
                    :error="form.errors.store_description"
                    :placeholder="__('Shown at the top of the storefront')"
                    type="text"
                    name="store_description"
                  />
                </div>
              </div>
            </fieldset>

            <!-- Currency -->
            <fieldset>
              <legend class="text-sm font-medium text-foreground mb-4">
                {{ __("Currency") }}
              </legend>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <XSelect
                  id="base_currency"
                  v-model="form.base_currency"
                  :label="__('Base Currency')"
                  :select-list="currencyList"
                  :error="form.errors.base_currency"
                  :disabled="hasOrders"
                  :help="hasOrders
                    ? __('Locked: orders already exist and their revenue was recorded against this currency.')
                    : __('The currency all reporting is converted back to.')"
                  name="base_currency"
                />
                <XSelect
                  id="currency_rate_source"
                  v-model="form.currency_rate_source"
                  :label="__('Exchange Rate Source')"
                  :select-list="rateSourceList"
                  :error="form.errors.currency_rate_source"
                  name="currency_rate_source"
                />
              </div>
            </fieldset>

            <!-- Tax -->
            <fieldset>
              <legend class="text-sm font-medium text-foreground mb-4">
                {{ __("Tax") }}
              </legend>

              <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <XSelect
                  id="tax_mode"
                  v-model="form.tax_mode"
                  :label="__('Tax Mode')"
                  :select-list="taxModeList"
                  :error="form.errors.tax_mode"
                  name="tax_mode"
                />
                <XInput
                  id="tax_rate_bp"
                  v-model="form.tax_rate_bp"
                  :label="__('Tax Rate (basis points)')"
                  :error="form.errors.tax_rate_bp"
                  :help="__('2000 = 20%. Basis points avoid rounding drift on percentages.')"
                  type="number"
                  name="tax_rate_bp"
                />
                <XInput
                  id="tax_label"
                  v-model="form.tax_label"
                  :label="__('Tax Label')"
                  :error="form.errors.tax_label"
                  :placeholder="__('Eg: VAT, GST, Sales Tax')"
                  type="text"
                  name="tax_label"
                />
              </div>
            </fieldset>

            <!-- Checkout -->
            <fieldset>
              <legend class="text-sm font-medium text-foreground mb-4">
                {{ __("Checkout") }}
              </legend>

              <div class="space-y-4">
                <XSwitch
                  id="enable_guest_checkout"
                  v-model="form.enable_guest_checkout"
                  :label="__('Allow guest checkout')"
                  :help="__('Buyers can purchase without a website account.')"
                  :error="form.errors.enable_guest_checkout"
                  name="enable_guest_checkout"
                />
                <XSwitch
                  id="require_email_on_guest_checkout"
                  v-model="form.require_email_on_guest_checkout"
                  :label="__('Require an email address from guests')"
                  :help="__('Needed to send a receipt or contact the buyer about a failed delivery.')"
                  :error="form.errors.require_email_on_guest_checkout"
                  name="require_email_on_guest_checkout"
                />
                <XSwitch
                  id="mojang_username_verification"
                  v-model="form.mojang_username_verification"
                  :label="__('Verify usernames against Mojang')"
                  :help="__('Turn this off for offline-mode (cracked) servers; the offline UUID is derived instead.')"
                  :error="form.errors.mojang_username_verification"
                  name="mojang_username_verification"
                />

                <XTextarea
                  id="terms_text"
                  v-model="form.terms_text"
                  :auto-resize="true"
                  :label="__('Terms of Service')"
                  :placeholder="__('Shown at checkout. Buyers must accept before paying.')"
                  :error="form.errors.terms_text"
                  name="terms_text"
                  :rows="4"
                />
              </div>
            </fieldset>

            <!-- Payment gateways -->
            <fieldset>
              <legend class="text-sm font-medium text-foreground mb-1">
                {{ __("Payment Gateways") }}
              </legend>
              <p class="text-xs text-muted-foreground mb-4">
                {{ __("A gateway is only offered at checkout once it is switched on and every required credential is filled in.") }}
              </p>

              <div class="space-y-4">
                <div
                  v-for="gateway in gateways"
                  :key="gateway.key"
                  class="border border-border rounded-lg p-4"
                >
                  <div class="flex items-start justify-between gap-4">
                    <div>
                      <div class="flex items-center gap-2">
                        <span class="font-medium text-sm">{{ gateway.label }}</span>
                        <span
                          v-if="gateway.is_configured"
                          class="text-xs px-2 py-0.5 rounded-full bg-green-500/10 text-green-600 dark:text-green-400"
                        >{{ __("Ready") }}</span>
                        <span
                          v-else-if="isGatewayOn(gateway.key)"
                          class="text-xs px-2 py-0.5 rounded-full bg-orange-500/10 text-orange-600 dark:text-orange-400"
                        >{{ __("Missing credentials") }}</span>
                      </div>
                      <p
                        v-if="gateway.description"
                        class="text-xs text-muted-foreground mt-1"
                      >
                        {{ gateway.description }}
                      </p>
                    </div>

                    <XSwitch
                      :id="`gateway_${gateway.key}`"
                      :model-value="isGatewayOn(gateway.key)"
                      :name="`gateway_${gateway.key}`"
                      @update:model-value="toggleGateway(gateway.key, $event)"
                    />
                  </div>

                  <div
                    v-if="isGatewayOn(gateway.key) && gateway.schema.length"
                    class="mt-4 space-y-4 border-t border-border pt-4"
                  >
                    <template
                      v-for="field in gateway.schema"
                      :key="field.key"
                    >
                      <XTextarea
                        v-if="field.type === 'textarea'"
                        :id="`${gateway.key}_${field.key}`"
                        v-model="form.gateway_credentials[gateway.key][field.key]"
                        :label="field.label"
                        :help="field.help"
                        :auto-resize="true"
                        :rows="3"
                        :name="`${gateway.key}_${field.key}`"
                      />
                      <XInput
                        v-else
                        :id="`${gateway.key}_${field.key}`"
                        v-model="form.gateway_credentials[gateway.key][field.key]"
                        :label="field.label"
                        :help="field.help"
                        :required="field.required"
                        type="text"
                        autocomplete="off"
                        :name="`${gateway.key}_${field.key}`"
                      />
                    </template>

                    <div class="text-xs text-muted-foreground">
                      {{ __("Webhook URL") }}:
                      <code class="px-1 py-0.5 rounded bg-muted break-all">{{ webhookUrlFor(gateway.key) }}</code>
                      <button
                        type="button"
                        class="ml-2 underline hover:text-foreground cursor-pointer"
                        @click="copyWebhookUrl(gateway.key)"
                      >
                        {{ __("Copy") }}
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </fieldset>

            <!-- Community -->
            <fieldset>
              <legend class="text-sm font-medium text-foreground mb-4">
                {{ __("Community & Notifications") }}
              </legend>

              <div class="space-y-3">
                <XCheckbox
                  id="show_recent_purchases"
                  v-model="form.show_recent_purchases"
                  :label="__('Show recent purchases on the site')"
                  name="show_recent_purchases"
                />
                <XCheckbox
                  id="hide_buyer_identity"
                  v-model="form.hide_buyer_identity"
                  :label="__('Hide buyer names in public purchase lists')"
                  name="hide_buyer_identity"
                />
                <XCheckbox
                  id="notify_staff_on_purchase"
                  v-model="form.notify_staff_on_purchase"
                  :label="__('Notify staff on every purchase')"
                  name="notify_staff_on_purchase"
                />
              </div>
            </fieldset>
          </div>

          <div class="px-4 py-3 bg-card border-t border-border sm:px-6 flex justify-end gap-2">
            <Button
              type="submit"
              :disabled="form.processing"
            >
              {{ __("Save Store Settings") }}
            </Button>
          </div>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>
