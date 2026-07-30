<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { useTranslations } from "@/Composables/useTranslations";
import AppBreadcrumb from "@/Shared/AppBreadcrumb.vue";
import { Button } from "@/Components/ui/button";
import { Link, useForm } from "@inertiajs/vue3";
import XInput from "@/Components/Form/XInput.vue";
import XSelect from "@/Components/Form/XSelect.vue";
import { computed } from "vue";

const { __ } = useTranslations();

const props = defineProps({
    currencies: Array,
    baseCurrency: Object,
});

const breadcrumbItems = [
    {
        text: __("Admin"),
        current: false,
    },
    {
        text: __("Store Gift Cards"),
        url: route("admin.store.gift-card.index"),
        current: false,
    },
    {
        text: __("Issue Gift Card"),
        current: true,
    }
];

const currencyOptions = props.currencies.reduce((acc, currency) => {
    return { ...acc, [currency.code]: `${currency.code} (${currency.symbol})` };
}, {});

const form = useForm({
    // Typed as a human amount and converted on submit; the server stores minor units.
    amount: null,
    currency_code: props.baseCurrency.code,
    username: null,
    expires_at: null,
    note: null,
});

// The exponent belongs to the currency the amount is typed in, never a constant: JPY has no minor
// unit and KWD has three digits, so a fixed 100 would misprice both.
const amountExponent = computed(() => {
    const currency = props.currencies.find(item => item.code === form.currency_code);

    return currency?.exponent ?? props.baseCurrency.exponent ?? 2;
});

const amountStep = computed(() => amountExponent.value === 0 ? "1" : (1 / (10 ** amountExponent.value)).toFixed(amountExponent.value));

function toMinorUnits(decimalAmount, exponent) {
    if (decimalAmount === null || decimalAmount === "" || isNaN(parseFloat(decimalAmount))) {
        return null;
    }
    return Math.round(parseFloat(decimalAmount) * (10 ** exponent));
}

function createGiftCard() {
    form.transform(() => ({
        ...form.data(),
        balance: toMinorUnits(form.amount, amountExponent.value),
    })).post(route("admin.store.gift-card.store"), {});
}
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Issue Store Gift Card')" />

    <div class="px-10 py-8 mx-auto max-w-6xl text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
      </div>

      <div class="mt-6">
        <form @submit.prevent="createGiftCard">
          <div class="shadow overflow-hidden rounded-lg mb-6 card-clip-safe">
            <div class="px-4 py-5 bg-card sm:p-6 border-b border-border">
              <h3 class="text-lg font-medium text-foreground mb-1">
                {{ __("Credit") }}
              </h3>
              <p class="text-sm text-muted-foreground mb-4">
                {{ __("The code is generated for you and shown once the card is saved. Redeeming against an order in another currency converts at that order's rate.") }}
              </p>
              <div class="grid grid-cols-6 gap-6">
                <div class="col-span-6 sm:col-span-2">
                  <XInput
                    id="amount"
                    v-model="form.amount"
                    :label="__('Amount')"
                    :help="__('Decimal amount, e.g. 25.00')"
                    :error="form.errors.balance"
                    type="number"
                    :step="amountStep"
                    name="amount"
                    min="0"
                    required
                  />
                </div>

                <div class="col-span-6 sm:col-span-2">
                  <XSelect
                    id="currency_code"
                    v-model="form.currency_code"
                    name="currency_code"
                    :label="__('Currency')"
                    :help="__('Frozen once the card exists, because it may be part-spent at a snapshot rate.')"
                    :select-list="currencyOptions"
                    :error="form.errors.currency_code"
                    :disable-null="true"
                  />
                </div>

                <div class="col-span-6 sm:col-span-2">
                  <XInput
                    id="expires_at"
                    v-model="form.expires_at"
                    :label="__('Expires At')"
                    :help="__('Leave empty for no expiry.')"
                    :error="form.errors.expires_at"
                    type="datetime-local"
                    name="expires_at"
                  />
                </div>
              </div>
            </div>
          </div>

          <div class="shadow overflow-hidden rounded-lg mb-6 card-clip-safe">
            <div class="px-4 py-5 bg-card sm:p-6 border-b border-border">
              <h3 class="text-lg font-medium text-foreground mb-4">
                {{ __("Recipient") }}
              </h3>
              <div class="grid grid-cols-6 gap-6">
                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="username"
                    v-model="form.username"
                    :label="__('Issued To')"
                    :help="__('Optional account username. Leave empty and anybody holding the code can spend it.')"
                    :error="form.errors.username"
                    type="text"
                    name="username"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="note"
                    v-model="form.note"
                    :label="__('Ledger Note')"
                    :help="__('Why this card exists. Recorded against the issue entry and never shown to the customer.')"
                    :error="form.errors.note"
                    type="text"
                    name="note"
                  />
                </div>
              </div>
            </div>
            <div class="px-4 py-3 bg-card border-t border-border sm:px-6 flex justify-end gap-2">
              <Button
                variant="outline"
                as-child
              >
                <Link :href="route('admin.store.gift-card.index')">
                  {{ __("Cancel") }}
                </Link>
              </Button>
              <Button
                type="submit"
                :disabled="form.processing"
              >
                {{ __("Issue Gift Card") }}
              </Button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
