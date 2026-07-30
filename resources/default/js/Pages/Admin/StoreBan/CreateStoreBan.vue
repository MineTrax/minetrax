<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { useTranslations } from "@/Composables/useTranslations";
import AppBreadcrumb from "@/Shared/AppBreadcrumb.vue";
import { Button } from "@/Components/ui/button";
import { Link, useForm } from "@inertiajs/vue3";
import XInput from "@/Components/Form/XInput.vue";

const { __ } = useTranslations();

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
        text: __("Create Ban"),
        current: true,
    }
];

const form = useForm({
    username: null,
    player_uuid: null,
    ip_address: null,
    email: null,
    reason: null,
    expires_at: null,
});

function createBan() {
    form.post(route("admin.store.ban.store"), {});
}
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Create Store Ban')" />

    <div class="px-10 py-8 mx-auto max-w-6xl text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
      </div>

      <div class="mt-6">
        <form @submit.prevent="createBan">
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
                  <XInput
                    id="expires_at"
                    v-model="form.expires_at"
                    :label="__('Expires At')"
                    :help="__('Leave empty for a permanent ban. After this moment the row stays as a record but stops blocking.')"
                    :error="form.errors.expires_at"
                    type="datetime-local"
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
                {{ __("Create Ban") }}
              </Button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
