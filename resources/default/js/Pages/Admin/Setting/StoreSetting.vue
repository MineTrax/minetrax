<script setup>
import { computed } from "vue";
import { Link, useForm } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import AppBreadcrumb from "@/Shared/AppBreadcrumb.vue";
import { Button } from "@/Components/ui/button";
import XInput from "@/Components/Form/XInput.vue";
import XSelect from "@/Components/Form/XSelect.vue";
import XSwitch from "@/Components/Form/XSwitch.vue";
import XTextarea from "@/Components/Form/XTextarea.vue";
import { useTranslations } from "@/Composables/useTranslations";

const { __ } = useTranslations();

const props = defineProps({
    settings: Object,
    currencies: Array,
    hasOrders: Boolean,
});

const breadcrumbItems = [
    { text: __("Admin"), current: false },
    { text: __("Settings"), current: false },
    { text: __("Store Settings"), current: true },
];

// How many digits the goal amount takes belongs to the base currency, never a constant: JPY has no
// minor unit and KWD has three, so a hardcoded 100 would misstate the target in both.
const baseExponent = props.currencies.find((c) => c.code === props.settings.base_currency)?.exponent ?? 2;
const goalStep = baseExponent === 0 ? "1" : (1 / (10 ** baseExponent)).toFixed(baseExponent);

function toDecimal(minorAmount) {
    if (minorAmount === null || minorAmount === undefined) {
        return null;
    }
    return (Number(minorAmount) / (10 ** baseExponent)).toFixed(baseExponent);
}

function toMinorUnits(decimalAmount) {
    if (decimalAmount === null || decimalAmount === "" || isNaN(parseFloat(decimalAmount))) {
        return 0;
    }
    return Math.round(parseFloat(decimalAmount) * (10 ** baseExponent));
}

const form = useForm({
    store_name: props.settings.store_name,
    store_description: props.settings.store_description,

    base_currency: props.settings.base_currency,
    currency_rate_source: props.settings.currency_rate_source,


    enable_guest_checkout: props.settings.enable_guest_checkout,
    require_email_on_guest_checkout: props.settings.require_email_on_guest_checkout,
    mojang_username_verification: props.settings.mojang_username_verification,
    terms_text: props.settings.terms_text,

    show_recent_purchases: props.settings.show_recent_purchases,
    show_purchase_goal: props.settings.show_purchase_goal,
    show_top_donor: props.settings.show_top_donor,
    // Typed as a human amount and converted on submit; the setting itself is minor units.
    purchase_goal: toDecimal(props.settings.purchase_goal_amount),
    hide_buyer_identity: props.settings.hide_buyer_identity,
    notify_staff_on_purchase: props.settings.notify_staff_on_purchase,
    discord_purchase_webhook_url: props.settings.discord_purchase_webhook_url,
    auto_ban_on_chargeback: props.settings.auto_ban_on_chargeback,
});

const currencyList = computed(() =>
    Object.fromEntries(props.currencies.map((c) => [c.code, `${c.code} — ${c.name}`])),
);

const rateSourceList = {
    manual: __("Manual — I will set rates myself"),
    api: __("Automatic — refresh daily from an exchange rate API"),
};

const saveSetting = () => {
    form.transform((data) => ({
        ...data,
        purchase_goal_amount: toMinorUnits(data.purchase_goal),
    })).post(route("admin.setting.store.update"), { preserveScroll: true });
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

              <p
                v-if="!currencies.length"
                class="text-sm text-orange-500 mb-4"
              >
                {{ __("No currencies exist yet, so there is nothing to choose here.") }}
                <Link
                  class="underline hover:text-foreground"
                  :href="route('admin.store.currency.index')"
                >
                  {{ __("Add one first") }}
                </Link>.
              </p>

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
                  :disable-null="true"
                />
                <XSelect
                  id="currency_rate_source"
                  v-model="form.currency_rate_source"
                  :label="__('Exchange Rate Source')"
                  :select-list="rateSourceList"
                  :error="form.errors.currency_rate_source"
                  name="currency_rate_source"
                  :disable-null="true"
                />
              </div>
            </fieldset>

            <!-- Tax -->

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

            <!-- Community -->
            <fieldset>
              <legend class="text-sm font-medium text-foreground mb-4">
                {{ __("Community & Notifications") }}
              </legend>

              <div class="space-y-4">
                <XSwitch
                  id="show_recent_purchases"
                  v-model="form.show_recent_purchases"
                  :label="__('Show recent purchases on the site')"
                  name="show_recent_purchases"
                />
                <XSwitch
                  id="show_top_donor"
                  v-model="form.show_top_donor"
                  :label="__('Show this month\'s top supporter')"
                  :help="__('Whoever has spent the most this calendar month, counted per player rather than per account.')"
                  name="show_top_donor"
                />
                <XSwitch
                  id="hide_buyer_identity"
                  v-model="form.hide_buyer_identity"
                  :label="__('Hide buyer names in public purchase lists')"
                  :help="__('Replaces every public name with Anonymous, guests\' Minecraft usernames included.')"
                  name="hide_buyer_identity"
                />
                <XSwitch
                  id="show_purchase_goal"
                  v-model="form.show_purchase_goal"
                  :label="__('Show a monthly goal bar')"
                  name="show_purchase_goal"
                />
                <div
                  v-if="form.show_purchase_goal"
                  class="sm:w-1/2"
                >
                  <XInput
                    id="purchase_goal"
                    v-model="form.purchase_goal"
                    :label="__('Monthly Goal')"
                    :help="__('In :currency. Leave at zero and the bar stays hidden — there would be nothing to measure against.', { currency: settings.base_currency })"
                    :error="form.errors.purchase_goal_amount"
                    type="number"
                    :step="goalStep"
                    name="purchase_goal"
                    min="0"
                  />
                </div>
                <XSwitch
                  id="notify_staff_on_purchase"
                  v-model="form.notify_staff_on_purchase"
                  :label="__('Notify staff on every purchase')"
                  name="notify_staff_on_purchase"
                />
                <div>
                  <XInput
                    id="discord_purchase_webhook_url"
                    v-model="form.discord_purchase_webhook_url"
                    :label="__('Discord Purchase Announcements')"
                    :help="__('An incoming webhook URL from the Discord channel you want sales posted in. Leave empty to announce nothing. Buyer names follow the setting above.')"
                    :error="form.errors.discord_purchase_webhook_url"
                    type="text"
                    name="discord_purchase_webhook_url"
                    placeholder="https://discord.com/api/webhooks/..."
                  />
                </div>
                <XSwitch
                  id="auto_ban_on_chargeback"
                  v-model="form.auto_ban_on_chargeback"
                  :label="__('Ban the buyer automatically when a chargeback lands')"
                  :help="__('Blocks their account, player, email and IP address from checking out again. Leave off if you would rather review each dispute — an IP is shared more often than people expect.')"
                  name="auto_ban_on_chargeback"
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
