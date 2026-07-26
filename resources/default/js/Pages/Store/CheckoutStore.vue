<script setup>
import { computed, ref } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import AppHead from "@/Components/AppHead.vue";
import { Link, useForm } from "@inertiajs/vue3";
import { useTranslations } from "@/Composables/useTranslations";
import { Button } from "@/Components/ui/button";
import XInput from "@/Components/Form/XInput.vue";
import XCheckbox from "@/Components/Form/XCheckbox.vue";

const { __ } = useTranslations();

const props = defineProps({
    quote: { type: Object, required: true },
    gateways: { type: Array, required: true },
    linkedPlayers: { type: Array, default: () => [] },
    requiresEmail: { type: Boolean, default: false },
    termsText: { type: String, default: null },
    mojangVerification: { type: Boolean, default: true },
});

const form = useForm({
    player_username: props.linkedPlayers[0]?.username ?? "",
    email: "",
    gateway: props.gateways[0]?.key ?? "",
    accept_terms: false,
});

const showTerms = ref(false);

const hasGateways = computed(() => props.gateways.length > 0);

const submit = () => form.post(route("store.checkout.store"));
</script>

<template>
  <AppLayout>
    <AppHead :title="__('Checkout')" />

    <div class="px-4 py-8 mx-auto max-w-5xl text-foreground">
      <h1 class="text-2xl font-semibold mb-6">
        {{ __("Checkout") }}
      </h1>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <form
          class="lg:col-span-2 space-y-6"
          @submit.prevent="submit"
        >
          <!-- Recipient -->
          <section class="bg-card rounded-lg shadow p-6">
            <h2 class="text-sm font-medium mb-1">
              {{ __("Who is this for?") }}
            </h2>
            <p class="text-xs text-muted-foreground mb-4">
              {{ mojangVerification
                ? __("Enter the Minecraft username that should receive these items. We will check it exists.")
                : __("Enter the Minecraft username that should receive these items.") }}
            </p>

            <div
              v-if="linkedPlayers.length"
              class="flex flex-wrap gap-2 mb-3"
            >
              <button
                v-for="player in linkedPlayers"
                :key="player.id"
                type="button"
                class="px-3 py-1 text-xs rounded-full border transition-colors cursor-pointer"
                :class="form.player_username === player.username
                  ? 'bg-primary text-primary-foreground border-primary'
                  : 'border-border hover:bg-muted'"
                @click="form.player_username = player.username"
              >
                {{ player.username }}
              </button>
            </div>

            <XInput
              id="player_username"
              v-model="form.player_username"
              :label="__('Minecraft Username')"
              :error="form.errors.player_username"
              :required="true"
              type="text"
              name="player_username"
              maxlength="16"
              autocomplete="off"
            />
          </section>

          <!-- Contact -->
          <section
            v-if="requiresEmail"
            class="bg-card rounded-lg shadow p-6"
          >
            <h2 class="text-sm font-medium mb-4">
              {{ __("Where should we send your receipt?") }}
            </h2>
            <XInput
              id="email"
              v-model="form.email"
              :label="__('Email Address')"
              :error="form.errors.email"
              :required="true"
              type="email"
              name="email"
            />
          </section>

          <!-- Payment method -->
          <section class="bg-card rounded-lg shadow p-6">
            <h2 class="text-sm font-medium mb-4">
              {{ __("Payment Method") }}
            </h2>

            <p
              v-if="!hasGateways"
              class="text-sm text-destructive"
            >
              {{ __("No payment method is available for this currency right now. Please try another currency or check back later.") }}
            </p>

            <div class="space-y-2">
              <label
                v-for="gateway in gateways"
                :key="gateway.key"
                class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition-colors"
                :class="form.gateway === gateway.key ? 'border-primary bg-primary/5' : 'border-border hover:bg-muted'"
              >
                <input
                  v-model="form.gateway"
                  type="radio"
                  name="gateway"
                  :value="gateway.key"
                  class="mt-1"
                >
                <span>
                  <span class="block text-sm font-medium">{{ gateway.label }}</span>
                  <span
                    v-if="gateway.description"
                    class="block text-xs text-muted-foreground"
                  >{{ gateway.description }}</span>
                </span>
              </label>
            </div>

            <p
              v-if="form.errors.gateway"
              class="text-xs text-destructive mt-2"
            >
              {{ form.errors.gateway }}
            </p>
          </section>

          <!-- Terms -->
          <section class="bg-card rounded-lg shadow p-6">
            <XCheckbox
              id="accept_terms"
              v-model="form.accept_terms"
              :label="__('I accept the terms of service')"
              :error="form.errors.accept_terms"
              name="accept_terms"
            />

            <button
              v-if="termsText"
              type="button"
              class="text-xs text-primary hover:underline mt-2 cursor-pointer"
              @click="showTerms = !showTerms"
            >
              {{ showTerms ? __("Hide terms") : __("Read terms") }}
            </button>

            <p
              v-if="showTerms && termsText"
              class="text-xs text-muted-foreground whitespace-pre-line mt-3 max-h-48 overflow-y-auto"
            >
              {{ termsText }}
            </p>
          </section>

          <p
            v-if="form.errors.cart"
            class="text-sm text-destructive"
          >
            {{ form.errors.cart }}
          </p>

          <div class="flex justify-between items-center">
            <Link
              class="text-sm text-muted-foreground hover:text-foreground"
              :href="route('store.cart.show')"
            >
              &larr; {{ __("Back to cart") }}
            </Link>

            <Button
              type="submit"
              size="lg"
              :disabled="form.processing || !hasGateways"
            >
              {{ __("Pay :amount", { amount: quote.formatted.amount_due }) }}
            </Button>
          </div>
        </form>

        <!-- Summary -->
        <aside class="bg-card rounded-lg shadow p-6 h-fit">
          <h2 class="text-sm font-medium mb-4">
            {{ __("Order Summary") }}
          </h2>

          <ul class="space-y-3 mb-4">
            <li
              v-for="item in quote.items"
              :key="item.cart_item_id ?? item.package_id"
              class="flex justify-between gap-3 text-sm"
            >
              <span>
                <span class="block">{{ item.package_name }}</span>
                <span class="block text-xs text-muted-foreground">&times;{{ item.quantity }}</span>
              </span>
              <span class="whitespace-nowrap">{{ item.formatted.total }}</span>
            </li>
          </ul>

          <dl class="space-y-1 text-sm border-t border-border pt-4">
            <div class="flex justify-between">
              <dt class="text-muted-foreground">
                {{ __("Subtotal") }}
              </dt>
              <dd>{{ quote.formatted.subtotal }}</dd>
            </div>
            <div
              v-if="quote.coupon_discount > 0"
              class="flex justify-between text-success"
            >
              <dt>{{ __("Coupon") }} <span v-if="quote.coupon_code">({{ quote.coupon_code }})</span></dt>
              <dd>-{{ quote.formatted.coupon_discount }}</dd>
            </div>
            <div
              v-if="quote.tax_amount > 0"
              class="flex justify-between"
            >
              <dt class="text-muted-foreground">
                {{ quote.tax_label || __("Tax") }}
              </dt>
              <dd>{{ quote.formatted.tax_amount }}</dd>
            </div>
            <div class="flex justify-between font-medium pt-1">
              <dt>{{ __("Total") }}</dt>
              <dd>{{ quote.formatted.total }}</dd>
            </div>
            <div
              v-if="quote.gift_card_amount > 0"
              class="flex justify-between text-success"
            >
              <dt>{{ __("Gift Card") }}</dt>
              <dd>-{{ quote.formatted.gift_card_amount }}</dd>
            </div>
            <div
              v-if="quote.gift_card_amount > 0"
              class="flex justify-between font-medium border-t border-border pt-2 mt-2"
            >
              <dt>{{ __("Due Now") }}</dt>
              <dd>{{ quote.formatted.amount_due }}</dd>
            </div>
          </dl>
        </aside>
      </div>
    </div>
  </AppLayout>
</template>
