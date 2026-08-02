<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { useTranslations } from "@/Composables/useTranslations";
import { useHelpers } from "@/Composables/useHelpers";
import AppBreadcrumb from "@/Shared/AppBreadcrumb.vue";
import { Button } from "@/Components/ui/button";
import { Link, useForm } from "@inertiajs/vue3";
import XInput from "@/Components/Form/XInput.vue";
import XDatePicker from "@/Components/Form/XDatePicker.vue";
import CommonStatusBadge from "@/Shared/CommonStatusBadge.vue";
import { ClipboardDocumentIcon, TrashIcon } from "@heroicons/vue/24/outline";
import { computed, ref } from "vue";

const { __ } = useTranslations();
const { formatToDayDateString } = useHelpers();

const props = defineProps({
    referral: Object,
    orders: Object,
    payouts: Object,
    trackingBaseUrl: String,
    canPayout: Boolean,
    baseCurrency: Object,
});

const breadcrumbItems = [
    {
        text: __("Admin"),
        current: false,
    },
    {
        text: __("Store Referrals"),
        url: route("admin.store.referral.index"),
        current: false,
    },
    {
        text: props.referral.code,
        current: true,
    },
];

const copied = ref(false);
const trackingUrl = computed(() => `${props.trackingBaseUrl}?ref=${props.referral.code}`);

function copy() {
    navigator.clipboard?.writeText(trackingUrl.value);
    copied.value = true;
    setTimeout(() => (copied.value = false), 2000);
}

// The exponent belongs to the currency, never a constant: JPY has no minor unit and KWD has three
// digits, so dividing by 100 would misstate both.
const exponent = computed(() => props.baseCurrency?.exponent ?? 2);

// The smallest amount the currency can express, which is also the smallest payout worth recording.
const amountStep = computed(() => (exponent.value > 0 ? (10 ** -exponent.value).toFixed(exponent.value) : "1"));

function toDecimal(minorUnits, digits) {
    if (minorUnits === null || minorUnits === undefined) {
        return null;
    }

    return Number((minorUnits / (10 ** digits)).toFixed(digits));
}

function toMinorUnits(decimalAmount, digits) {
    if (decimalAmount === null || decimalAmount === "" || isNaN(parseFloat(decimalAmount))) {
        return null;
    }

    return Math.round(parseFloat(decimalAmount) * (10 ** digits));
}

// Pre-filled with everything outstanding: paying the whole balance is the common case and should
// not need retyping. Clamped at zero, because a negative balance is not a payout to make. Entered
// in the base currency the balance is shown in — the conversion to minor units happens on submit,
// never in the admin's head.
const payoutForm = useForm({
    amount: toDecimal(Math.max(0, props.referral.owed), exponent.value),
    reference: "",
    note: "",
    paid_at: null,
});

