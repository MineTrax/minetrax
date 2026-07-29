<script setup>
import { Link } from "@inertiajs/vue3";
import { useTranslations } from "@/Composables/useTranslations";
import { useHelpers } from "@/Composables/useHelpers";
import { CheckIcon, XMarkIcon } from "@heroicons/vue/24/solid";

const { __ } = useTranslations();
// Comparison cells are authored HTML, so they are sanitised before being injected — the same
// treatment package descriptions get.
const { purifyText } = useHelpers();

defineProps({
    packages: {
        type: Array,
        required: true,
    },
    fields: {
        type: Array,
        required: true,
    },
});

// A cell is only a tick if the admin actually meant yes. Anything blank, "0", "no" or "false"
// reads as a cross rather than as a tick, which is what an empty cell would otherwise look like.
const isChecked = (value) => {
    if (typeof value === "boolean") {
        return value;
    }
    return ["1", "true", "yes", "on"].includes(String(value ?? "").trim().toLowerCase());
};
</script>

<template>
  <!-- Scrolls in its own container: a wide table must never make the page scroll sideways. -->
  <div class="bg-card text-card-foreground border border-border rounded-lg shadow overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="border-b border-border">
          <th class="text-left font-medium text-muted-foreground p-4 min-w-[10rem]">
            {{ __("Package") }}
          </th>
          <th
            v-for="storePackage in packages"
            :key="storePackage.id"
            class="p-4 text-center min-w-[12rem]"
          >
            <Link
              :href="route('store.package', storePackage.slug)"
              class="block font-semibold text-foreground hover:text-primary"
            >
              {{ storePackage.name }}
            </Link>
            <span
              v-if="storePackage.is_featured"
              class="inline-block mt-1 px-2 py-0.5 text-xs font-medium bg-primary/10 text-primary rounded"
            >
              {{ __("Featured") }}
            </span>
          </th>
        </tr>
      </thead>

      <tbody>
        <tr
          v-for="field in fields"
          :key="field.key"
          class="border-b border-border"
        >
          <th class="text-left font-normal p-4 align-top">
            <span class="block text-foreground">{{ field.name }}</span>
            <span
              v-if="field.description"
              class="block text-xs text-muted-foreground"
            >{{ field.description }}</span>
          </th>
          <td
            v-for="storePackage in packages"
            :key="storePackage.id"
            class="p-4 text-center align-top text-card-foreground/90"
          >
            <template v-if="field.type === 'check'">
              <CheckIcon
                v-if="isChecked(storePackage.comparison_values?.[field.key])"
                class="w-5 h-5 mx-auto text-success"
              />
              <XMarkIcon
                v-else
                class="w-5 h-5 mx-auto text-destructive"
              />
            </template>
            <span
              v-else-if="storePackage.comparison_values?.[field.key]"
              class="prose prose-sm dark:prose-invert max-w-none inline-block"
              v-html="purifyText(String(storePackage.comparison_values[field.key]))"
            />
            <span
              v-else
              class="text-muted-foreground"
            >&mdash;</span>
          </td>
        </tr>
      </tbody>

      <tfoot>
        <tr>
          <th class="text-left font-medium text-muted-foreground p-4">
            {{ __("Price") }}
          </th>
          <td
            v-for="storePackage in packages"
            :key="storePackage.id"
            class="p-4 text-center"
          >
            <div class="flex items-baseline justify-center gap-2 mb-3">
              <span
                v-if="storePackage.is_pay_what_you_want"
                class="text-xs text-muted-foreground"
              >
                {{ __("from") }}
              </span>
              <span class="font-bold text-lg text-foreground">
                {{ storePackage.price_formatted }}
              </span>
              <span
                v-if="storePackage.price_original > storePackage.price"
                class="text-sm text-muted-foreground line-through"
              >
                {{ storePackage.price_original_formatted }}
              </span>
            </div>

            <Link
              v-if="!storePackage.is_out_of_stock"
              :href="route('store.package', storePackage.slug)"
              class="inline-block px-4 py-2 text-sm font-medium rounded-lg bg-primary text-primary-foreground hover:bg-primary/90 transition-colors"
            >
              {{ __("View") }}
            </Link>
            <span
              v-else
              class="inline-block px-4 py-2 text-sm font-medium rounded-lg bg-destructive/10 text-destructive"
            >
              {{ __("Out of Stock") }}
            </span>
          </td>
        </tr>
      </tfoot>
    </table>
  </div>
</template>
