<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { useTranslations } from "@/Composables/useTranslations";
import AppBreadcrumb from "@/Shared/AppBreadcrumb.vue";
import { Button } from "@/Components/ui/button";
import { Link, useForm } from "@inertiajs/vue3";
import XInput from "@/Components/Form/XInput.vue";
import XSwitch from "@/Components/Form/XSwitch.vue";

const { __ } = useTranslations();

const props = defineProps({
    storeGiftCard: Object,
    username: String,
    balanceFormatted: String,
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
        text: __("Edit Gift Card"),
        current: true,
    }
];

// A datetime-local input wants "YYYY-MM-DDTHH:MM"; the server sends an ISO timestamp.
function toLocalInput(timestamp) {
    if (! timestamp) {
        return null;
    }
    const date = new Date(timestamp);
    const pad = (value) => String(value).padStart(2, "0");
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
}

const form = useForm({
    username: props.username ?? null,
    expires_at: toLocalInput(props.storeGiftCard.expires_at),
    is_enabled: !! props.storeGiftCard.is_enabled,
});

function updateGiftCard() {
    form.put(route("admin.store.gift-card.update", props.storeGiftCard.id), {});
}
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Edit Store Gift Card')" />

    <div class="px-10 py-8 mx-auto max-w-6xl text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
      </div>

      <div class="bg-card rounded-lg shadow p-4 mb-6 flex items-center justify-between">
        <div>
          <code class="px-1.5 py-0.5 rounded bg-muted text-xs font-mono select-all">
            {{ storeGiftCard.code }}
          </code>
          <p class="text-xs text-muted-foreground mt-2">
            {{ __("Balance :amount in :currency. Change it from the card page, where the movement is recorded.", {
              amount: balanceFormatted,
              currency: storeGiftCard.currency_code,
            }) }}
          </p>
        </div>
        <Button
          variant="outline"
          as-child
        >
          <Link :href="route('admin.store.gift-card.show', storeGiftCard.id)">
            {{ __("View Card") }}
          </Link>
        </Button>
      </div>

      <div class="mt-6">
        <form @submit.prevent="updateGiftCard">
          <div class="shadow overflow-hidden rounded-lg mb-6 card-clip-safe">
            <div class="px-4 py-5 bg-card sm:p-6 border-b border-border">
              <h3 class="text-lg font-medium text-foreground mb-4">
                {{ __("Card") }}
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
                    id="expires_at"
                    v-model="form.expires_at"
                    :label="__('Expires At')"
                    :help="__('A past date retires the card without deleting its history.')"
                    :error="form.errors.expires_at"
                    type="datetime-local"
                    name="expires_at"
                  />
                </div>

                <div class="flex items-center col-span-6 sm:col-span-3">
                  <XSwitch
                    id="is_enabled"
                    v-model="form.is_enabled"
                    :label="__('Enabled')"
                    :help="__('A disabled card is refused at the cart even if it still has a balance.')"
                    name="is_enabled"
                    :error="form.errors.is_enabled"
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
                {{ __("Save Gift Card") }}
              </Button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