function recordPayout() {
    payoutForm
        .transform(data => ({
            ...data,
            amount: toMinorUnits(data.amount, exponent.value),
        }))
        .post(route("admin.store.referral.payout", props.referral.id), {
            preserveScroll: true,
            onSuccess: () => payoutForm.reset("reference", "note", "paid_at"),
        });
}
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Referral: :code', { code: referral.code })" />

    <div class="px-10 py-8 mx-auto max-w-6xl text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
        <Button
          variant="outline"
          as-child
        >
          <Link :href="route('admin.store.referral.edit', referral.id)">
            {{ __("Edit") }}
          </Link>
        </Button>
      </div>

      <!-- Who, and their link -->
      <div class="shadow rounded-lg card-clip-safe mb-6 bg-card">
        <div class="px-4 py-5 sm:p-6 flex flex-wrap gap-6 items-start justify-between">
          <div>
            <div class="flex items-center gap-2">
              <h2 class="text-xl font-medium">
                {{ referral.referrer_name }}
              </h2>
              <CommonStatusBadge
                :status="referral.is_enabled ? 'green' : 'red'"
                :value="referral.is_enabled ? __('Enabled') : __('Disabled')"
              />
            </div>
            <p class="text-sm text-muted-foreground mt-1">
              <code class="px-1.5 py-0.5 rounded bg-muted text-xs font-mono select-all">{{ referral.code }}</code>
              <span class="ml-2">{{ __(":percent% share", { percent: Number(referral.share_bp) / 100 }) }}</span>
              <span
                v-if="referral.user"
                class="ml-2"
              >&middot; @{{ referral.user.username }}</span>
            </p>
          </div>

          <div class="grow max-w-xl">
            <label class="block text-sm font-medium mb-2">{{ __("Tracking URL") }}</label>
            <div class="flex gap-2">
              <input
                :value="trackingUrl"
                type="text"
                readonly
                class="grow rounded-md bg-muted border-input text-sm text-muted-foreground font-mono select-all"
              >
              <Button
                type="button"
                variant="outline"
                size="icon"
                :title="copied ? __('Copied') : __('Copy')"
                @click="copy"
              >
                <ClipboardDocumentIcon class="w-5 h-5" />
              </Button>
            </div>
          </div>
        </div>
      </div>

      <!-- The three figures. Earned less paid out is what is still owed. -->
      <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-card shadow rounded-lg p-5">
          <p class="text-sm text-muted-foreground">
            {{ __("Visits") }}
          </p>
          <p class="text-2xl font-semibold tabular-nums mt-1">
            {{ referral.visit_count }}
          </p>
          <p class="text-xs text-muted-foreground mt-1">
            {{ __(":count referred orders", { count: referral.orders_count }) }}
          </p>
        </div>

        <div class="bg-card shadow rounded-lg p-5">
          <p class="text-sm text-muted-foreground">
            {{ __("Earned") }}
          </p>
          <p class="text-2xl font-semibold tabular-nums mt-1">
            {{ referral.earned_formatted }}
          </p>
          <p class="text-xs text-muted-foreground mt-1">
            {{ __("On paid orders only") }}
          </p>
        </div>

        <div class="bg-card shadow rounded-lg p-5">
          <p class="text-sm text-muted-foreground">
            {{ __("Paid Out") }}
          </p>
          <p class="text-2xl font-semibold tabular-nums mt-1 text-muted-foreground">
            {{ referral.paid_out_formatted }}
          </p>
        </div>

        <div class="bg-card shadow rounded-lg p-5">
          <p class="text-sm text-muted-foreground">
            {{ __("Owed") }}
          </p>
          <p
            class="text-2xl font-semibold tabular-nums mt-1"
            :class="referral.owed < 0 ? 'text-destructive' : 'text-success'"
          >
            {{ referral.owed_formatted }}
          </p>
          <p
            v-if="referral.owed < 0"
            class="text-xs text-destructive mt-1"
          >
            {{ __("Overpaid after a refund. Carried against future earnings.") }}
          </p>
        </div>
      </div>

      <!-- Record a payout -->
      <div
        v-if="canPayout"
        class="shadow rounded-lg card-clip-safe mb-6 bg-card"
      >
        <div class="px-4 py-5 sm:p-6">
          <h3 class="text-lg font-medium mb-4">
            {{ __("Record a Payout") }}
          </h3>

          <form
            class="grid grid-cols-6 gap-4 items-end"
            @submit.prevent="recordPayout"
          >
            <div class="col-span-6 sm:col-span-2">
              <XInput
                v-model="payoutForm.amount"
                :label="__('Amount (:code)', { code: baseCurrency.code })"
                :help="__('Pre-filled with everything outstanding.')"
                :error="payoutForm.errors.amount"
                type="number"
                name="amount"
                :step="amountStep"
                :min="amountStep"
                required
              />
            </div>
            <div class="col-span-6 sm:col-span-2">
              <XInput
                v-model="payoutForm.reference"
                :label="__('Reference')"
                :help="__('PayPal transaction, bank reference.')"
                :error="payoutForm.errors.reference"
                type="text"
                name="reference"
              />
            </div>
            <!-- Two columns like the fields beside it, and carrying help text like them too.
                 `items-end` lines cells up by their bottom edge, so a field with no help sat a
                 whole line lower than its neighbours — and one column was too narrow to show a
                 date and a time at once. -->
            <div class="col-span-6 sm:col-span-2">
              <XDatePicker
                id="paid_at"
                v-model="payoutForm.paid_at"
                :label="__('Paid At')"
                :help="__('Leave empty to record it as now.')"
                :placeholder="__('Now')"
                :error="payoutForm.errors.paid_at"
                type="datetime"
                format="YYYY-MM-DD hh:mm:ss A"
                value-type="date"
              />
            </div>
            <div class="col-span-6 sm:col-span-4">
              <XInput
                v-model="payoutForm.note"
                :label="__('Note')"
                :error="payoutForm.errors.note"
                type="text"
                name="note"
              />
            </div>
            <!-- Beside the note rather than squeezed into the row of fields above, where it had a
                 column of its own and still bottom-aligned against their help text. -->
            <div class="col-span-6 sm:col-span-2">
              <Button
                type="submit"
                class="w-full"
                :disabled="payoutForm.processing"
              >
                {{ __("Record") }}
              </Button>
            </div>
          </form>
        </div>
      </div>

      <!-- Payout history -->
      <div class="shadow rounded-lg card-clip-safe mb-6 bg-card">
        <div class="px-4 py-5 sm:p-6">
          <h3 class="text-lg font-medium mb-4">
            {{ __("Payouts") }}
          </h3>

          <p
            v-if="payouts.data.length === 0"
            class="text-sm text-muted-foreground"
          >
            {{ __("Nothing has been paid out yet.") }}
          </p>

          <table
            v-else
            class="w-full text-sm"
          >
            <thead class="text-muted-foreground border-b border-border">
              <tr>
                <th class="text-left py-2 font-medium">
                  {{ __("Date") }}
                </th>
                <th class="text-right py-2 font-medium">
                  {{ __("Amount") }}
                </th>
                <th class="text-left py-2 font-medium">
                  {{ __("Reference") }}
                </th>
                <th class="text-left py-2 font-medium">
                  {{ __("Recorded By") }}
                </th>
                <th />
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="payout in payouts.data"
                :key="payout.id"
                class="border-b border-border last:border-0"
              >
                <td class="py-2">
                  {{ formatToDayDateString(payout.paid_at) }}
                </td>
                <td class="py-2 text-right tabular-nums font-medium">
                  {{ payout.amount_formatted }}
                </td>
                <td class="py-2 text-muted-foreground">
                  {{ payout.reference || "—" }}
                  <div
                    v-if="payout.note"
                    class="text-xs"
                  >
                    {{ payout.note }}
                  </div>
                </td>
                <td class="py-2 text-muted-foreground">
                  {{ payout.creator?.username ?? __("System") }}
                </td>
                <td class="py-2 text-right">
                  <Button
                    v-if="canPayout"
                    variant="outline"
                    size="icon"
                    as-child
                    class="text-destructive hover:text-destructive"
                  >
                    <Link
                      v-confirm="{
                        message: __('Remove this payout? The amount goes straight back into what is owed.'),
                      }"
                      as="button"
                      method="DELETE"
                      :href="route('admin.store.referral.payout.delete', [referral.id, payout.id])"
                    >
                      <TrashIcon />
                    </Link>
                  </Button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Where the earned figure came from -->
      <div class="shadow rounded-lg card-clip-safe bg-card">
        <div class="px-4 py-5 sm:p-6">
          <h3 class="text-lg font-medium mb-1">
            {{ __("Referred Orders") }}
          </h3>
          <p class="text-sm text-muted-foreground mb-4">
            {{ __("A refunded or charged back order stays here contributing nothing, so it is visible why the earned total moved.") }}
          </p>

          <p
            v-if="orders.data.length === 0"
            class="text-sm text-muted-foreground"
          >
            {{ __("No orders have used this code yet.") }}
          </p>

          <table
            v-else
            class="w-full text-sm"
          >
            <thead class="text-muted-foreground border-b border-border">
              <tr>
                <th class="text-left py-2 font-medium">
                  {{ __("Date") }}
                </th>
                <th class="text-left py-2 font-medium">
                  {{ __("Order") }}
                </th>
                <th class="text-left py-2 font-medium">
                  {{ __("Source") }}
                </th>
                <th class="text-left py-2 font-medium">
                  {{ __("Status") }}
                </th>
                <th class="text-right py-2 font-medium">
                  {{ __("Order Total") }}
                </th>
                <th class="text-right py-2 font-medium">
                  {{ __("Earned") }}
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="order in orders.data"
                :key="order.id"
                class="border-b border-border last:border-0"
                :class="order.counts_towards_balance ? '' : 'text-muted-foreground line-through'"
              >
                <td class="py-2">
                  {{ formatToDayDateString(order.created_at) }}
                </td>
                <td class="py-2">
                  <Link
                    class="text-primary hover:underline font-mono text-xs no-underline"
                    :href="route('admin.store.order.show', order.uuid)"
                  >
                    {{ order.uuid.slice(0, 8) }}
                  </Link>
                </td>
                <td class="py-2 text-xs">
                  {{ order.referral_source === "manual" ? __("Typed") : __("Link") }}
                </td>
                <td class="py-2">
                  {{ order.status?.value ?? order.status }}
                </td>
                <td class="py-2 text-right tabular-nums">
                  {{ order.total_formatted }}
                </td>
                <td class="py-2 text-right tabular-nums font-medium">
                  {{ order.earning_formatted }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
