<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { useTranslations } from "@/Composables/useTranslations";
import AppBreadcrumb from "@/Shared/AppBreadcrumb.vue";
import { Button } from "@/Components/ui/button";
import { Link, useForm } from "@inertiajs/vue3";
import XInput from "@/Components/Form/XInput.vue";
import XSelect from "@/Components/Form/XSelect.vue";
import XSwitch from "@/Components/Form/XSwitch.vue";
import ReferralCommandsCard from "@/Components/Store/ReferralCommandsCard.vue";
import ReferralTrackingCard from "@/Components/Store/ReferralTrackingCard.vue";
import { computed } from "vue";

const { __ } = useTranslations();

const props = defineProps({
    storeReferral: Object,
    coupons: Array,
    servers: Array,
    attributionModes: Object,
    trackingBaseUrl: String,
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
        text: props.storeReferral.code,
        current: true,
    },
];

const form = useForm({
    code: props.storeReferral.code,
    referrer_name: props.storeReferral.referrer_name,
    username: props.storeReferral.user?.username ?? "",
    share_percent: Number(props.storeReferral.share_bp) / 100,
    // XSelect binds by value, and a numeric id never preselects against a string key.
    store_coupon_id: props.storeReferral.store_coupon_id
        ? String(props.storeReferral.store_coupon_id)
        : null,
    is_url_tracking_enabled: props.storeReferral.is_url_tracking_enabled,
    attribution_window_days: props.storeReferral.attribution_window_days,
    // The server serialises a backed enum as {key, value}; the raw fallback covers a plain string.
    attribution_mode: props.storeReferral.attribution_mode?.value ?? props.storeReferral.attribution_mode,
    is_command_execution_enabled: props.storeReferral.is_command_execution_enabled,
    is_enabled: props.storeReferral.is_enabled,
    notes: props.storeReferral.notes ?? "",
    commands: (props.storeReferral.commands ?? []).map((command) => ({
        id: command.id,
        command: command.command,
        delay_seconds: command.delay_seconds,
        is_player_online_required: command.is_player_online_required,
        sort_order: command.sort_order,
        servers: command.servers ?? [],
    })),
});

const couponOptions = computed(() => {
    const options = {};

    props.coupons.forEach((coupon) => {
        const type = coupon.discount_type?.value ?? coupon.discount_type;

        options[coupon.id] = type === "percent"
            ? `${coupon.code} (${Number(coupon.discount_value) / 100}%)`
            : coupon.code;
    });

    return options;
});

function submit() {
    form
        .transform((data) => ({
            ...data,
            share_bp: Math.round(Number(data.share_percent) * 100),
            commands: data.commands.map((command) => ({
                ...command,
                servers: (command.servers ?? []).map((server) => ({ id: server.id })),
            })),
        }))
        .put(route("admin.store.referral.update", props.storeReferral.id));
}
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Edit Referral Code')" />

    <div class="px-10 py-8 mx-auto max-w-5xl text-foreground">
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
          <Link :href="route('admin.store.referral.show', storeReferral.id)">
            {{ __("Earnings and Payouts") }}
          </Link>
        </Button>
      </div>

      <form @submit.prevent="submit">
        <div class="shadow rounded-lg card-clip-safe mb-6">
          <div class="px-4 py-5 bg-card sm:p-6 border-b border-border">
            <h3 class="text-lg font-medium text-foreground mb-4">
              {{ __("Referrer Details") }}
            </h3>

            <div class="grid grid-cols-6 gap-6">
              <div class="col-span-6 sm:col-span-3">
                <XInput
                  v-model="form.referrer_name"
                  :label="__('Referrer Name')"
                  :help="__('Shown to buyers as who their purchase supports.')"
                  :error="form.errors.referrer_name"
                  type="text"
                  name="referrer_name"
                  required
                />
              </div>

              <div class="col-span-6 sm:col-span-3">
                <XInput
                  v-model="form.code"
                  :label="__('Referral Code')"
                  :help="__('Changing this breaks links already published with the old code. Orders keep the code they recorded.')"
                  :error="form.errors.code"
                  type="text"
                  name="code"
                  required
                />
              </div>

              <div class="col-span-6 sm:col-span-3">
                <XInput
                  v-model.number="form.share_percent"
                  :label="__('Sharing Part (%)')"
                  :help="__('Applies to orders from now on. What has already been earned keeps the rate it was bought at.')"
                  :error="form.errors.share_bp"
                  type="number"
                  name="share_percent"
                  step="0.01"
                  min="0"
                  max="100"
                  required
                />
              </div>

              <div class="col-span-6 sm:col-span-3">
                <XSelect
                  v-model="form.store_coupon_id"
                  :label="__('Attached Coupon')"
                  :help="__('Applied automatically for anyone using this code. A coupon the buyer types themselves takes priority.')"
                  :select-list="couponOptions"
                  :error="form.errors.store_coupon_id"
                  :placeholder="__('No discount')"
                />
              </div>

              <div class="col-span-6 sm:col-span-3">
                <XInput
                  v-model="form.username"
                  :label="__('Linked Account')"
                  :help="__('Optional. Lets this member see their own earnings, and stops the code paying them for their own purchases.')"
                  :error="form.errors.username"
                  type="text"
                  name="username"
                />
              </div>

              <div class="col-span-6 sm:col-span-3">
                <XInput
                  v-model="form.notes"
                  :label="__('Internal Note')"
                  :error="form.errors.notes"
                  type="text"
                  name="notes"
                />
              </div>

              <div class="col-span-6">
                <XSwitch
                  id="is_enabled"
                  v-model="form.is_enabled"
                  :label="__('Enabled')"
                  :help="__('A disabled code stops tracking and stops earning. What it has already earned is untouched.')"
                  :error="form.errors.is_enabled"
                  name="is_enabled"
                />
              </div>
            </div>
          </div>
        </div>

        <ReferralTrackingCard
          :form="form"
          :attribution-modes="attributionModes"
          :tracking-base-url="trackingBaseUrl"
        />

        <ReferralCommandsCard
          :form="form"
          :servers="servers"
        />

        <div class="flex justify-end gap-2">
          <Button
            variant="outline"
            as-child
          >
            <Link :href="route('admin.store.referral.index')">
              {{ __("Cancel") }}
            </Link>
          </Button>
          <Button
            type="submit"
            :disabled="form.processing"
          >
            {{ __("Save Referral Code") }}
          </Button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>
