<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useTranslations } from "@/Composables/useTranslations";
import { useHelpers } from "@/Composables/useHelpers";
import { Button } from "@/Components/ui/button";
import { ClipboardDocumentIcon } from "@heroicons/vue/24/outline";
import { ref } from "vue";

const { __ } = useTranslations();
const { formatToDayDateString } = useHelpers();

const props = defineProps({
    referral: Object,
    payouts: Object,
    trackingUrl: String,
});

const copied = ref(false);

function copy() {
    navigator.clipboard?.writeText(props.trackingUrl);
    copied.value = true;
    setTimeout(() => (copied.value = false), 2000);
}
</script>

<template>
  <AppLayout :title="__('My Referral Code')">
    <div class="px-4 py-8 mx-auto max-w-4xl text-foreground">
      <h1 class="text-2xl font-semibold mb-1">
        {{ __("My Referral Code") }}
      </h1>
      <p class="text-sm text-muted-foreground mb-6">
        {{ __("Share your link. You earn :percent% of what people spend after using it.", { percent: Number(referral.share_bp) / 100 }) }}
      </p>

      <div
        v-if="!referral.is_enabled"
        class="rounded-lg border border-destructive/30 bg-destructive/10 text-destructive p-4 mb-6 text-sm"
      >
        {{ __("This code is currently disabled, so it is not earning. Anything already earned is unaffected.") }}
      </div>

      <!-- The link -->
      <div class="bg-card shadow rounded-lg p-5 mb-6">
        <label class="block text-sm font-medium mb-2">{{ __("Your Link") }}</label>
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
        <p class="text-xs text-muted-foreground mt-2">
          {{ __("Buyers can also type :code at the cart.", { code: referral.code }) }}
        </p>
      </div>

      <!-- Earned, paid out, outstanding -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-card shadow rounded-lg p-5">
          <p class="text-sm text-muted-foreground">
            {{ __("Visits") }}
          </p>
          <p class="text-2xl font-semibold tabular-nums mt-1">
            {{ referral.visit_count }}
          </p>
        </div>

        <div class="bg-card shadow rounded-lg p-5">
          <p class="text-sm text-muted-foreground">
            {{ __("Orders") }}
          </p>
          <p class="text-2xl font-semibold tabular-nums mt-1">
            {{ referral.orders_count }}
          </p>
        </div>

        <div class="bg-card shadow rounded-lg p-5">
          <p class="text-sm text-muted-foreground">
            {{ __("Earned to date") }}
          </p>
          <p class="text-2xl font-semibold tabular-nums mt-1">
            {{ referral.earned_formatted }}
          </p>
        </div>

        <div class="bg-card shadow rounded-lg p-5">
          <p class="text-sm text-muted-foreground">
            {{ __("Outstanding") }}
          </p>
          <p
            class="text-2xl font-semibold tabular-nums mt-1"
            :class="referral.owed < 0 ? 'text-destructive' : 'text-success'"
          >
            {{ referral.owed_formatted }}
          </p>
          <p class="text-xs text-muted-foreground mt-1">
            {{ __(":paid paid out", { paid: referral.paid_out_formatted }) }}
          </p>
        </div>
      </div>

      <div
        v-if="referral.owed < 0"
        class="rounded-lg border border-border bg-muted/50 p-4 mb-6 text-sm text-muted-foreground"
      >
        {{ __("An order you were paid for has since been refunded, so this is carried against what you earn next.") }}
      </div>

      <!-- What has actually been paid -->
      <div class="bg-card shadow rounded-lg p-5">
        <h2 class="text-lg font-medium mb-4">
          {{ __("Payouts") }}
        </h2>

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
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>
