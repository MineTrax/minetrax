<script setup>
import { computed, ref } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import AppHead from "@/Components/AppHead.vue";
import { Link, useForm } from "@inertiajs/vue3";
import { useTranslations } from "@/Composables/useTranslations";
import { Button } from "@/Components/ui/button";
import XInput from "@/Components/Form/XInput.vue";
import XSelect from "@/Components/Form/XSelect.vue";
import XSwitch from "@/Components/Form/XSwitch.vue";
import { LockIcon } from "lucide-vue-next";

const { __ } = useTranslations();

const props = defineProps({
    quote: { type: Object, required: true },
    gateways: { type: Array, required: true },
    linkedPlayers: { type: Array, default: () => [] },
    requiresEmail: { type: Boolean, default: false },
    termsText: { type: String, default: null },
    mojangVerification: { type: Boolean, default: true },
    requiresBillingAddress: { type: Boolean, default: false },
    // Only sent when the address block will render, so a checkout that asks for no address is not
    // carrying a couple of hundred rows it never uses.
    countries: { type: Array, default: () => [] },
});

const form = useForm({
    player_username: props.linkedPlayers[0]?.username ?? "",
    email: "",
    gateway: props.gateways[0]?.key ?? "",
    accept_terms: false,
    billing_name: "",
    billing_address_line1: "",
    billing_address_line2: "",
    billing_city: "",
    billing_state: "",
    billing_postal_code: "",
    billing_country_id: null,
});

// XSelect takes an object of value => label, and its keys arrive back as strings — the id is
// stringified on submit anyway, and the server validates it as an integer.
const countryOptions = computed(
    () => Object.fromEntries(props.countries.map((country) => [country.id, country.name]))
);

const showTerms = ref(false);

const hasGateways = computed(() => props.gateways.length > 0);

const submit = () => form.post(route("store.checkout.store"));
</script>

<template>
  <AppLayout>
    <AppHead :title="__('Checkout')" />

    <div class="px-4 py-8 mx-auto max-w-5xl text-foreground">
      <h1 class="text-2xl font-semibold mb-4">
        {{ __("Checkout") }}
      </h1>

      <!-- Where they are and what is left. A single unlabelled form gives no sense of how long
           this takes, and "how much more of this is there" is what closes a tab. -->
      <ol class="flex items-center gap-2 text-xs mb-6">
        <li class="flex items-center gap-2 text-muted-foreground">
          <span class="w-5 h-5 rounded-full bg-muted flex items-center justify-center font-semibold">1</span>
          {{ __("Cart") }}
        </li>
        <li
          class="h-px w-6 bg-border"
          aria-hidden="true"
        />
        <li class="flex items-center gap-2 font-semibold text-foreground">
          <span class="w-5 h-5 rounded-full bg-primary text-primary-foreground flex items-center justify-center">2</span>
          {{ __("Details") }}
        </li>
        <li
          class="h-px w-6 bg-border"
          aria-hidden="true"
        />
        <li class="flex items-center gap-2 text-muted-foreground">
          <span class="w-5 h-5 rounded-full bg-muted flex items-center justify-center font-semibold">3</span>
          {{ __("Payment") }}
        </li>
      </ol>

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

          <!-- Billing address. Asked of guests and members alike: an account holds no address, so
               being signed in is not a reason to skip it. -->
          <section
            v-if="requiresBillingAddress"
            class="bg-card rounded-lg shadow p-6"
          >
            <h2 class="text-sm font-medium mb-4">
              {{ __("Billing Address") }}
            </h2>

            <div class="grid grid-cols-6 gap-4">
              <div class="col-span-6">
                <XInput
                  id="billing_name"
                  v-model="form.billing_name"
                  :label="__('Full Name')"
                  :error="form.errors.billing_name"
                  :required="true"
                  type="text"
                  name="billing_name"
                  autocomplete="name"
                />
              </div>

              <div class="col-span-6">
                <XInput
                  id="billing_address_line1"
                  v-model="form.billing_address_line1"
                  :label="__('Address Line 1')"
                  :error="form.errors.billing_address_line1"
                  :required="true"
                  type="text"
                  name="billing_address_line1"
                  autocomplete="address-line1"
                />
              </div>

              <div class="col-span-6">
                <XInput
                  id="billing_address_line2"
                  v-model="form.billing_address_line2"
                  :label="__('Address Line 2')"
                  :help="__('Optional.')"
                  :error="form.errors.billing_address_line2"
                  type="text"
                  name="billing_address_line2"
                  autocomplete="address-line2"
                />
              </div>

              <div class="col-span-6 sm:col-span-3">
                <XInput
                  id="billing_city"
                  v-model="form.billing_city"
                  :label="__('City')"
                  :error="form.errors.billing_city"
                  :required="true"
                  type="text"
                  name="billing_city"
                  autocomplete="address-level2"
                />
              </div>

              <div class="col-span-6 sm:col-span-3">
                <XInput
                  id="billing_state"
                  v-model="form.billing_state"
                  :label="__('State / Province')"
                  :help="__('Optional.')"
                  :error="form.errors.billing_state"
                  type="text"
                  name="billing_state"
                  autocomplete="address-level1"
                />
              </div>

              <div class="col-span-6 sm:col-span-3">
                <XInput
                  id="billing_postal_code"
                  v-model="form.billing_postal_code"
                  :label="__('Zip / Postal Code')"
                  :error="form.errors.billing_postal_code"
                  :required="true"
                  type="text"
                  name="billing_postal_code"
                  autocomplete="postal-code"
                />
              </div>

              <div class="col-span-6 sm:col-span-3">
                <XSelect
                  id="billing_country_id"
                  v-model="form.billing_country_id"
                  :label="__('Country')"
                  :select-list="countryOptions"
                  :error="form.errors.billing_country_id"
                  name="billing_country_id"
                />
              </div>
            </div>
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

            <!-- Says what the button does before it does it. Being thrown to a third-party domain
                 unannounced is the moment a first-time buyer decides the store is not legitimate. -->
            <p
              v-if="hasGateways"
              class="flex items-center gap-1.5 text-xs text-muted-foreground mt-4"
            >
              <LockIcon class="w-3 h-3 shrink-0" />
              {{ __("You will be taken to the payment provider to pay securely. We never see your card details.") }}
            </p>
          </section>

          <!-- Terms -->
          <section class="bg-card rounded-lg shadow p-6">
            <XSwitch
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
            <!-- The cart itemises a sale and an upgrade credit; this summary did not, so a buyer
                 who had both went from a cart whose figures added up to a checkout whose figures
                 did not. Money that disappears between two screens is money a buyer stops to
                 query. -->
            <div
              v-if="quote.sale_discount > 0"
              class="flex justify-between text-success"
            >
              <dt>{{ __("Sale Discount") }}</dt>
              <dd>-{{ quote.formatted.sale_discount }}</dd>
            </div>
            <div
              v-if="quote.upgrade_credit > 0"
              class="flex justify-between text-success"
            >
              <dt>{{ __("Upgrade Credit") }}</dt>
              <dd>-{{ quote.formatted.upgrade_credit }}</dd>
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

            <!-- Said again here rather than only on the cart: this is the last page before money
                 moves, and who it credits is part of what is being agreed to. -->
            <div
              v-if="quote.referral"
              class="text-xs text-muted-foreground border-t border-border pt-2 mt-2"
            >
              {{ __("Supporting :name", { name: quote.referral.referrer_name }) }}
            </div>
          </dl>
        </aside>
      </div>
    </div>
  </AppLayout>
</template>
