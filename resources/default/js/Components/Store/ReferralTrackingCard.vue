<script setup>
import { useTranslations } from "@/Composables/useTranslations";
import XInput from "@/Components/Form/XInput.vue";
import XSelect from "@/Components/Form/XSelect.vue";
import XSwitch from "@/Components/Form/XSwitch.vue";
import { Button } from "@/Components/ui/button";
import { ClipboardDocumentIcon } from "@heroicons/vue/24/outline";
import { computed, ref } from "vue";

const { __ } = useTranslations();

const props = defineProps({
    form: Object,
    attributionModes: Object,
    trackingBaseUrl: String,
});

const copied = ref(false);

const trackingUrl = computed(() => `${props.trackingBaseUrl}?ref=${props.form.code || ""}`);

// Same idiom as the payment gateway page: no clipboard library for one button.
function copy() {
    navigator.clipboard?.writeText(trackingUrl.value);
    copied.value = true;
    setTimeout(() => (copied.value = false), 2000);
}

const modeLabels = {
    first_touch: __("First Touch (Recommended) - First source gets credit"),
    last_touch: __("Last Touch - Newest source gets credit"),
    extend_window: __("Extend Window - Keep original source, reset the timer"),
};

const modeOptions = computed(() => {
    const options = {};

    Object.keys(props.attributionModes).forEach((value) => {
        options[value] = modeLabels[value] ?? value;
    });

    return options;
});
</script>

<template>
  <div class="shadow rounded-lg card-clip-safe mb-6">
    <div class="px-4 py-5 bg-card sm:p-6 border-b border-border">
      <h3 class="text-lg font-medium text-foreground mb-4">
        {{ __("URL Tracking Configuration") }}
      </h3>

      <div class="grid grid-cols-6 gap-6">
        <div class="col-span-6">
          <XSwitch
            id="is_url_tracking_enabled"
            v-model="form.is_url_tracking_enabled"
            :label="__('Enable URL tracking?')"
            :help="__('Off means the code only works when a buyer types it at the cart. The link stops crediting anyone.')"
            :error="form.errors.is_url_tracking_enabled"
            name="is_url_tracking_enabled"
          />
        </div>

        <div
          v-if="form.is_url_tracking_enabled"
          class="col-span-6"
        >
          <label class="block text-sm font-medium text-foreground mb-2">
            {{ __("Tracking URL") }}
          </label>
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
          <p class="text-xs text-muted-foreground mt-1">
            {{ __("Anyone arriving through this link is tracked as coming from this referrer.") }}
          </p>
        </div>

        <div
          v-if="form.is_url_tracking_enabled"
          class="col-span-6 sm:col-span-3"
        >
          <XInput
            v-model.number="form.attribution_window_days"
            :label="__('Attribution Window (Days)')"
            :help="__('How long a visit keeps earning. Leave empty for lifetime.')"
            :error="form.errors.attribution_window_days"
            type="number"
            name="attribution_window_days"
            min="1"
          />
        </div>

        <div
          v-if="form.is_url_tracking_enabled"
          class="col-span-6 sm:col-span-3"
        >
          <XSelect
            v-model="form.attribution_mode"
            :label="__('Attribution Mode')"
            :select-list="modeOptions"
            :error="form.errors.attribution_mode"
            :disable-null="true"
          />
        </div>

        <div
          v-if="form.is_url_tracking_enabled"
          class="col-span-6"
        >
          <div class="rounded-lg border border-border bg-muted/50 p-4 text-sm">
            <p class="font-medium text-foreground mb-2">
              {{ __("How it works") }}
            </p>
            <ul class="list-disc list-inside space-y-1 text-muted-foreground">
              <li>{{ __("First Touch: if the visitor already has a referrer saved, this code will NOT override it.") }}</li>
              <li>{{ __("Last Touch: this code always overrides whatever was saved before.") }}</li>
              <li>{{ __("Extend Window: keep the saved referrer, but restart their expiry timer.") }}</li>
            </ul>
            <p class="text-muted-foreground mt-2">
              {{ __("The mode belongs to the code that arrives second, not the one already saved.") }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
