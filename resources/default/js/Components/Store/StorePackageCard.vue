<script setup>
import { Link } from "@inertiajs/vue3";
import { useTranslations } from "@/Composables/useTranslations";

const { __ } = useTranslations();

defineProps({
    storePackage: {
        type: Object,
        required: true,
    },
});
</script>

<template>
  <Link
    :href="route('store.package', storePackage.slug)"
    as="div"
    class="h-full"
  >
    <div class="h-full bg-card text-card-foreground border border-border rounded-lg shadow hover:shadow-lg transition-shadow overflow-hidden cursor-pointer">
      <!-- Image -->
      <div class="aspect-video bg-muted overflow-hidden">
        <img
          v-if="storePackage.photo_url"
          :src="storePackage.photo_url"
          :alt="storePackage.name"
          class="w-full h-full object-cover"
        >
        <div
          v-else
          class="w-full h-full flex items-center justify-center text-muted-foreground"
        >
          <svg
            class="w-12 h-12"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
            />
          </svg>
        </div>
      </div>

      <!-- Content -->
      <div class="p-4 flex flex-col gap-3">
        <!-- Name -->
        <h3 class="font-semibold text-foreground line-clamp-2">
          {{ storePackage.name }}
        </h3>

        <!-- Short Description -->
        <p
          v-if="storePackage.short_description"
          class="text-sm text-muted-foreground line-clamp-2"
        >
          {{ storePackage.short_description }}
        </p>

        <!-- Badges -->
        <div class="flex flex-wrap gap-2">
          <!-- Out of Stock Badge -->
          <span
            v-if="storePackage.is_out_of_stock"
            class="inline-block px-2 py-1 text-xs font-medium bg-destructive/10 text-destructive rounded"
          >
            {{ __("Out of Stock") }}
          </span>

          <!-- Duration Badge -->
          <span
            v-if="storePackage.expiry_duration_days"
            class="inline-block px-2 py-1 text-xs font-medium bg-muted text-muted-foreground rounded"
          >
            {{ __("{days} days", { days: storePackage.expiry_duration_days }) }}
          </span>
        </div>

        <!-- Price -->
        <div class="mt-auto pt-3 border-t border-border">
          <span class="font-bold text-lg text-foreground">
            {{ storePackage.price_formatted }}
          </span>
        </div>
      </div>
    </div>
  </Link>
</template>
