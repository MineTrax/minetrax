<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { useTranslations } from "@/Composables/useTranslations";
import AppBreadcrumb from "@/Shared/AppBreadcrumb.vue";
import { Button } from "@/Components/ui/button";
import { Link, useForm } from "@inertiajs/vue3";
import XInput from "@/Components/Form/XInput.vue";
import XDatePicker from "@/Components/Form/XDatePicker.vue";

const { __ } = useTranslations();

const props = defineProps({
    storeBan: Object,
    username: String,
});

const breadcrumbItems = [
    {
        text: __("Admin"),
        current: false,
    },
    {
        text: __("Store Bans"),
        url: route("admin.store.ban.index"),
        current: false,
    },
    {
        text: __("Edit Ban"),
        current: true,
    }
];

// The date picker works in Date objects; the server sends an ISO timestamp.
function toDate(timestamp) {
    return timestamp ? new Date(timestamp) : null;
}

const form = useForm({
    username: props.username ?? null,
    player_uuid: props.storeBan.player_uuid,
    ip_address: props.storeBan.ip_address,
    email: props.storeBan.email,
    reason: props.storeBan.reason,
    expires_at: toDate(props.storeBan.expires_at),
});

function updateBan() {
    form.put(route("admin.store.ban.update", props.storeBan.id), {});
}
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Edit Store Ban')" />

    <div class="px-10 py-8 mx-auto max-w-6xl text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
      </div>

      <div
        v-if="storeBan.is_automatic"
        class="bg-card rounded-lg shadow p-4 mb-6 border-l-4 border-amber-500"
      >
        <p class="text-sm text-foreground">
          {{ __("This ban was raised automatically by a chargeback.") }}
        </p>
        <p class="text-xs text-muted-foreground mt-1">
          {{ __("If the chargeback was a mistake, clear the identities that should not be blocked, or lift the ban entirely from the list. Editing it here does not make it a manual ban.") }}
        </p>
      </div>

      <div class="mt-6">
        <form @submit.prevent="updateBan">
          <!-- Identity Section -->
          <div class="shadow overflow-hidden rounded-lg mb-6 card-clip-safe">
            <div class="px-4 py-5 bg-card sm:p-6 border-b border-border">
              <h3 class="text-lg font-medium text-foreground mb-1">
                {{ __("Who To Block") }}
              </h3>
              <p class="text-sm text-muted-foreground mb-4">
                {{ __("Fill in at least one. Any single match blocks the checkout, so filling in several widens the ban rather than narrowing it — which is what you want against someone who will come back as a guest.") }}
              </p>
              <div class="grid grid-cols-6 gap-6">
                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="username"
                    v-model="form.username"
                    :label="__('Account Username')"
                    :help="__('Blocks this site account however they pay.')"
                    :error="form.errors.username"
                    type="text"
                    name="username"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="player_uuid"
                    v-model="form.player_uuid"
                    :label="__('Player UUID')"
                    :help="__('The Minecraft UUID a purchase is delivered to. Dashes are added for you.')"
                    :error="form.errors.player_uuid"
                    type="text"
                    name="player_uuid"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="ip_address"
                    v-model="form.ip_address"
                    :label="__('IP Address')"
                    :help="__('Catches a returning guest. Be careful: households and mobile networks share addresses.')"
                    :error="form.errors.ip_address"
                    type="text"
                    name="ip_address"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="email"
                    v-model="form.email"
                    :label="__('Email Address')"
                    :help="__('Matched case-insensitively against the address given at checkout.')"
                    :error="form.errors.email"
                    type="email"
                    name="email"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Terms Section -->
          <div class="shadow overflow-hidden rounded-lg mb-6 card-clip-safe">
            <div class="px-4 py-5 bg-card sm:p-6 border-b border-border">
              <h3 class="text-lg font-medium text-foreground mb-4">
                {{ __("Terms") }}
              </h3>
              <div class="grid grid-cols-6 gap-6">
                <div class="col-span-6">
                  <XInput
                    id="reason"
                    v-model="form.reason"
                    :label="__('Reason')"
                    :help="__('Staff-facing only. Never shown to the buyer, who simply cannot check out.')"
                    :error="form.errors.reason"
                    type="text"
                    name="reason"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XDatePicker
                    id="expires_at"
                    v-model="form.expires_at"
                    :label="__('Expires At')"
                    :help="__('Leave empty for a permanent ban. After this moment the row stays as a record but stops blocking.')"
                    :error="form.errors.expires_at"
                    type="datetime"
                    format="YYYY-MM-DD hh:mm:ss A"
                    value-type="date"
                    :placeholder="__('Select date and time')"
                    name="expires_at"
                  />
                </div>
              </div>
            </div>
            <div class="px-4 py-3 bg-card border-t border-border sm:px-6 flex justify-end gap-2">
              <Button
                variant="outline"
                as-child
              >
                <Link :href="route('admin.store.ban.index')">
                  {{ __("Cancel") }}
                </Link>
              </Button>
              <Button
                type="submit"
                :disabled="form.processing"
              >
                {{ __("Save Ban") }}
              </Button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
