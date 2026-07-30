<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { useTranslations } from "@/Composables/useTranslations";
import AppBreadcrumb from "@/Shared/AppBreadcrumb.vue";
import CommonStatusBadge from "@/Shared/CommonStatusBadge.vue";
import { Button } from "@/Components/ui/button";
import XInput from "@/Components/Form/XInput.vue";
import { Link, useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";

const { __ } = useTranslations();

const props = defineProps({
    card: Object,
    exponent: Number,
    cardPermissions: Object,
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
        text: props.card.code,
        current: true,
    }
];

const adjusting = ref(false);

const adjustForm = useForm({
    // Signed: a positive figure tops the card up, a negative one takes credit off.
    amount: null,
    note: null,
});

const amountStep = computed(() => props.exponent === 0 ? "1" : (1 / (10 ** props.exponent)).toFixed(props.exponent));

function toMinorUnits(decimalAmount) {
    if (decimalAmount === null || decimalAmount === "" || isNaN(parseFloat(decimalAmount))) {
        return null;
    }
    return Math.round(parseFloat(decimalAmount) * (10 ** props.exponent));
}

function submitAdjustment() {
    adjustForm
        .transform(data => ({ ...data, amount: toMinorUnits(data.amount) }))
        .post(route("admin.store.gift-card.adjust", props.card.id), {
            preserveScroll: true,
            onSuccess: () => {
                adjusting.value = false;
                adjustForm.reset();
            },
        });
}

const isSpendable = computed(() => {
    if (! props.card.is_enabled || props.card.balance <= 0) {
        return false;
    }

    return ! props.card.expires_at || new Date(props.card.expires_at) > new Date();
});

// The four kinds a movement can be, worded as what happened rather than as the enum name.
const typeLabels = {
    issue: __("Issued"),
    redeem: __("Redeemed"),
    reversal: __("Returned"),
    adjustment: __("Adjusted"),
};

function typeLabel(transaction) {
    const type = transaction.type?.value ?? transaction.type;

    return typeLabels[type] ?? type;
}
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Gift Card :code', { code: card.code })" />

    <div class="px-10 py-8 mx-auto max-w-5xl text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
        <div class="flex gap-2">
          <Button
            v-if="cardPermissions.update"
            variant="outline"
            as-child
          >
            <Link :href="route('admin.store.gift-card.edit', card.id)">
              {{ __("Edit") }}
            </Link>
          </Button>
          <Button
            v-if="cardPermissions.delete"
            variant="outline"
            as-child
            class="text-destructive hover:text-destructive"
          >
            <Link
              v-confirm="{
                message: __('Delete this card permanently? Its ledger goes with it. A card that has paid for an order cannot be deleted.'),
              }"
              as="button"
              method="DELETE"
              :href="route('admin.store.gift-card.delete', card.id)"
            >
              {{ __("Delete") }}
            </Link>
          </Button>
        </div>
      </div>

      <!-- Summary -->
      <div class="bg-card rounded-lg shadow p-6 mb-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
          <div>
            <p class="text-xs text-muted-foreground mb-1">
              {{ __("Code") }}
            </p>
            <code class="px-2 py-1 rounded bg-muted text-sm font-mono select-all">
              {{ card.code }}
            </code>
          </div>

          <div>
            <p class="text-xs text-muted-foreground mb-1">
              {{ __("Remaining") }}
            </p>
            <p class="text-2xl font-semibold">
              {{ card.balance_formatted }}
            </p>
            <p class="text-xs text-muted-foreground">
              {{ __("of :amount issued", { amount: card.original_balance_formatted }) }}
            </p>
          </div>

          <div>
            <p class="text-xs text-muted-foreground mb-1">
              {{ __("Spendable") }}
            </p>
            <CommonStatusBadge
              :status="isSpendable ? 'green' : 'red'"
              :value="isSpendable ? __('Yes') : __('No')"
            />
            <p
              v-if="!card.is_enabled"
              class="text-xs text-muted-foreground mt-1"
            >
              {{ __("Disabled") }}
            </p>
            <p
              v-else-if="card.expires_at"
              class="text-xs text-muted-foreground mt-1"
            >
              {{ __("Expires :date", { date: new Date(card.expires_at).toLocaleString() }) }}
            </p>
          </div>

          <div>
            <p class="text-xs text-muted-foreground mb-1">
              {{ __("Issued To") }}
            </p>
            <p class="text-sm">
              {{ card.issued_to_user?.username ?? __("Anyone holding the code") }}
            </p>
            <p class="text-xs text-muted-foreground mt-1">
              {{ card.creator ? __("Issued by :name", { name: card.creator.username }) : __("Issued by a purchase") }}
            </p>
          </div>
        </div>
      </div>

      <!-- Adjustment -->
      <div
        v-if="cardPermissions.update"
        class="bg-card rounded-lg shadow p-6 mb-6"
      >
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-sm font-medium">
              {{ __("Adjust Balance") }}
            </h3>
            <p class="text-xs text-muted-foreground mt-1">
              {{ __("The only way to move a balance. Every adjustment is recorded below with who made it.") }}
            </p>
          </div>
          <Button
            v-if="!adjusting"
            variant="outline"
            @click="adjusting = true"
          >
            {{ __("Adjust") }}
          </Button>
        </div>

        <form
          v-if="adjusting"
          class="mt-4 space-y-4"
          @submit.prevent="submitAdjustment"
        >
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <XInput
              id="amount"
              v-model="adjustForm.amount"
              :label="__('Amount in :currency', { currency: card.currency_code })"
              :help="__('Positive tops the card up, negative takes credit off. It cannot go below zero.')"
              :error="adjustForm.errors.amount"
              type="number"
              :step="amountStep"
              name="amount"
            />
            <div class="sm:col-span-2">
              <XInput
                id="note"
                v-model="adjustForm.note"
                :label="__('Ledger Note')"
                :help="__('Why. Recorded against the entry.')"
                :error="adjustForm.errors.note"
                type="text"
                name="note"
              />
            </div>
          </div>

          <div class="flex justify-end gap-2">
            <Button
              type="button"
              variant="outline"
              @click="adjusting = false"
            >
              {{ __("Cancel") }}
            </Button>
            <Button
              type="submit"
              :disabled="adjustForm.processing"
            >
              {{ __("Apply Adjustment") }}
            </Button>
          </div>
        </form>
      </div>

      <!-- Ledger -->
      <div class="bg-card rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-border">
          <h3 class="text-sm font-medium">
            {{ __("Ledger") }}
          </h3>
          <p class="text-xs text-muted-foreground mt-1">
            {{ __("Every movement on this card, newest first.") }}
          </p>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-border">
            <thead class="bg-muted/50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase">
                  {{ __("What") }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase">
                  {{ __("Amount") }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase">
                  {{ __("Balance After") }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase">
                  {{ __("Order") }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase">
                  {{ __("When") }}
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-border">
              <tr
                v-for="transaction in card.transactions"
                :key="transaction.id"
              >
                <td class="px-6 py-4">
                  <div class="text-sm">
                    {{ typeLabel(transaction) }}
                  </div>
                  <div
                    v-if="transaction.note"
                    class="text-xs text-muted-foreground"
                  >
                    {{ transaction.note }}
                  </div>
                  <div
                    v-if="transaction.creator"
                    class="text-xs text-muted-foreground"
                  >
                    {{ __("by :name", { name: transaction.creator.username }) }}
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span
                    class="text-sm font-medium"
                    :class="transaction.amount < 0 ? 'text-destructive' : 'text-success'"
                  >{{ transaction.amount_formatted }}</span>
                </td>
                <td class="px-6 py-4 text-sm">
                  {{ transaction.balance_after_formatted }}
                </td>
                <td class="px-6 py-4">
                  <Link
                    v-if="transaction.order"
                    class="text-sm text-primary hover:underline"
                    :href="route('admin.store.order.show', transaction.order.uuid)"
                  >
                    {{ transaction.order.player_username ?? transaction.order.uuid }}
                  </Link>
                  <span
                    v-else
                    class="text-xs text-muted-foreground"
                  >—</span>
                </td>
                <td class="px-6 py-4 text-xs text-muted-foreground">
                  {{ new Date(transaction.created_at).toLocaleString() }}
                </td>
              </tr>
              <tr v-if="!card.transactions?.length">
                <td
                  class="px-6 py-8 text-sm text-center text-muted-foreground"
                  colspan="5"
                >
                  {{ __("Nothing has happened to this card yet.") }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
