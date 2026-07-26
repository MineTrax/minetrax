<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { useTranslations } from "@/Composables/useTranslations";
import AppBreadcrumb from "@/Shared/AppBreadcrumb.vue";
import AlertCard from "@/Components/AlertCard.vue";
import { Badge } from "@/Components/ui/badge";
import { Button } from "@/Components/ui/button";
import XInput from "@/Components/Form/XInput.vue";
import XSwitch from "@/Components/Form/XSwitch.vue";
import XTextarea from "@/Components/Form/XTextarea.vue";
import { useForm } from "@inertiajs/vue3";

const { __ } = useTranslations();

const props = defineProps({
    gateways: { type: Array, required: true },
    enabledCurrencies: { type: Array, default: () => [] },
});

const breadcrumbItems = [
    { text: __("Admin"), current: false },
    { text: __("Store"), current: false },
    { text: __("Payment Gateways"), current: true },
];

// Binds to the masked copy the server sent. A secret left untouched goes back as the mask and the
// server keeps what it already had, so a real credential never reaches the browser.
const form = useForm({
    enabled_gateways: props.gateways.filter((g) => g.is_enabled).map((g) => g.key),
    gateway_credentials: Object.fromEntries(
        props.gateways.map((gateway) => [gateway.key, { ...gateway.credentials }]),
    ),
});

const isOn = (key) => form.enabled_gateways.includes(key);

const toggle = (key, on) => {
    if (on && !isOn(key)) {
        form.enabled_gateways.push(key);
    } else if (!on) {
        form.enabled_gateways = form.enabled_gateways.filter((k) => k !== key);
    }
};

const copyWebhookUrl = (url) => navigator.clipboard?.writeText(url);

const save = () => form.post(route("admin.store.payment-gateway.update"), { preserveScroll: true });
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Payment Gateways')" />

    <div class="px-10 py-8 mx-auto max-w-5xl text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
      </div>

      <AlertCard
        v-if="!enabledCurrencies.length"
        variant="warning"
        class="mb-6"
      >
        {{ __("No currency is enabled yet, so nothing can be charged no matter which gateway you switch on.") }}
      </AlertCard>

      <p class="text-sm text-muted-foreground mb-6">
        {{ __("A gateway is only offered at checkout once it is switched on and every required credential is filled in.") }}
      </p>

      <form @submit.prevent="save">
        <div class="space-y-4">
          <div
            v-for="gateway in gateways"
            :key="gateway.key"
            class="bg-card rounded-lg shadow overflow-hidden"
          >
            <div class="p-6 flex items-start justify-between gap-4">
              <div>
                <div class="flex items-center gap-2 flex-wrap">
                  <span class="font-medium">{{ gateway.label }}</span>
                  <Badge
                    v-if="gateway.is_configured"
                    variant="default"
                  >
                    {{ __("Ready") }}
                  </Badge>
                  <Badge
                    v-else-if="isOn(gateway.key)"
                    variant="destructive"
                  >
                    {{ __("Missing credentials") }}
                  </Badge>
                  <Badge
                    v-else
                    variant="secondary"
                  >
                    {{ __("Off") }}
                  </Badge>
                </div>
                <p
                  v-if="gateway.description"
                  class="text-sm text-muted-foreground mt-1"
                >
                  {{ gateway.description }}
                </p>
                <p
                  v-if="gateway.unsupported_currencies.length"
                  class="text-xs text-orange-500 mt-2"
                >
                  {{ __("Cannot charge in: :codes. Buyers in those currencies will not be offered this gateway.", { codes: gateway.unsupported_currencies.join(", ") }) }}
                </p>
              </div>

              <XSwitch
                :id="`gateway_${gateway.key}`"
                :model-value="isOn(gateway.key)"
                :name="`gateway_${gateway.key}`"
                @update:model-value="toggle(gateway.key, $event)"
              />
            </div>

            <div
              v-if="isOn(gateway.key)"
              class="px-6 pb-6 space-y-4 border-t border-border pt-6"
            >
              <template
                v-for="field in gateway.schema"
                :key="field.key"
              >
                <XTextarea
                  v-if="field.type === 'textarea'"
                  :id="`${gateway.key}_${field.key}`"
                  v-model="form.gateway_credentials[gateway.key][field.key]"
                  :label="field.label"
                  :help="field.help"
                  :auto-resize="true"
                  :rows="3"
                  :name="`${gateway.key}_${field.key}`"
                />
                <XInput
                  v-else
                  :id="`${gateway.key}_${field.key}`"
                  v-model="form.gateway_credentials[gateway.key][field.key]"
                  :label="field.label"
                  :help="field.help"
                  :required="field.required"
                  type="text"
                  autocomplete="off"
                  :name="`${gateway.key}_${field.key}`"
                />
              </template>

              <div
                v-if="gateway.schema.length"
                class="text-xs text-muted-foreground"
              >
                {{ __("Webhook URL") }}:
                <code class="px-1 py-0.5 rounded bg-muted break-all">{{ gateway.webhook_url }}</code>
                <button
                  type="button"
                  class="ml-2 underline hover:text-foreground cursor-pointer"
                  @click="copyWebhookUrl(gateway.webhook_url)"
                >
                  {{ __("Copy") }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="flex justify-end mt-6">
          <Button
            type="submit"
            :disabled="form.processing"
          >
            {{ __("Save Payment Gateways") }}
          </Button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>
